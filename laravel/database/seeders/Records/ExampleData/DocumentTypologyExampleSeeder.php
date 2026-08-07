<?php

namespace Database\Seeders\Records\ExampleData;

use App\Models\Attachment;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordMedium;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\RecordType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * 3 notices d'exemple par typologie documentaire (30 au total — voir
 * DocumentTypologySeeder pour les 10 typologies et leurs métadonnées propres).
 * Chaque notice reçoit deux supports (`record_mediums`) : un support papier
 * (placement en conteneur si disponible) et un support numérique (fichier
 * réellement écrit sur le disque `local`, attaché via `Attachment`).
 *
 * Idempotent : updateOrCreate sur records.code, firstOrCreate sur les supports.
 */
class DocumentTypologyExampleSeeder extends Seeder
{
    /**
     * type_code => liste de 3 exemples : name, description, metadata (valeurs des
     * champs propres définis par DocumentTypologySeeder), date_exact.
     */
    private const EXAMPLES = [
        'CORR' => [
            [
                'name' => 'Lettre de notification de nomination — Directeur régional',
                'description' => "Notification officielle de la nomination du nouveau directeur régional des archives.",
                'date_exact' => '2024-01-08',
                'metadata' => [
                    'corr_expediteur' => 'Secrétariat Général',
                    'corr_destinataire' => 'Direction Régionale des Archives',
                    'corr_mode_transmission' => 'Courrier',
                ],
            ],
            [
                'name' => 'Courriel de convocation à la réunion budgétaire',
                'description' => 'Convocation des chefs de service à la réunion de cadrage budgétaire annuel.',
                'date_exact' => '2024-02-02',
                'metadata' => [
                    'corr_expediteur' => 'Service Financier',
                    'corr_destinataire' => 'Chefs de service',
                    'corr_mode_transmission' => 'Email',
                ],
            ],
            [
                'name' => 'Fax de confirmation de livraison de matériel',
                'description' => "Confirmation de la livraison de matériel de bureautique par le fournisseur.",
                'date_exact' => '2024-03-11',
                'metadata' => [
                    'corr_expediteur' => 'Bureautique Plus',
                    'corr_destinataire' => 'Service Logistique',
                    'corr_mode_transmission' => 'Fax',
                ],
            ],
        ],
        'RAPPORT' => [
            [
                'name' => "Rapport d'activité annuel 2023 — Service des Archives",
                'description' => "Bilan des activités du service des archives pour l'année 2023.",
                'date_exact' => '2024-01-20',
                'metadata' => [
                    'rapport_periode' => 'Janvier–Décembre 2023',
                    'rapport_redacteur' => 'Chef du service des archives',
                    'rapport_diffusion_limitee' => false,
                ],
            ],
            [
                'name' => "Rapport d'étude de faisabilité — Numérisation du fonds historique",
                'description' => 'Étude de faisabilité technique et budgétaire du projet de numérisation.',
                'date_exact' => '2024-03-15',
                'metadata' => [
                    'rapport_periode' => '1er trimestre 2024',
                    'rapport_redacteur' => 'Consultant en archivistique',
                    'rapport_diffusion_limitee' => true,
                ],
            ],
            [
                'name' => "Rapport d'expertise technique — Climatisation des magasins d'archives",
                'description' => "Diagnostic technique des installations de climatisation des magasins.",
                'date_exact' => '2024-03-28',
                'metadata' => [
                    'rapport_periode' => 'Mars 2024',
                    'rapport_redacteur' => 'Ingénieur bâtiment',
                    'rapport_diffusion_limitee' => false,
                ],
            ],
        ],
        'PV' => [
            [
                'name' => 'Procès-verbal de la réunion du comité de direction du 12 janvier 2024',
                'description' => 'Compte rendu des décisions prises en comité de direction.',
                'date_exact' => '2024-01-12',
                'metadata' => [
                    'pv_type_reunion' => 'Comité de direction',
                    'pv_president_seance' => 'Directeur Général',
                    'pv_nombre_participants' => 8,
                ],
            ],
            [
                'name' => 'Procès-verbal d\'élection des délégués du personnel',
                'description' => "Compte rendu de l'élection des représentants du personnel.",
                'date_exact' => '2024-02-19',
                'metadata' => [
                    'pv_type_reunion' => 'Assemblée du personnel',
                    'pv_president_seance' => 'Responsable RH',
                    'pv_nombre_participants' => 45,
                ],
            ],
            [
                'name' => 'Procès-verbal de réception des travaux de rénovation',
                'description' => 'Réception contradictoire des travaux de rénovation du dépôt.',
                'date_exact' => '2024-04-05',
                'metadata' => [
                    'pv_type_reunion' => 'Réception de chantier',
                    'pv_president_seance' => 'Chef de projet',
                    'pv_nombre_participants' => 6,
                ],
            ],
        ],
        'CONTRAT' => [
            [
                'name' => 'Contrat de prestation de services — Maintenance informatique',
                'description' => 'Contrat annuel de maintenance du parc informatique.',
                'date_exact' => '2024-01-02',
                'metadata' => [
                    'contrat_parties' => 'Institution / SARL InfoTech',
                    'contrat_montant' => 4500000,
                    'contrat_duree_mois' => 12,
                ],
            ],
            [
                'name' => 'Contrat de bail commercial — Local annexe de stockage',
                'description' => "Bail commercial pour un local de stockage additionnel.",
                'date_exact' => '2024-02-14',
                'metadata' => [
                    'contrat_parties' => 'Institution / SCI Les Trois Palmiers',
                    'contrat_montant' => 1200000,
                    'contrat_duree_mois' => 36,
                ],
            ],
            [
                'name' => 'Contrat de travail à durée déterminée — Archiviste assistant',
                'description' => "Contrat de travail pour un poste d'archiviste assistant.",
                'date_exact' => '2024-03-01',
                'metadata' => [
                    'contrat_parties' => 'Institution / RAKOTO Jean',
                    'contrat_montant' => 250000,
                    'contrat_duree_mois' => 6,
                ],
            ],
        ],
        'FACTURE' => [
            [
                'name' => 'Facture n° F2024-0145 — Fournitures de bureau',
                'description' => 'Facture pour la fourniture de matériel de bureau.',
                'date_exact' => '2024-01-18',
                'metadata' => [
                    'facture_numero' => 'F2024-0145',
                    'facture_montant_ttc' => 350000,
                    'facture_fournisseur' => 'Papeterie Centrale',
                ],
            ],
            [
                'name' => 'Facture n° F2024-0198 — Prestation de numérisation',
                'description' => 'Facture pour la prestation de numérisation du fonds historique.',
                'date_exact' => '2024-02-27',
                'metadata' => [
                    'facture_numero' => 'F2024-0198',
                    'facture_montant_ttc' => 2800000,
                    'facture_fournisseur' => 'ScanArchive SARL',
                ],
            ],
            [
                'name' => 'Facture n° F2024-0233 — Achat de matériel de conservation',
                'description' => "Facture pour l'achat de boîtes et pochettes de conservation.",
                'date_exact' => '2024-03-09',
                'metadata' => [
                    'facture_numero' => 'F2024-0233',
                    'facture_montant_ttc' => 980000,
                    'facture_fournisseur' => 'ConservTech',
                ],
            ],
        ],
        'ARRETE' => [
            [
                'name' => 'Arrêté portant nomination du responsable des archives',
                'description' => 'Arrêté municipal portant nomination du responsable du service des archives.',
                'date_exact' => '2024-01-10',
                'metadata' => [
                    'arrete_numero' => 'AR-2024-012',
                    'arrete_autorite_signataire' => 'Le Maire',
                    'arrete_objet' => 'Nomination du responsable du service des archives municipales',
                ],
            ],
            [
                'name' => 'Arrêté portant organisation du service des archives',
                'description' => "Arrêté préfectoral portant réorganisation interne du service.",
                'date_exact' => '2024-02-20',
                'metadata' => [
                    'arrete_numero' => 'AR-2024-034',
                    'arrete_autorite_signataire' => 'Le Préfet',
                    'arrete_objet' => 'Réorganisation interne du service des archives',
                ],
            ],
            [
                'name' => "Arrêté portant classement d'un immeuble aux monuments historiques",
                'description' => "Arrêté ministériel de classement de l'ancien hôtel de ville.",
                'date_exact' => '2024-04-02',
                'metadata' => [
                    'arrete_numero' => 'AR-2024-051',
                    'arrete_autorite_signataire' => 'Le Ministre de la Culture',
                    'arrete_objet' => "Classement de l'ancien hôtel de ville",
                ],
            ],
        ],
        'DELIBERATION' => [
            [
                'name' => 'Délibération portant approbation du budget primitif 2024',
                'description' => "Délibération du conseil approuvant le budget primitif de l'exercice 2024.",
                'date_exact' => '2024-02-10',
                'metadata' => [
                    'delib_numero' => 'DEL-2024-005',
                    'delib_seance_du' => '2024-02-10',
                    'delib_resultat_vote' => 'Adopté',
                ],
            ],
            [
                'name' => "Délibération portant création d'un poste d'archiviste",
                'description' => "Délibération créant un poste d'archiviste au tableau des effectifs.",
                'date_exact' => '2024-03-22',
                'metadata' => [
                    'delib_numero' => 'DEL-2024-018',
                    'delib_seance_du' => '2024-03-22',
                    'delib_resultat_vote' => 'Adopté',
                ],
            ],
            [
                'name' => "Délibération portant projet de rénovation du dépôt d'archives",
                'description' => "Délibération sur le projet de rénovation du dépôt, ajournée dans l'attente du chiffrage.",
                'date_exact' => '2024-05-14',
                'metadata' => [
                    'delib_numero' => 'DEL-2024-027',
                    'delib_seance_du' => '2024-05-14',
                    'delib_resultat_vote' => 'Ajourné',
                ],
            ],
        ],
        'REGISTRE' => [
            [
                'name' => 'Registre du courrier arrivée — Année 2023',
                'description' => "Registre chronologique du courrier entrant pour l'année 2023.",
                'date_exact' => '2023-12-31',
                'metadata' => [
                    'registre_periode' => '2023',
                    'registre_nombre_folios' => 210,
                    'registre_service_producteur' => 'Secrétariat Général',
                ],
            ],
            [
                'name' => 'Registre des délibérations du conseil — 2020-2023',
                'description' => 'Registre regroupant les délibérations du conseil sur quatre années.',
                'date_exact' => '2023-12-31',
                'metadata' => [
                    'registre_periode' => '2020-2023',
                    'registre_nombre_folios' => 340,
                    'registre_service_producteur' => 'Conseil Municipal',
                ],
            ],
            [
                'name' => 'Registre matricule du personnel — 1990-2005',
                'description' => "Registre matricule retraçant la carrière des agents entre 1990 et 2005.",
                'date_exact' => '2005-12-31',
                'metadata' => [
                    'registre_periode' => '1990-2005',
                    'registre_nombre_folios' => 520,
                    'registre_service_producteur' => 'Service des Ressources Humaines',
                ],
            ],
        ],
        'PHOTO' => [
            [
                'name' => "Photographie de l'inauguration du nouveau bâtiment administratif",
                'description' => "Photographie prise lors de la cérémonie d'inauguration.",
                'date_exact' => '2019-06-15',
                'metadata' => [
                    'photo_photographe' => 'Studio Image Plus',
                    'photo_lieu_prise_vue' => "Cour d'honneur",
                    'photo_date_prise_vue' => '2019-06-15',
                ],
            ],
            [
                'name' => 'Photographie aérienne du site archivistique',
                'description' => 'Vue aérienne du site et de ses abords.',
                'date_exact' => '2021-09-03',
                'metadata' => [
                    'photo_photographe' => 'Service Cartographique National',
                    'photo_lieu_prise_vue' => 'Zone industrielle',
                    'photo_date_prise_vue' => '2021-09-03',
                ],
            ],
            [
                'name' => 'Photographie de groupe — Équipe du service des archives',
                'description' => "Photographie de l'équipe du service des archives.",
                'date_exact' => '2023-11-20',
                'metadata' => [
                    'photo_photographe' => 'RAZAFY Miora',
                    'photo_lieu_prise_vue' => 'Salle de lecture',
                    'photo_date_prise_vue' => '2023-11-20',
                ],
            ],
        ],
        'DICTIONNAIRE' => [
            [
                'name' => 'Dictionnaire de terminologie archivistique française',
                'description' => "Ouvrage de référence des termes utilisés en archivistique.",
                'date_exact' => '2018-01-01',
                'metadata' => [
                    'dico_editeur' => 'Direction des Archives de France',
                    'dico_nombre_entrees' => 850,
                    'dico_domaine_specialite' => 'Archivistique',
                ],
            ],
            [
                'name' => 'Dictionnaire juridique et administratif',
                'description' => 'Dictionnaire des termes juridiques et administratifs courants.',
                'date_exact' => '2020-01-01',
                'metadata' => [
                    'dico_editeur' => 'Éditions Juridiques Officielles',
                    'dico_nombre_entrees' => 1200,
                    'dico_domaine_specialite' => 'Droit administratif',
                ],
            ],
            [
                'name' => "Dictionnaire des sigles et abréviations de l'administration",
                'description' => "Recueil des sigles et abréviations en usage dans l'administration.",
                'date_exact' => '2022-01-01',
                'metadata' => [
                    'dico_editeur' => 'Service de la Documentation',
                    'dico_nombre_entrees' => 430,
                    'dico_domaine_specialite' => 'Administration publique',
                ],
            ],
        ],
    ];

    public function run(): void
    {
        $organisation = Organisation::query()->first();
        $user = User::query()->first();
        $level = RecordLevel::query()->where('name', 'Pièce')->first();
        $status = RecordStatus::query()->where('name', 'Publié')->first();
        $supportPaper = RecordSupport::query()->where('name', 'Papier')->first();
        $supportDigital = RecordSupport::query()->where('name', 'Numérique')->first();

        if (!$organisation || !$user || !$level || !$status || !$supportPaper || !$supportDigital) {
            $this->command?->warn('⚠️  Prérequis manquants (organisation, utilisateur, niveau "Pièce", statut "Publié" ou supports Papier/Numérique). Exécutez RecordLevelSeeder, RecordStatusSeeder et RecordSupportSeeder avant ce seeder.');
            return;
        }

        $containers = DB::table('containers')->pluck('id');

        $recordCount = 0;
        $mediumCount = 0;

        foreach (self::EXAMPLES as $typeCode => $examples) {
            $recordType = RecordType::query()->where('code', $typeCode)->first();

            if (!$recordType) {
                $this->command?->warn("⚠️  Typologie {$typeCode} introuvable — exécutez DocumentTypologySeeder avant ce seeder.");
                continue;
            }

            foreach ($examples as $index => $example) {
                $sequence = $index + 1;
                $code = sprintf('%s-EX-%02d', $typeCode, $sequence);

                $record = Record::withTrashed()->updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $example['name'],
                        'description' => $example['description'],
                        'type_id' => $recordType->id,
                        'level_id' => $level->id,
                        'status_id' => $status->id,
                        'organisation_id' => $organisation->id,
                        'creator_id' => $user->id,
                        'access_level' => 'internal',
                        'date_exact' => $example['date_exact'],
                        'date_format' => 'D',
                        'metadata' => $example['metadata'],
                    ]
                );

                $recordCount++;

                // --- Support physique (placement en conteneur si disponible) ---
                $containerId = $containers->isNotEmpty()
                    ? $containers[($recordCount - 1) % $containers->count()]
                    : null;

                $physicalMedium = RecordMedium::firstOrCreate(
                    [
                        'record_id' => $record->id,
                        'support_id' => $supportPaper->id,
                    ],
                    [
                        'container_id' => $containerId,
                        'status' => RecordMedium::STATUS_FINAL,
                        'is_principal' => true,
                        'copy_code' => 'ORIG-1',
                    ]
                );
                if ($physicalMedium->wasRecentlyCreated) {
                    $mediumCount++;
                }

                // --- Support numérique (fichier réel sur le disque local) ---
                $digitalMedium = RecordMedium::query()
                    ->where('record_id', $record->id)
                    ->where('support_id', $supportDigital->id)
                    ->first();

                if (!$digitalMedium) {
                    $attachment = $this->createDigitalAttachment($code, $example, $recordType, $user->id);

                    $digitalMedium = RecordMedium::create([
                        'record_id' => $record->id,
                        'support_id' => $supportDigital->id,
                        'attachment_id' => $attachment->id,
                        'status' => RecordMedium::STATUS_FINAL,
                        'is_principal' => false,
                        'copy_code' => 'NUM-1',
                    ]);

                    $mediumCount++;
                }
            }
        }

        $this->command?->info("✅ {$recordCount} notices d'exemple créées (10 typologies × 3), {$mediumCount} supports papier/numérique attachés.");
    }

    private function createDigitalAttachment(string $code, array $example, RecordType $recordType, int $creatorId): Attachment
    {
        $relativePath = 'digital_documents/typology_examples/' . $code . '.txt';

        $metadataLines = collect($example['metadata'])
            ->map(fn ($value, $key) => '- ' . $key . ' : ' . (is_bool($value) ? ($value ? 'oui' : 'non') : $value))
            ->implode("\n");

        $content = <<<TEXT
        {$example['name']}

        Typologie : {$recordType->name}
        Description : {$example['description']}

        --- Métadonnées ---
        {$metadataLines}
        TEXT;

        Storage::disk('local')->put($relativePath, $content);
        $fullPath = storage_path('app/' . $relativePath);

        return Attachment::create([
            'path' => $relativePath,
            'name' => $code . '.txt',
            'crypt' => hash('sha256', $relativePath . microtime()),
            'crypt_sha512' => hash_file('sha512', $fullPath),
            'size' => Storage::disk('local')->size($relativePath),
            'creator_id' => $creatorId,
            'type' => Attachment::TYPE_DIGITAL_DOCUMENT,
            'mime_type' => 'text/plain',
            'file_extension' => 'txt',
            'file_hash_md5' => md5_file($fullPath),
            'is_primary' => true,
        ]);
    }
}
