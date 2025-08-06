# Utilisation des Templates de Reformulation de Titres

## Types de reformulation disponibles

### 1. Reformulation standard (`reformulation`)
Pour des titres généraux avec amélioration de la clarté et de l'impact.

**Exemple de requête :**
```json
{
  "title": "Travaux de construction du gymnase",
  "model": "llama3.2",
  "options": {
    "style": "formal",
    "maxLength": 100,
    "type": "reformulation"
  }
}
```

### 2. Reformulation archivistique (`archival`)
Pour des titres respectant les normes de description archivistique française.

**Exemple de requête :**
```json
{
  "title": "Documents sur la construction du gymnase municipal",
  "content": "Dossier contenant les plans, devis, correspondance avec l'architecte et procès-verbaux de réception des travaux de construction du gymnase municipal entre 1958 et 1962, puis documents relatifs à l'extension réalisée en 1983.",
  "model": "llama3.2",
  "options": {
    "style": "formal",
    "maxLength": 150,
    "type": "archival"
  }
}
```

**Résultat attendu :**
```
Gymnase municipal. — Construction : plans, devis, correspondance, procès-verbaux de réception (1958-1962) ; extension (1983). 1958-1983
```

### 3. Génération de titre (`generation`)
Pour créer un titre complet à partir du seul contenu, selon les normes archivistiques.

**Exemple de requête :**
```json
{
  "content": "Ce dossier rassemble l'ensemble des pièces relatives à l'attribution de la médaille du travail aux employés de la mairie de Rouen. Il contient les demandes individuelles des agents, les listes nominatives des bénéficiaires établies par le service du personnel, ainsi que la correspondance avec la préfecture pour la période 1950-1960.",
  "model": "llama3.2",
  "options": {
    "style": "formal",
    "maxLength": 120,
    "type": "generation"
  }
}
```

**Résultat attendu :**
```
Personnel de la mairie de Rouen. — Attribution de la médaille du travail : demandes, listes nominatives, correspondance. 1950-1960
```

## Règles appliquées automatiquement

### Structure archivistique
- **Un objet :** `Objet. — Action : typologie documentaire. Dates`
- **Deux objets :** `Objet. — Action 1 (dates) ; action 2 (dates). Dates extrêmes`
- **Trois objets+ :** `Objet principal. — Sous-objet : typologie (dates). Autre sous-objet : typologie (dates). Dates extrêmes`

### Ponctuation normalisée
- Point-tiret (`. —`) après l'objet principal
- Virgule (`,`) entre éléments équivalents
- Point-virgule (`;`) entre actions différentes
- Deux points (`:`) avant la typologie documentaire
- Parenthèses `()` pour les dates intermédiaires

### Vocabulaire contrôlé
- **Typologies :** registres, correspondance, procès-verbaux, plans, états, listes, rapports, dossiers
- **Actions :** construction, aménagement, création, attribution, surveillance, gestion
- **Liaisons :** avec, dont, contient, concerne, en particulier, notamment

## Exemples de transformation

### Avant/Après - Type archivistique

**Avant :**
```
"Dossier travaux construction nouvelle mairie 1880-1900 et extension 1933"
```

**Après :**
```
"Mairie. — Construction : plans, correspondance (1880-1900) ; extension : procès-verbal d'adjudication (1933). 1880-1933"
```

**Avant :**
```
"Documents médecins autorisations exercer demandes listes"
```

**Après :**
```
"Médecins. — Autorisations d'exercer : demandes, listes des autorisations accordées. an XI-1896"
```

## Configuration recommandée

Pour un système d'archivage, utilisez prioritairement :
- `type: "archival"` pour les documents d'archives
- `style: "formal"` pour maintenir la rigueur
- `maxLength: 150` pour permettre la structure complète
- Fournissez toujours le `content` en plus du `title` pour une meilleure analyse
