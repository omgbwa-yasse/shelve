# Inventaire des endpoints — étape 1.0.1

> Généré par `scripts/build-endpoint-inventory.php` depuis `contracts/inventory/routes.json`.
> Source de vérité : `contracts/inventory/endpoints.csv`.

**Total : 1261 routes.**

## Répartition par domaine

| Domaine | Libellé | Crud | Action | Vue | Export | Upload | Framework | **Total** |
|---|---|--:|--:|--:|--:|--:|--:|--:|
| **D01** | Référentiels | 75 | 19 | 29 | 2 | · | · | **125** |
| **D02** | Records (notices) | 107 | 54 | 35 | 2 | 1 | · | **199** |
| **D03** | Localisation physique | 46 | · | 18 | · | · | · | **64** |
| **D04** | Versements / bordereaux | 27 | 6 | 10 | 3 | 3 | · | **49** |
| **D05** | Communications & réservations | 26 | 15 | 10 | 2 | · | · | **53** |
| **D06** | Courrier (Mail) | 76 | 38 | 29 | 3 | 1 | · | **147** |
| **D07** | Cycle de vie / rétention | 20 | 15 | 8 | · | · | · | **43** |
| **D08** | Thésaurus (SKOS) | 17 | 19 | 6 | 2 | 1 | · | **45** |
| **D09** | Organisation & sécurité | 45 | 14 | 18 | 2 | · | · | **79** |
| **D10** | Recherche | 7 | 27 | · | · | · | · | **34** |
| **D11** | Dolly (paniers) | 7 | 30 | 2 | · | · | · | **39** |
| **D12** | Collaboration | 31 | 18 | 6 | · | · | · | **55** |
| **D13** | Workflow | 9 | 8 | 3 | · | · | · | **20** |
| **D14** | IA | 12 | 21 | 2 | 1 | · | · | **36** |
| **D15** | Portail public / OPAC | 116 | 46 | 33 | 4 | 1 | · | **200** |
| **D16** | Exploitation | 20 | 22 | 7 | 3 | · | · | **52** |
| **—** | Hors périmètre (framework) | · | · | · | · | · | 21 | **21** |
| | **TOTAL** | **641** | **352** | **216** | **24** | **7** | **21** | **1261** |

## Charge de la phase 1

- **1024 endpoints à porter en API v1**
- 216 routes `create`/`edit` abandonnées (remplacées par des écrans Next + endpoints `/options`)
- 21 routes hors périmètre (Dusk, Sanctum, Ignition, Swagger)
- 352 **actions métier non-CRUD** — chacune doit devenir un `POST /api/v1/{ressource}/{id}/{verbe}` explicite (risque R06)
- 24 exports et 7 uploads — classes d'équivalence E2 en phase 3

