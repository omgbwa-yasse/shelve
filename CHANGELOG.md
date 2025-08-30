# Changelog

## [v2.1] - 2025-01-15

### Nouvelles fonctionnalités

- Système de pagination amélioré pour tous les contrôleurs et vues
- Logique de sélection de lots renforcée avec interface utilisateur améliorée
- Gestion avancée des conteneurs avec codes d'enregistrement uniques
- Fonctionnalité de transfert pour conteneurs de courrier avec chargement dynamique d'activités
- Validations de sécurité renforcées pour les transferts

### Améliorations

- Interface utilisateur pour la sélection de lots dans les vues de création
- Clarté et fonctionnalité des vues de transaction de lots
- Sections d'information détaillées avec nouveaux boutons
- Filtrage des activités d'organisation avec `whereHas`

### Corrections

- Requête d'activité pour un meilleur filtrage d'organisation
- Configuration d'environnement pour le développement local

### Technique

- Mise à jour de @tailwindcss/postcss vers la version 4.1.12
- Suppression des scripts de release obsolètes

---

## [v2.0] - 2024-12-20

### Nouvelles fonctionnalités

- Fonctionnalité de gestion des courriers retournés
- Nouvelle vue et méthode de contrôleur pour les retours
- Nouvelle clé de traduction 'To return' en anglais
- Vues et routes pour la gestion des courriers reçus retournés

### Améliorations

- Configuration d'environnement mise à jour
- Fonctionnalités de sécurité renforcées
- Workflow de retour de courrier complet

### Sécurité

- Améliorations de sécurité importantes
- Validation renforcée des données

---

## [v1.5] - 2024-11-28

### Nouvelles fonctionnalités

- Fonctionnalité de résumé IA pour les emails avec interface modale
- Invites utilisateur détaillées pour la résumé d'emails avec catégorisation par mots-clés
- Informations détaillées expéditeur/destinataire dans les résumés IA
- Chargement dynamique des propriétés de conteneur dans le formulaire de création de boîte
- Relation de lot et option d'impression pour les courriers

### Améliorations

- Fonctionnalité de courrier améliorée avec gestion des lots
- Sauvegarde automatique des résumés IA générés
- Interface utilisateur améliorée pour l'interaction IA

### Intégration IA

- Module de résumé automatique des emails
- Catégorisation intelligente par mots-clés
- Analyse sémantique du contenu

---

## [v1.4] - 2024-10-25

### Nouvelles fonctionnalités

- Export XML Dublin Core pour les enregistrements
- Export XML EAD 2002 pour les fonctionnalités d'export d'enregistrements
- Fonctionnalité de transfert de lots pour courriers vers boîtes et chariots
- Vue d'affichage de lot de courrier avec fonctionnalités de sélection et transfert
- Gestion de lot de courrier avec routes mises à jour pour les chariots
- Fonctionnalité de sélection de courrier avec option "Tout sélectionner"

### Améliorations

- Fonctionnalité de recherche de courrier dans BatchController
- Requête de disponibilité des courriers et affichage des propriétés
- Capacités d'export PDF améliorées
- Gestion de la sélection multiple avec pagination

### Corrections

- Relation keywords simplifiée en supprimant la méthode withTimestamps inutile
- Requête de courrier pour exclure les enregistrements de type factory
- Logique d'affichage du type de document dans les détails de courrier

### Conformité aux standards

- Support complet des standards EAD 2002
- Export Dublin Core conforme aux métadonnées
- Interopérabilité renforcée avec les systèmes d'archives

---

## [v1.3] - 2024-09-15

### Nouvelles fonctionnalités

- Système de gestion des mots-clés avec fonctionnalités de sauvegarde et suggestion
- Filtrage par mots-clés dans la recherche d'enregistrements et vues d'index
- Gestion de lots avec opérations de création, liste, ajout, suppression et effacement
- Pagination pour les archives de courrier avec différents styles de pagination
- Badge de statut "En cours" pour les courriers dans la vue d'index

### Améliorations

- Logique d'archivage pour empêcher l'archivage de courriers en cours
- Affichage des métadonnées de courrier dans la vue d'affichage
- Liens d'attachement de courrier pour gérer les courriers entrants et sortants
- Gestion des erreurs et fonctionnalité de retry pour l'extraction de mots-clés
- Interface utilisateur améliorée pour une meilleure expérience utilisateur

### IA et mots-clés

- Extraction automatique de mots-clés via IA
- Gestion intelligente des suggestions
- Interface d'application d'IA refactorisée
- Connectivité améliorée avec les fournisseurs Ollama

### Technique

- Migration record_keyword avec timestamps
- Relations de modèle Keyword mises à jour
- Test de connexion API GitHub et formatage de fichier de version
- Résolution des problèmes de certificat SSL pour les appels API GitHub

---

## [v1.2] - 2024-08-30

### Nouvelles fonctionnalités

- Gestion des mises à jour système avec routes et informations de version
- Fonctionnalité de gestion des mises à jour système implémentée

### Améliorations

- Contraintes de clé étrangère mises à jour dans la migration des conteneurs de courrier
- Remplacement de type_id par property_id dans le modèle MailContainer
- Étiquettes et vues de création mises à jour

### Suppressions

- Barre de recherche et icônes de navigation supprimées de la vue d'index de courrier
- Simplification de l'interface utilisateur

### Corrections

- Correction des noms de routes et ajustements de navigation
- Améliorations générales de stabilité

---

## [v1.1] - 2025-08-24

### Nouvelles fonctionnalités

- Système de gestion des archives complètement fonctionnel
- Interface de gestion des correspondances
- Système de réservations
- Gestion des communications
- Interface publique pour consultation
- Système de notifications
- Gestion des rôles et permissions
- Fonctionnalités d'import/export
- Système de thésaurus
- Gestion des sauvegardes
- Intelligence artificielle intégrée (Ollama)
- Système de bulletins d'information

### Features principales

- Gestion complète des documents et archives
- Workflow de validation et approbation
- Recherche avancée avec indexation TNTSearch
- Génération de PDF et exports Excel
- Système de codes-barres
- Gestion multi-langues (FR/EN)
- Interface responsive
- API REST complète

### Stack technique

- Laravel 12.0
- PHP 8.2+
- MySQL/PostgreSQL
- Vue.js components
- Bootstrap 5
- TNTSearch indexing
- Ollama AI integration

### Sécurité

- Authentification Laravel Sanctum
- Système de permissions granulaires
- Protection CSRF
- Validation des données
- Logs d'audit complets

---

## Historique de développement

### Phase de développement initial (2024-2025)

- **Structure modulaire** : Architecture basée sur des modules spécialisés (courrier, archives, chariots, thésaurus)
- **Évolution progressive** : Développement itératif avec amélioration continue des fonctionnalités
- **Intégration IA** : Ajout progressif de fonctionnalités d'intelligence artificielle avec Ollama
- **Standardisation** : Mise en conformité avec les standards archivistiques (EAD, Dublin Core)

### Modules principaux développés

1. **Module Courrier** : Gestion complète des correspondances entrantes/sortantes
2. **Module Archives** : Système de versement et de gestion des documents
3. **Module Chariots** : Système de transport et logistique documentaire
4. **Module IA** : Intégration Ollama pour analyse et résumé automatique
5. **Module Recherche** : Indexation TNTSearch et recherche avancée
6. **Module Thésaurus** : Gestion des termes et vocabulaires contrôlés
7. **Module Communication** : Système de réservation et communication documentaire
8. **Module Transfert** : Gestion des versements et transferts d'archives

### Technologies adoptées

- **Framework** : Laravel (évolution vers version 12.0)
- **Base de données** : Support MySQL et PostgreSQL
- **Frontend** : Vue.js avec Bootstrap 5, migration vers Tailwind CSS
- **Recherche** : TNTSearch pour indexation full-text
- **IA** : Intégration Ollama pour traitement automatique
- **Exports** : Support PDF, Excel, XML (EAD, Dublin Core)
- **Sécurité** : Laravel Sanctum, système de permissions granulaires

---

*Note: Shelve représente une solution complète de gestion d'archives numériques, développée de manière itérative pour répondre aux besoins croissants de digitalisation et d'automatisation des processus archivistiques.*
