# Couverture de validation des écritures — étape 1.0.2

> Généré par `scripts/extract-validation-rules.php`.
> Traite le **risque R01** (règles de validation perdues), criticité 20.

## Synthèse

| Indicateur | Valeur |
|---|--:|
| Blocs de validation trouvés | 360 |
| Couples champ → règles extraits | 1556 |
| … dont **règles construites dynamiquement** (à relire à la source) | **73** |
| Méthodes d'écriture (`store`/`update`/`upload`/`import`/`attach`) | 279 |
| … dont **validées** | 234 |
| … dont **SANS validation** | **45** |
| **Couverture** | **83.9 %** |

## Interprétation

Chaque ligne du tableau ci-dessous est une méthode qui écrit en base **sans valider son entrée**.
En Blade, le formulaire limitait implicitement ce qui arrivait (champs du formulaire, types HTML,
JavaScript côté client). Une API n'a aucune de ces protections : le portage tel quel exposerait
ces écritures à n'importe quel payload.

**Chacune exige une décision explicite** au moment du portage de son domaine :
règles reconstituées depuis le schéma de la table + le formulaire Blade correspondant.

Par ailleurs, **73 règles sont construites dynamiquement** (concaténation avec un id,
`implode()` sur une constante de classe, `Rule::` …). Le CSV n'en contient que la partie littérale :
ces lignes portent `dynamique = oui` et **doivent être relues à la source** avant d'être portées.

## ⚠️ Écritures sans validation (45)

| Contrôleur | Méthode | Source |
|---|---|---|
| `AiRecordApplyController` | `attachFromConceptArray()` | app/Http/Controllers/Api/AiRecordApplyController.php:364 |
| `AiRecordApplyController` | `attachFromRawLabels()` | app/Http/Controllers/Api/AiRecordApplyController.php:388 |
| `AuthorContactController` | `store()` | app/Http/Controllers/AuthorContactController.php:25 |
| `AuthorContactController` | `update()` | app/Http/Controllers/AuthorContactController.php:46 |
| `BackupController` | `store()` | app/Http/Controllers/BackupController.php:52 |
| `ConfigurationController` | `import()` | app/Http/Controllers/OPAC/ConfigurationController.php:70 |
| `ConfigurationController` | `update()` | app/Http/Controllers/OPAC/ConfigurationController.php:39 |
| `MailController` | `update()` | app/Http/Controllers/MailController.php:367 |
| `MailReceivedController` | `store()` | app/Http/Controllers/MailReceivedController.php:92 |
| `MailSendController` | `store()` | app/Http/Controllers/MailSendController.php:200 |
| `OrganisationActiveController` | `store()` | app/Http/Controllers/OrganisationActiveController.php:37 |
| `OrganisationActiveController` | `update()` | app/Http/Controllers/OrganisationActiveController.php:67 |
| `OrganisationContactController` | `store()` | app/Http/Controllers/OrganisationContactController.php:22 |
| `OrganisationContactController` | `update()` | app/Http/Controllers/OrganisationContactController.php:45 |
| `PublicDocumentRequestApiController` | `store()` | app/Http/Controllers/Api/PublicDocumentRequestApiController.php:39 |
| `PublicFeedbackApiController` | `store()` | app/Http/Controllers/Api/PublicFeedbackApiController.php:36 |
| `PublicPageApiController` | `store()` | app/Http/Controllers/Api/PublicPageApiController.php:133 |
| `PublicPageApiController` | `update()` | app/Http/Controllers/Api/PublicPageApiController.php:155 |
| `PublicResponseApiController` | `store()` | app/Http/Controllers/Api/PublicResponseApiController.php:103 |
| `PublicResponseApiController` | `update()` | app/Http/Controllers/Api/PublicResponseApiController.php:130 |
| `PublicResponseAttachmentApiController` | `store()` | app/Http/Controllers/Api/PublicResponseAttachmentApiController.php:64 |
| `PublicSearchLogApiController` | `store()` | app/Http/Controllers/Api/PublicSearchLogApiController.php:81 |
| `PublicTemplateApiController` | `store()` | app/Http/Controllers/Api/PublicTemplateApiController.php:112 |
| `PublicTemplateApiController` | `update()` | app/Http/Controllers/Api/PublicTemplateApiController.php:133 |
| `PublicUserApiController` | `updateProfile()` | app/Http/Controllers/Api/PublicUserApiController.php:151 |
| `RecordController` | `store()` | app/Http/Controllers/RecordController.php:169 |
| `RecordController` | `update()` | app/Http/Controllers/RecordController.php:251 |
| `RecordDragDropController` | `attachAuthors()` | app/Http/Controllers/RecordDragDropController.php:447 |
| `RecordDragDropController` | `attachKeywords()` | app/Http/Controllers/RecordDragDropController.php:506 |
| `RecordDragDropController` | `storeAndExtractAttachments()` | app/Http/Controllers/RecordDragDropController.php:246 |
| `RecordDragDropController` | `storeSingleAttachment()` | app/Http/Controllers/RecordDragDropController.php:267 |
| `RecordPeriodicApiController` | `update()` | app/Http/Controllers/Api/RecordPeriodicApiController.php:165 |
| `RecordTypeController` | `store()` | app/Http/Controllers/Settings/RecordTypeController.php:38 |
| `RecordTypeController` | `update()` | app/Http/Controllers/Settings/RecordTypeController.php:59 |
| `RolePermissionController` | `updateMatrix()` | app/Http/Controllers/RolePermissionController.php:179 |
| `SlipController` | `importForm()` | app/Http/Controllers/SlipController.php:673 |
| `ThesaurusController` | `importExport()` | app/Http/Controllers/ThesaurusController.php:152 |
| `ThesaurusController` | `importFromCsv()` | app/Http/Controllers/ThesaurusController.php:1057 |
| `ThesaurusController` | `importFromJson()` | app/Http/Controllers/ThesaurusController.php:1074 |
| `ThesaurusController` | `importFromSkos()` | app/Http/Controllers/ThesaurusController.php:1040 |
| `ThesaurusController` | `update()` | app/Http/Controllers/ThesaurusController.php:136 |
| `ThesaurusImportController` | `importFromCsv()` | app/Http/Controllers/Api/ThesaurusImportController.php:216 |
| `ThesaurusImportController` | `importFromSkos()` | app/Http/Controllers/Api/ThesaurusImportController.php:207 |
| `activityCommunicabilityController` | `store()` | app/Http/Controllers/activityCommunicabilityController.php:30 |
| `activityCommunicabilityController` | `update()` | app/Http/Controllers/activityCommunicabilityController.php:54 |
