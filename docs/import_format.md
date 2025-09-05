# Format d'import des Records

## Champs requis

Pour qu'un record soit importé avec succès, les champs suivants sont **obligatoires** :

1. **code** - Code unique du record
2. **name** - Nom/titre du record
3. **level** - Niveau hiérarchique (ex: fonds, série, sous-série, etc.)
4. **status** - Statut du record (ex: actif, inactif, etc.)
5. **support** - Support matériel (ex: papier, numérique, etc.)
6. **activity** - Activité/domaine (ex: administration, culture, etc.)

## Champs optionnels

- **content** - Contenu/description du record
- **start_date** - Date de début (format: YYYY-MM-DD, DD/MM/YYYY, etc.)
- **end_date** - Date de fin
- **exact_date** - Date exacte
- **authors** - Auteurs (séparés par des virgules)
- **terms** - Termes du thésaurus (séparés par des virgules)
- **biographical_history** - Historique biographique
- **archival_history** - Historique archivistique
- **acquisition_source** - Source d'acquisition
- **appraisal** - Évaluation
- **accrual** - Accroissement
- **arrangement** - Classement
- **access_conditions** - Conditions d'accès
- **reproduction_conditions** - Conditions de reproduction
- **language_material** - Langue du matériel
- **characteristic** - Caractéristiques
- **finding_aids** - Instruments de recherche
- **location_original** - Localisation de l'original
- **location_copy** - Localisation de la copie
- **related_unit** - Unité liée
- **publication_note** - Note de publication
- **note** - Note générale
- **archivist_note** - Note de l'archiviste
- **rule_convention** - Règle/convention
- **width** - Largeur (numérique)
- **width_description** - Description de la largeur

## Format de fichier

### Excel (.xlsx)
- Première ligne : en-têtes (optionnel)
- Colonnes dans l'ordre : code, name, level, status, support, activity, content, start_date, end_date, exact_date, authors, terms, etc.

### CSV
- Même structure que Excel
- Séparateur : virgule
- Encodage : UTF-8

## Exemple de fichier

```csv
code,name,level,status,support,activity,content,start_date,end_date,authors,terms
F001,Fonds de la mairie,fonds,actif,papier,administration,Documents administratifs de la mairie,1900-01-01,2000-12-31,"Mairie de Paris","administration,collectivité"
S001,Série des délibérations,série,actif,papier,administration,Procès-verbaux des délibérations du conseil municipal,1950-01-01,1990-12-31,"Conseil municipal","délibération,conseil"
```

## Mapping automatique

Si aucun mapping personnalisé n'est fourni, le système tente de mapper automatiquement les colonnes selon cet ordre :
1. Colonne 0 → code
2. Colonne 1 → name
3. Colonne 2 → level
4. Colonne 3 → status
5. Colonne 4 → support
6. Colonne 5 → activity
7. Colonne 6 → content
8. Colonne 7 → start_date
9. Colonne 8 → end_date
10. Colonne 9 → exact_date
11. Colonne 10 → authors
12. Colonne 11 → terms

## Résolution des problèmes

### Lignes ignorées
Si des lignes sont ignorées, vérifiez :
1. Que tous les champs requis sont présents
2. Que les valeurs ne sont pas vides
3. Que le mapping des colonnes est correct

### Erreurs de validation
- Les dates doivent être dans un format reconnu
- Les valeurs numériques (width) doivent être des nombres
- Les codes doivent être uniques (sauf si update_existing est activé)

## Gestion des types de données

Le système d'import gère automatiquement les conversions de types :

### Types supportés
- **Chaînes de caractères** : Converties automatiquement en chaînes propres
- **Tableaux** : Convertis en chaînes séparées par des virgules
- **Objets** : Convertis en chaînes via `__toString()` ou JSON
- **Nombres** : Convertis en chaînes pour les champs texte, préservés pour les champs numériques
- **Dates** : Support de multiples formats (YYYY-MM-DD, DD/MM/YYYY, YYYY, etc.)

### Nettoyage automatique
- Suppression des espaces en début et fin
- Gestion des caractères spéciaux et accents
- Conversion des tableaux en chaînes
- Validation des formats de dates

## Conseils

1. **Testez d'abord avec un petit fichier** contenant quelques lignes
2. **Vérifiez le mapping** avant de lancer l'import complet
3. **Consultez les logs** en cas de problème (`storage/logs/laravel.log`)
4. **Utilisez des valeurs par défaut** pour les champs optionnels si nécessaire
5. **Vérifiez les types de données** dans votre fichier source
