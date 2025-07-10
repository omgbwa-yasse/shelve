# Traductions du Sous-menu Communications

## État des traductions

Tous les termes utilisés dans le sous-menu communications (`resources/views/submenu/communications.blade.php`) sont maintenant correctement traduits dans les fichiers de langue.

## Correspondances des traductions

| Clé de traduction | Français | Anglais |
|-------------------|----------|---------|
| `communications` | Communications | Communications |
| `view_all` | Voir tout | View all |
| `advanced_search` | Recherche avancée | Advanced search |
| `date_search` | Recherche par date | Date search |
| `in_progress` | En cours | In progress |
| `returned` | Retournés | Returned |
| `without_return` | Sans retour | Without return |
| `not_returned` | Non retournés | Not returned |
| `return_available` | Retour disponible | Return available |
| `reservations` | Réservations | Reservations |
| `date_selection` | Sélection de date | Date selection |
| `pending_reservations` | Réservations en attente | Pending reservations |
| `approved_reservations` | Réservations approuvées | Approved reservations |
| `approved_with_communications` | Approuvées avec communications | Approved with communications |
| `add` | Ajouter | Add |
| `add_communication` | Ajouter une communication | Communication |
| `add_reservation` | Ajouter une réservation | Reservation |

## Fichiers modifiés

### Fichiers de traduction
- `lang/fr.json` - Traductions françaises (déjà présentes)
- `lang/en.json` - Traductions anglaises (termes ajoutés)

### Clés ajoutées au fichier anglais
Les termes suivants ont été ajoutés au fichier `lang/en.json` :
- `advanced_search`
- `date_search` 
- `in_progress`
- `return_available`
- `date_selection`
- `pending_reservations`
- `approved_reservations`
- `approved_with_communications`

## Permissions appliquées

Le sous-menu utilise maintenant les permissions suivantes basées sur le `PermissionCategorySeeder` :

### Section Communications
- `communications_view` - Pour afficher la section et tous les liens de consultation

### Section Réservations  
- `reservations_view` - Pour les liens de consultation basiques
- `reservations_manage` - Pour les liens de gestion avancée

### Section Ajout
- `communications_create` - Pour le lien "Ajouter une communication"
- `reservations_create` - Pour le lien "Ajouter une réservation"

## Notes
- Toutes les traductions sont maintenant cohérentes entre les langues française et anglaise
- Les permissions sont appliquées selon la logique métier définie dans le seeder
- Le système de permissions est granulaire permettant un contrôle fin des accès
