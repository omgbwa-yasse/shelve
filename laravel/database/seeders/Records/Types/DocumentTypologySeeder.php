<?php

namespace Database\Seeders\Records\Types;

use App\Models\MetadataDefinition;
use App\Models\RecordType;
use App\Models\RecordTypeMetadataProfile;
use App\Models\ReferenceList;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Dictionnaire de 10 typologies documentaires (libellés alignés sur le thésaurus
 * T3 des typologies documentaires — cf. ThesaurusTypologieSeeder) créées comme
 * `RecordType` (is_container=false). Chaque typologie reçoit un jeu de
 * métadonnées propres (`MetadataDefinition.is_system=false`) attaché via
 * `RecordTypeMetadataProfile`. Les métadonnées système communes (ISAD(G)) sont
 * attachées séparément par `SystemMetadataDefinitionsSeeder`, qui doit tourner
 * après ce seeder pour couvrir aussi ces 10 nouveaux types (il boucle sur tous
 * les `RecordType` actifs au moment de son exécution).
 *
 * Idempotent : updateOrCreate/firstOrCreate partout, rejouable sans doublon.
 */
class DocumentTypologySeeder extends Seeder
{
    /**
     * code => [name, description, icon, color, metadata]
     * metadata: code => [label, data_type, options?]
     */
    private const TYPOLOGIES = [
        'CORR' => [
            'name' => 'Correspondance',
            'description' => 'Courrier échangé (entrant, sortant ou interne)',
            'icon' => 'fa-envelope',
            'color' => '#2563EB',
            'metadata' => [
                'corr_expediteur' => ['Expéditeur', 'text'],
                'corr_destinataire' => ['Destinataire', 'text'],
                'corr_mode_transmission' => ['Mode de transmission', 'select', ['Courrier', 'Email', 'Fax', 'Porteur']],
            ],
        ],
        'RAPPORT' => [
            'name' => 'Rapport',
            'description' => "Rapport d'activité, d'étude ou d'expertise",
            'icon' => 'fa-file-lines',
            'color' => '#059669',
            'metadata' => [
                'rapport_periode' => ['Période couverte', 'text'],
                'rapport_redacteur' => ['Rédacteur', 'text'],
                'rapport_diffusion_limitee' => ['Diffusion limitée', 'boolean'],
            ],
        ],
        'PV' => [
            'name' => 'Procès-verbal',
            'description' => "Compte rendu officiel d'une réunion ou d'une séance",
            'icon' => 'fa-gavel',
            'color' => '#7C3AED',
            'metadata' => [
                'pv_type_reunion' => ['Type de réunion', 'text'],
                'pv_president_seance' => ['Président de séance', 'text'],
                'pv_nombre_participants' => ['Nombre de participants', 'number'],
            ],
        ],
        'CONTRAT' => [
            'name' => 'Contrat',
            'description' => 'Accord contractuel entre deux ou plusieurs parties',
            'icon' => 'fa-file-signature',
            'color' => '#DC2626',
            'metadata' => [
                'contrat_parties' => ['Parties contractantes', 'textarea'],
                'contrat_montant' => ['Montant', 'number'],
                'contrat_duree_mois' => ['Durée (mois)', 'number'],
            ],
        ],
        'FACTURE' => [
            'name' => 'Facture',
            'description' => 'Pièce comptable de facturation',
            'icon' => 'fa-file-invoice',
            'color' => '#D97706',
            'metadata' => [
                'facture_numero' => ['Numéro de facture', 'text'],
                'facture_montant_ttc' => ['Montant TTC', 'number'],
                'facture_fournisseur' => ['Fournisseur', 'text'],
            ],
        ],
        'ARRETE' => [
            'name' => 'Arrêté',
            'description' => 'Acte administratif unilatéral (municipal, préfectoral, etc.)',
            'icon' => 'fa-stamp',
            'color' => '#0891B2',
            'metadata' => [
                'arrete_numero' => ["Numéro de l'arrêté", 'text'],
                'arrete_autorite_signataire' => ['Autorité signataire', 'text'],
                'arrete_objet' => ["Objet de l'arrêté", 'textarea'],
            ],
        ],
        'DELIBERATION' => [
            'name' => 'Délibération',
            'description' => "Décision prise à l'issue d'une délibération d'assemblée",
            'icon' => 'fa-people-group',
            'color' => '#4338CA',
            'metadata' => [
                'delib_numero' => ['Numéro de délibération', 'text'],
                'delib_seance_du' => ['Séance du', 'date'],
                'delib_resultat_vote' => ['Résultat du vote', 'select', ['Adopté', 'Rejeté', 'Ajourné']],
            ],
        ],
        'REGISTRE' => [
            'name' => 'Registre',
            'description' => 'Registre chronologique tenu par un service producteur',
            'icon' => 'fa-book',
            'color' => '#65A30D',
            'metadata' => [
                'registre_periode' => ['Période couverte', 'text'],
                'registre_nombre_folios' => ['Nombre de folios', 'number'],
                'registre_service_producteur' => ['Service producteur', 'text'],
            ],
        ],
        'PHOTO' => [
            'name' => 'Document photographique',
            'description' => 'Photographie, diapositive ou tirage',
            'icon' => 'fa-image',
            'color' => '#BE185D',
            'metadata' => [
                'photo_photographe' => ['Photographe', 'text'],
                'photo_lieu_prise_vue' => ['Lieu de prise de vue', 'text'],
                'photo_date_prise_vue' => ['Date de prise de vue', 'date'],
            ],
        ],
        'DICTIONNAIRE' => [
            'name' => 'Dictionnaire',
            'description' => 'Ouvrage de référence lexicographique ou terminologique',
            'icon' => 'fa-book-open',
            'color' => '#475569',
            'metadata' => [
                'dico_editeur' => ['Éditeur', 'text'],
                'dico_nombre_entrees' => ["Nombre d'entrées", 'number'],
                'dico_domaine_specialite' => ['Domaine de spécialité', 'text'],
            ],
        ],
    ];

    public function run(): void
    {
        $userId = User::query()->value('id');

        if (!$userId) {
            $this->command?->warn('⚠️  Aucun utilisateur trouvé. Exécutez SuperAdminSeeder avant ce seeder.');
            return;
        }

        $referenceList = ReferenceList::firstOrCreate(
            ['code' => 'DOCUMENT_TYPES'],
            [
                'name' => 'Types de documents',
                'description' => 'Catalogue unifié des types de notices (aligné TypeDocument IntelliGID)',
                'active' => true,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]
        );

        $displayOrder = 100;

        foreach (self::TYPOLOGIES as $code => $config) {
            $displayOrder += 10;

            $recordType = RecordType::withTrashed()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $config['name'],
                    'description' => $config['description'],
                    'reference_list_id' => $referenceList->id,
                    'is_container' => false,
                    'icon' => $config['icon'],
                    'color' => $config['color'],
                    'code_prefix' => $code,
                    'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                    'default_access_level' => 'internal',
                    'is_active' => true,
                    'display_order' => $displayOrder,
                    'created_by' => $userId,
                ]
            );

            $sortOrder = 0;

            foreach ($config['metadata'] as $metaCode => $metaConfig) {
                [$metaName, $dataType, $options] = array_pad($metaConfig, 3, null);
                $sortOrder += 10;

                $definition = MetadataDefinition::updateOrCreate(
                    ['code' => $metaCode],
                    [
                        'name' => $metaName,
                        'data_type' => $dataType,
                        'options' => $options,
                        'searchable' => true,
                        'active' => true,
                        'is_system' => false,
                        'sort_order' => $sortOrder,
                        'created_by' => $userId,
                    ]
                );

                RecordTypeMetadataProfile::firstOrCreate(
                    [
                        'record_type_id' => $recordType->id,
                        'metadata_definition_id' => $definition->id,
                    ],
                    [
                        'mandatory' => false,
                        'visible' => true,
                        'sort_order' => $sortOrder,
                        'created_by' => $userId,
                    ]
                );
            }
        }

        $this->command?->info(sprintf(
            '✅ %d typologies documentaires créées avec leurs métadonnées propres.',
            count(self::TYPOLOGIES)
        ));
    }
}
