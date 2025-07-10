# Documentation du Système de Gestion des Courriers

## Vue d'ensemble

Le système de gestion des courriers est maintenant complètement intégré avec la gestion des contacts et organisations externes. Il comprend tous les éléments nécessaires pour fonctionner correctement.

## Composants du système

### 1. Typologies de courrier

Le système inclut **26 typologies** organisées par catégories :

#### 📁 Correspondance
- **CORR** - Correspondance générale
- **INFO** - Information  
- **CONV** - Convocation

#### 📁 Demandes
- **DSTG** - Demande de stage
- **DAID** - Demande d'aide
- **DFOR** - Demande de formation
- **RECL** - Réclamation

#### 📁 Documents officiels
- **CERT** - Certificat
- **DECL** - Déclaration
- **PERM** - Autorisation/Permis

#### 📁 Financier
- **CRED** - Crédit/Financement
- **FACT** - Facture
- **BUDG** - Budget

#### 📁 Ressources humaines
- **CAND** - Candidature
- **CONT** - Contrat
- **EVAL** - Évaluation

#### 📁 Juridique
- **JUST** - Justice/Contentieux
- **MISE** - Mise en demeure

#### 📁 Communication
- **COMM** - Communication
- **PRES** - Presse
- **PART** - Partenariat

#### 📁 Technique
- **TECH** - Technique
- **MAIN** - Maintenance

#### 📁 Divers
- **INVT** - Invitation
- **RAPP** - Rapport
- **DIRS** - Divers

### 2. Priorités de courrier

**5 niveaux de priorité** avec durées associées :

| Priorité | Durée | Usage |
|----------|-------|-------|
| Très urgent | 1 jour | Urgence absolue |
| Urgent | 3 jours | Traitement prioritaire |
| Normal | 7 jours | Traitement standard |
| Faible | 15 jours | Pas de contrainte temporelle forte |
| Informationnel | 30 jours | Pour information uniquement |

### 3. Actions de courrier

**8 types d'actions** pour le traitement :

| Action | Durée | À retourner | Description |
|--------|-------|-------------|-------------|
| Pour information | 0 jour | Non | Transmission informative |
| Pour avis | 5 jours | Oui | Demande de consultation |
| Pour décision | 7 jours | Oui | Nécessite une décision |
| Pour signature | 3 jours | Oui | Document à signer |
| Pour validation | 5 jours | Oui | Document à approuver |
| Pour traitement | 10 jours | Oui | Traitement complet requis |
| Pour suivi | 15 jours | Non | Suivi régulier |
| Pour archivage | 0 jour | Non | Archivage simple |

### 4. Contacts et organisations externes

Le système inclut des exemples de :
- **Organisations** : Ministère de l'Éducation, Universités, Entreprises
- **Contacts** : Directeurs, Responsables, Consultants indépendants

## Installation et initialisation

### Commandes disponibles

#### Initialisation complète
```bash
php artisan mail:init
```
Cette commande initialise tout le système en une seule fois.

#### Options avancées
```bash
# Réinitialisation complète (supprime les données existantes)
php artisan mail:init --fresh

# Initialisation du système de courriers uniquement
php artisan mail:seed

# Vérification du système
php artisan mail:check-system

# Vérification des données externes
php artisan external:check-data
```

### Seeders individuels

Si vous préférez une approche granulaire :

```bash
# Seeder principal (typologies, priorités, actions)
php artisan db:seed --class=MailSystemSeeder

# Seeder des contacts externes
php artisan db:seed --class=ExternalContactsSeeder
```

## Utilisation dans l'application

### Création de courriers entrants

1. **Sélection de la typologie** : Choisir parmi les 26 typologies disponibles
2. **Définition de la priorité** : Sélectionner le niveau d'urgence
3. **Attribution d'une action** : Définir le type de traitement requis
4. **Assignation de contacts externes** : Utiliser les organisations/contacts configurés

### Création de courriers sortants

1. **Choix du destinataire externe** : Sélection d'une organisation ou contact
2. **Personnalisation** : Possibilité d'ajouter des contacts spécifiques
3. **Traçabilité complète** : Historique des échanges avec les entités externes

### API de recherche

Le système fournit des API pour la recherche dynamique :

- `/api/external-organizations/search` - Recherche d'organisations
- `/api/external-contacts/search` - Recherche de contacts

## Structure de base de données

### Tables principales

- `mail_typologies` - Types de courrier
- `mail_priorities` - Niveaux de priorité  
- `mail_actions` - Actions de traitement
- `external_organizations` - Organisations externes
- `external_contacts` - Contacts externes
- `mails` - Courriers avec relations vers les entités externes

### Relations

- Un courrier peut être lié à une organisation externe ET/OU un contact externe
- Les contacts peuvent être indépendants ou rattachés à une organisation
- Chaque courrier a une typologie, une priorité et peut avoir plusieurs actions

## Vérification du système

Après installation, vérifiez que tout fonctionne :

```bash
php artisan mail:check-system
```

Cette commande affiche :
- ✅ Nombre de typologies configurées
- ✅ Priorités et leurs durées
- ✅ Actions disponibles  
- ✅ Organisations et contacts externes
- ✅ État général du système

## Maintenance

### Ajout de nouvelles typologies

Modifiez le fichier `database/seeders/MailSystemSeeder.php` et ajoutez vos typologies dans le tableau `$typologies`.

### Personnalisation des priorités

Ajustez les durées dans la méthode `seedMailPriorities()` du seeder.

### Gestion des contacts externes

Utilisez l'interface web ou ajoutez-les via le seeder `ExternalContactsSeeder.php`.

## Support

Pour toute question ou problème :
1. Vérifiez l'état du système avec `php artisan mail:check-system`
2. Consultez les logs Laravel en cas d'erreur
3. Utilisez `php artisan mail:init --fresh` pour une réinitialisation complète

---

**Le système de gestion des courriers est maintenant prêt pour la production !** 🎯
