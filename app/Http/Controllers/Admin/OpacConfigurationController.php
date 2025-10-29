<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpacConfiguration;
use App\Models\OpacConfigurationCategory;
use App\Models\OpacConfigurationValue;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpacConfigurationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:admin.opac.configure');
    }

    /**
     * Affiche la liste des configurations OPAC
     */
    public function index()
    {
        $organisations = Organisation::all();
        $categories = OpacConfigurationCategory::getWithConfigurations();

        // Organisation sélectionnée (par défaut la première organisation de l'utilisateur)
        $selectedOrganisationId = request('organisation_id') ?? Auth::user()->organisations->first()?->id;

        // Récupère les valeurs pour l'organisation sélectionnée
        $configurationValues = [];
        if ($selectedOrganisationId) {
            $values = OpacConfigurationValue::getValuesForOrganisation($selectedOrganisationId);
            foreach ($values as $key => $value) {
                $configurationValues[$key] = $value->effective_value;
            }
        }

        return view('public.admin.opac.configurations.index', compact(
            'categories',
            'organisations',
            'selectedOrganisationId',
            'configurationValues'
        ));
    }

    /**
     * Met à jour les configurations OPAC pour une organisation
     */
    public function update(Request $request)
    {
        $organisationId = $request->input('organisation_id');
        $configurations = $request->input('configurations', []);

        if (!$organisationId) {
            return back()->with('error', 'Organisation non spécifiée.');
        }

        // Vérifier que l'utilisateur a accès à cette organisation
        $organisation = Organisation::find($organisationId);
        if (!$organisation) {
            return back()->with('error', 'Accès non autorisé à cette organisation.');
        }

        // TODO: Ajouter la vérification d'accès à l'organisation selon votre logique métier
        // if (!Auth::user()->canAccessOrganisation($organisationId)) {
        //     return back()->with('error', 'Accès non autorisé à cette organisation.');
        // }

        $updatedCount = 0;

        foreach ($configurations as $configurationId => $value) {
            $configuration = OpacConfiguration::find($configurationId);

            if ($configuration) {
                // Validation selon le type de configuration
                $validatedValue = $this->validateConfigurationValue($configuration, $value);

                if ($validatedValue !== false) {
                    $configuration->setValueForOrganisation(
                        $organisationId,
                        $validatedValue,
                        Auth::id()
                    );
                    $updatedCount++;
                }
            }
        }

        return back()->with('success',
            "Configuration mise à jour avec succès. {$updatedCount} paramètre(s) modifié(s)."
        );
    }

    /**
     * Affiche les détails d'une configuration
     */
    public function show(OpacConfiguration $configuration)
    {
        $configuration->load('category', 'values.organisation', 'values.modifiedBy');

        return view('public.admin.opac.configurations.show', compact('configuration'));
    }

    /**
     * Réinitialise une configuration à sa valeur par défaut
     */
    public function reset(Request $request, OpacConfiguration $configuration)
    {
        $organisationId = $request->input('organisation_id');

        if (!$organisationId) {
            return back()->with('error', 'Organisation non spécifiée.');
        }

        // Désactiver la valeur personnalisée (revient à la valeur par défaut)
        OpacConfigurationValue::where('organisation_id', $organisationId)
                               ->where('configuration_id', $configuration->id)
                               ->update(['is_active' => false]);

        return back()->with('success',
            "Configuration '{$configuration->label}' réinitialisée à sa valeur par défaut."
        );
    }

    /**
     * Exporte les configurations d'une organisation
     */
    public function export(Request $request)
    {
        $organisationId = $request->input('organisation_id');

        if (!$organisationId) {
            return back()->with('error', 'Organisation non spécifiée.');
        }

        $organisation = Organisation::find($organisationId);
        $configurations = OpacConfiguration::getConfigurationsForOrganisation($organisationId);

        $exportData = [
            'organisation' => $organisation->name,
            'export_date' => now()->toISOString(),
            'configurations' => []
        ];

        foreach ($configurations as $categoryName => $categoryConfigs) {
            foreach ($categoryConfigs as $config) {
                $value = $config->getValueForOrganisation($organisationId);

                $exportData['configurations'][] = [
                    'category' => $categoryName,
                    'key' => $config->key,
                    'label' => $config->label,
                    'type' => $config->type,
                    'value' => $value,
                    'is_default' => $value === $config->default_value
                ];
            }
        }

        $filename = "opac_config_{$organisation->name}_" . now()->format('Y-m-d_H-i-s') . ".json";

        return response()->json($exportData)
                         ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Importe les configurations pour une organisation
     */
    public function import(Request $request)
    {
        $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'config_file' => 'required|file|mimes:json'
        ]);

        $organisationId = $request->input('organisation_id');
        $file = $request->file('config_file');

        try {
            $importData = json_decode($file->get(), true);

            if (!isset($importData['configurations'])) {
                throw new \Exception('Format de fichier invalide.');
            }

            $importedCount = 0;

            foreach ($importData['configurations'] as $configData) {
                $configuration = OpacConfiguration::where('key', $configData['key'])->first();

                if ($configuration && !$configData['is_default']) {
                    $configuration->setValueForOrganisation(
                        $organisationId,
                        $configData['value'],
                        Auth::id()
                    );
                    $importedCount++;
                }
            }

            return back()->with('success',
                "Configuration importée avec succès. {$importedCount} paramètre(s) mis à jour."
            );

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }

    /**
     * Valide une valeur de configuration selon son type
     */
    private function validateConfigurationValue(OpacConfiguration $configuration, $value)
    {
        switch ($configuration->type) {
            case 'boolean':
                return (bool) $value;

            case 'integer':
                if (!is_numeric($value)) {
                    return false;
                }
                $intValue = (int) $value;

                // Appliquer les règles de validation si définies
                if ($configuration->validation_rules) {
                    foreach ($configuration->validation_rules as $rule) {
                        if (strpos($rule, 'min:') === 0) {
                            $min = (int) substr($rule, 4);
                            if ($intValue < $min) return false;
                        }
                        if (strpos($rule, 'max:') === 0) {
                            $max = (int) substr($rule, 4);
                            if ($intValue > $max) return false;
                        }
                    }
                }
                return $intValue;

            case 'string':
                // Validation spécifique selon les règles
                if ($configuration->validation_rules && in_array('email', $configuration->validation_rules)) {
                    return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : false;
                }
                return $value;

            case 'multiselect':
                if (is_string($value)) {
                    $value = json_decode($value, true);
                }
                return is_array($value) ? $value : [];

            default:
                return $value;
        }
    }
}
