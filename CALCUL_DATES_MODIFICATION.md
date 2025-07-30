# Modification du Calcul des Dates dans le Cycle de Vie

## Changement apporté

### **Logique de priorité des dates :**
Le système utilise maintenant une logique de priorité pour les calculs de durée :
1. **`date_end`** en priorité (date de fin du document)
2. **`date_exact`** si `date_end` est NULL (date exacte alternative)

### **Implémentation technique :**

#### **1. Constantes SQL mises à jour :**
```php
const RETENTION_DURATION_EXPIRED = 'DATEDIFF(NOW(), COALESCE(records.date_end, records.date_exact)) > retentions.duration * 365';
const RETENTION_DURATION_ACTIVE = 'DATEDIFF(NOW(), COALESCE(records.date_end, records.date_exact)) <= retentions.duration * 365';
const COMMUNICABILITY_EXPIRED = 'DATEDIFF(NOW(), COALESCE(records.date_end, records.date_exact)) > communicabilities.duration * 365';
```

#### **2. Tri adapté :**
```php
private function addDateOrderBy($query)
{
    return $query->orderByRaw('COALESCE(records.date_end, records.date_exact) DESC');
}
```

### **Fonction SQL COALESCE :**
- `COALESCE(records.date_end, records.date_exact)` retourne :
  - `records.date_end` si elle n'est pas NULL
  - `records.date_exact` si `date_end` est NULL
  - NULL si les deux sont NULL

### **Avantages :**
1. **Flexibilité** : Gestion des cas où `date_end` n'est pas renseignée
2. **Cohérence** : Même logique pour tous les calculs de durée
3. **Performance** : Calcul fait au niveau SQL plutôt qu'en PHP
4. **Robustesse** : Évite les erreurs de dates manquantes

### **Fonctionnalités mises à jour :**
- ✅ `recordToRetain()` - Documents en rétention active
- ✅ `recordToTransfer()` - Documents à transférer (communicabilité)
- ✅ `recordToSort()` - Documents à trier
- ✅ `recordToStore()` - Documents à archiver définitivement
- ✅ `recordToKeep()` - Documents en attente de conservation
- ✅ `recordToEliminate()` - Documents à éliminer

### **Tous les calculs utilisent maintenant :**
```sql
DATEDIFF(NOW(), COALESCE(records.date_end, records.date_exact)) [COMPARAISON] duration * 365
```

Cette modification garantit que le système de cycle de vie fonctionne même avec des données partielles, en utilisant intelligemment les dates disponibles.
