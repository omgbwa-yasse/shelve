<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\OrganisationInterim;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Gestion des intérims : désigner un intérimaire pour le responsable d'un service
 * sur une période donnée. Tant qu'un intérim est actif, le courrier adressé au
 * service est routé vers l'intérimaire (voir Organisation::responsible()).
 */
class OrganisationInterimController extends Controller
{
    /**
     * Seuls le DG et les superadmins gèrent les intérims.
     */
    private function requireManager(): void
    {
        $user = Auth::user();

        abort_unless(
            $user->isSuperAdmin() || $user->hasRoleInOrganisation('DG', $user->current_organisation_id),
            403,
            'Seul le Directeur Général peut gérer les intérims.'
        );
    }

    public function index()
    {
        $this->requireManager();

        // Regroupés par entité + titulaire pour lire d'un coup tous les volets délégués.
        $interims = OrganisationInterim::with(['organisation', 'titular', 'interim', 'activity'])
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->orderBy('organisation_id')
            ->orderBy('titular_user_id')
            ->orderByDesc('is_primary')
            ->paginate(20);

        return view('organisations.interims.index', compact('interims'));
    }

    public function create()
    {
        $this->requireManager();

        $organisations = Organisation::with(['activities' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();
        $users = User::orderBy('name')->get();

        // Activités du plan de classement par entité : le formulaire n'affiche que
        // celles de la direction sélectionnée.
        $activitiesByOrganisation = $organisations->mapWithKeys(fn ($org) => [
            $org->id => $org->activities->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])->values(),
        ]);

        return view('organisations.interims.create', compact('organisations', 'users', 'activitiesByOrganisation'));
    }

    public function store(Request $request)
    {
        $this->requireManager();

        // On accepte plusieurs intérimaires (un par volet). Les lignes laissées
        // vides dans le formulaire sont écartées avant la validation.
        $interims = collect($request->input('interims', []))
            ->filter(fn ($row) => !empty($row['interim_user_id'] ?? null))
            ->values()
            ->all();

        $request->merge(['interims' => $interims]);

        $data = $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'titular_user_id' => 'required|exists:users,id',
            'interims' => 'required|array|min:1|max:5',
            'interims.*.interim_user_id' => 'required|exists:users,id|different:titular_user_id|distinct',
            'interims.*.activity_id' => 'nullable|exists:activities,id',
            'interims.*.scope' => 'nullable|string|max:255',
            'primary_index' => 'nullable|integer|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ], [
            'interims.required' => 'Désignez au moins un intérimaire.',
            'interims.*.interim_user_id.required' => "Choisissez l'intérimaire.",
            'interims.*.interim_user_id.different' => "L'intérimaire doit être différent du titulaire.",
            'interims.*.interim_user_id.distinct' => 'Un même intérimaire ne peut pas être désigné deux fois.',
        ]);

        // Un seul intérimaire principal : celui qui reçoit le courrier routé par défaut.
        $primaryIndex = (int) ($data['primary_index'] ?? 0);
        if (!isset($data['interims'][$primaryIndex])) {
            $primaryIndex = 0;
        }

        foreach ($data['interims'] as $index => $row) {
            OrganisationInterim::create([
                'organisation_id' => $data['organisation_id'],
                'titular_user_id' => $data['titular_user_id'],
                'interim_user_id' => $row['interim_user_id'],
                'activity_id' => $row['activity_id'] ?? null,
                'scope' => $row['scope'] ?? null,
                'is_primary' => $index === $primaryIndex,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'reason' => $data['reason'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);
        }

        $count = count($data['interims']);

        return redirect()->route('organisation-interims.index')
            ->with('success', $count > 1
                ? "$count intérimaires enregistrés avec succès."
                : 'Intérim enregistré avec succès.');
    }

    /**
     * Clôture un intérim (désactivation immédiate).
     */
    public function deactivate(OrganisationInterim $interim)
    {
        $this->requireManager();

        $interim->update(['is_active' => false]);

        return back()->with('success', 'Intérim clôturé.');
    }

    /**
     * Désigne cet intérimaire comme principal (celui vers qui le courrier du
     * service est routé par défaut) ; les autres volets restent actifs.
     */
    public function setPrimary(OrganisationInterim $interim)
    {
        $this->requireManager();

        OrganisationInterim::where('organisation_id', $interim->organisation_id)
            ->where('titular_user_id', $interim->titular_user_id)
            ->update(['is_primary' => false]);

        $interim->update(['is_primary' => true]);

        return back()->with('success', "Intérimaire principal mis à jour.");
    }

    public function destroy(OrganisationInterim $interim)
    {
        $this->requireManager();

        $interim->delete();

        return back()->with('success', 'Intérim supprimé.');
    }
}
