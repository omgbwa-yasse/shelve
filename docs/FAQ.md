# ❓ FAQ - Questions Fréquemment Posées

**SHELVE - Système de Gestion de Bibliothèque et Archives**  
**Version**: 1.0  
**Dernière Mise à Jour**: 7 novembre 2025

---

## 📚 Table des Matières

1. [Compte et Connexion](#compte-et-connexion)
2. [Navigation et Interface](#navigation-et-interface)
3. [Fichiers et Upload](#fichiers-et-upload)
4. [Permissions et Accès](#permissions-et-accès)
5. [Recherche](#recherche)
6. [Workflows et Approbations](#workflows-et-approbations)
7. [Digital Folders](#digital-folders)
8. [Documents Numériques](#documents-numériques)
9. [Artifacts (Objets)](#artifacts-objets)
10. [Livres](#livres)
11. [Périodiques](#périodiques)
12. [API](#api)
13. [Performance](#performance)
14. [Données et Export](#données-et-export)
15. [Sécurité](#sécurité)
16. [Support Technique](#support-technique)

---

## 👤 Compte et Connexion

### Q: J'ai oublié mon mot de passe, que faire ?

**R**: Suivez ces étapes:

1. Sur la page de connexion, cliquez sur **"Mot de passe oublié ?"**
2. Saisissez votre **adresse email**
3. Cliquez sur **"Envoyer le lien de réinitialisation"**
4. Consultez vos **emails** (vérifiez aussi les spams)
5. Cliquez sur le **lien** dans l'email (valide 60 minutes)
6. Créez un **nouveau mot de passe** (minimum 8 caractères)
7. **Connectez-vous** avec votre nouveau mot de passe

> **Pas d'email reçu ?** Contactez votre administrateur système.

---

### Q: Comment changer mon mot de passe ?

**R**: Pour changer votre mot de passe actuel:

1. Cliquez sur votre **nom/avatar** (coin supérieur droit)
2. Sélectionnez **"Profil"**
3. Onglet **"Sécurité"**
4. Section **"Changer mot de passe"**:
   - Saisissez votre mot de passe actuel
   - Nouveau mot de passe
   - Confirmez le nouveau mot de passe
5. Cliquez sur **"Mettre à jour"**

**Règles de mot de passe**:
- Minimum 8 caractères
- Au moins 1 majuscule
- Au moins 1 minuscule
- Au moins 1 chiffre
- Recommandé: 1 caractère spécial (!@#$%^&*)

---

### Q: Pourquoi suis-je déconnecté automatiquement ?

**R**: Pour des raisons de sécurité, les sessions expirent après:

- **2 heures d'inactivité** (configuration par défaut)
- **Fermeture du navigateur** (si "Se souvenir de moi" non coché)
- **Changement d'adresse IP** (sécurité)

**Pour rester connecté plus longtemps**:
- Cochez **"Se souvenir de moi"** à la connexion (30 jours max)
- Gardez votre fenêtre SHELVE ouverte
- Effectuez une action toutes les 2 heures

---

### Q: Puis-je me connecter depuis plusieurs appareils ?

**R**: **Oui**, vous pouvez avoir plusieurs sessions actives simultanées:
- Ordinateur de bureau
- Ordinateur portable
- Tablette
- Smartphone

Toutes les sessions partagent les mêmes données en temps réel.

**Gérer vos sessions**:
- Menu Profil > Sécurité > Sessions actives
- Déconnectez les sessions suspectes

---

### Q: Mon compte a été verrouillé, pourquoi ?

**R**: Votre compte peut être verrouillé pour:

1. **Trop de tentatives échouées** (5 échecs en 10 minutes)
2. **Compte inactif** (90 jours sans connexion)
3. **Suspension administrative** (violation de politique)

**Solutions**:
- **Verrouillage automatique**: Attendez 30 minutes ou contactez admin
- **Compte inactif**: Contactez votre administrateur pour réactivation
- **Suspension**: Contactez votre responsable/administrateur

---

## 🧭 Navigation et Interface

### Q: Comment revenir à la page d'accueil ?

**R**: Plusieurs options:
- Cliquez sur le **logo SHELVE** (en haut à gauche)
- Cliquez sur **"Accueil"** dans le menu
- Raccourci clavier: **Alt + H** (Home)
- Utilisez le **fil d'Ariane** et cliquez sur "Accueil"

---

### Q: L'interface est en anglais, comment la mettre en français ?

**R**: Pour changer la langue:

1. Menu Utilisateur (coin supérieur droit)
2. **Paramètres**
3. Section **"Préférences"**
4. **Langue**: Sélectionnez "Français (FR)"
5. Cliquez sur **"Sauvegarder"**
6. La page se rechargera en français

**Langues disponibles**:
- 🇫🇷 Français
- 🇬🇧 English
- (Plus de langues peuvent être ajoutées par l'admin)

---

### Q: Puis-je personnaliser l'interface ?

**R**: Oui, plusieurs options de personnalisation:

**Thème**:
- Clair (défaut)
- Sombre
- Automatique (selon l'heure)

**Affichage**:
- Densité des listes (compact, confortable, spacieux)
- Nombre d'éléments par page (10, 25, 50, 100)

**Tableau de bord**:
- Ajouter/retirer des widgets
- Réorganiser par drag & drop
- Définir des raccourcis personnalisés

> **Paramètres** > **Préférences** > **Interface**

---

### Q: Les raccourcis clavier ne fonctionnent pas

**R**: Vérifiez:

1. **Aucun champ de texte actif** (les raccourcis sont désactivés quand vous tapez)
2. **Pas de conflits avec le navigateur** (certains raccourcis sont réservés)
3. **Clavier correct** (AZERTY vs QWERTY)

**Raccourcis principaux**:
- `Ctrl + K`: Recherche rapide
- `Ctrl + N`: Nouveau
- `Ctrl + S`: Sauvegarder
- `Échap`: Fermer modal
- `Alt + ←`: Page précédente

Voir la liste complète: **Menu** > **Aide** > **Raccourcis Clavier**

---

## 📁 Fichiers et Upload

### Q: Pourquoi mon fichier ne s'upload pas ?

**R**: Vérifiez les points suivants:

**1. Taille du fichier**:
- Maximum: **50 MB** par fichier
- Vérifiez la taille: Clic droit > Propriétés

**2. Format supporté**:
- PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX
- JPG, PNG, GIF, SVG
- TXT, CSV, ZIP
- Voir [liste complète](#formats-supportés)

**3. Nom du fichier**:
- Évitez caractères spéciaux: `/ \ : * ? " < > |`
- Utilisez lettres, chiffres, tirets, underscores
- Maximum 255 caractères

**4. Connexion internet**:
- Vérifiez que vous êtes en ligne
- Connexion stable (pas de coupures)

**5. Espace disponible**:
- Votre quota peut être dépassé
- Vérifiez: Menu > Profil > Utilisation du stockage

---

### Q: Puis-je uploader plusieurs fichiers en même temps ?

**R**: **Oui**, plusieurs méthodes:

**Méthode 1: Glisser-Déposer** (recommandé)
1. Sélectionnez plusieurs fichiers dans votre explorateur
2. Glissez-les ensemble dans la zone d'upload
3. Tous les fichiers seront uploadés en parallèle

**Méthode 2: Sélection Multiple**
1. Cliquez sur **"Upload"**
2. Maintenez **Ctrl** (Windows/Linux) ou **Cmd** (Mac)
3. Cliquez sur chaque fichier à uploader
4. Cliquez sur **"Ouvrir"**

**Limites**:
- Maximum **20 fichiers** simultanés
- Total cumulé < **500 MB** par upload

---

### Q: L'upload est bloqué à 50%, que faire ?

**R**: Essayez ces solutions:

1. **Attendez** (les gros fichiers prennent du temps)
2. **Vérifiez votre connexion** (ping, débit)
3. **Rafraîchissez la page** et réessayez
4. **Utilisez un fichier plus petit** (compressez si possible)
5. **Essayez un autre navigateur**
6. **Contactez le support** si le problème persiste

**Temps estimés** (connexion 10 Mbps):
- 1 MB: ~1 seconde
- 10 MB: ~10 secondes
- 50 MB: ~50 secondes

---

### Q: Comment télécharger un fichier attaché ?

**R**: Pour télécharger un fichier:

1. Ouvrez la **ressource** concernée
2. Section **"Fichiers attachés"**
3. Trouvez le fichier à télécharger
4. Cliquez sur l'icône **📥 Télécharger**
5. Le fichier se télécharge dans votre dossier par défaut

**Téléchargement multiple**:
- Cochez plusieurs fichiers
- Bouton **"Télécharger la sélection"** (télécharge un ZIP)

---

### Q: Puis-je prévisualiser un fichier avant de le télécharger ?

**R**: **Oui**, pour certains formats:

**Prévisualisation intégrée**:
- **PDF**: Visionneuse intégrée
- **Images**: Galerie avec zoom
- **Texte**: Affichage du contenu

**Comment prévisualiser**:
1. Cliquez sur le **nom du fichier** (au lieu de l'icône télécharger)
2. La prévisualisation s'ouvre dans une modale
3. Fermez avec **Échap** ou le bouton **X**

**Formats sans prévisualisation**:
- Documents Word, Excel, PowerPoint (téléchargement direct)
- ZIP et autres archives

---

### Q: Comment supprimer un fichier attaché ?

**R**: Pour supprimer un fichier:

1. Ouvrez la ressource
2. Mode **Édition** (icône ✏️)
3. Section **"Fichiers attachés"**
4. Cliquez sur l'icône **🗑️** à côté du fichier
5. **Confirmez** la suppression
6. **Sauvegardez** la ressource

> **⚠️ Attention**: La suppression est définitive !

---

## 🔐 Permissions et Accès

### Q: Pourquoi ne puis-je pas modifier cette ressource ?

**R**: Plusieurs raisons possibles:

**1. Permissions insuffisantes**:
- Vous avez peut-être seulement **lecture**
- Contactez votre administrateur pour obtenir **édition**

**2. Ressource verrouillée**:
- Quelqu'un d'autre est en train de la modifier
- Statut "Approuvé" ou "Publié" (modifications limitées)

**3. Workflow en cours**:
- Document en révision/approbation
- Modifications bloquées jusqu'à décision

**Vérifier vos permissions**:
- Ouvrez la ressource
- Regardez les icônes d'actions disponibles
- Si pas d'icône ✏️, vous ne pouvez pas éditer

---

### Q: Comment savoir qui peut voir ma ressource ?

**R**: Pour consulter les permissions:

1. Ouvrez la **ressource**
2. Onglet **"Permissions"** ou icône 🔒
3. Vous verrez:
   - **Utilisateurs individuels** avec leurs droits
   - **Groupes** avec leurs droits
   - **Permissions publiques** (si applicable)

**Types de permissions**:
- 👁️ **Lecture**: Peut voir
- ✏️ **Édition**: Peut modifier
- 🗑️ **Suppression**: Peut supprimer
- ✅ **Approbation**: Peut approuver/rejeter
- 👑 **Propriétaire**: Tous les droits + gestion des permissions

---

### Q: Puis-je partager une ressource avec quelqu'un ?

**R**: **Oui**, si vous avez les permissions appropriées:

**Partage Simple** (lecture seule):
1. Ouvrez la ressource
2. Bouton **"Partager"** 🔗
3. Copiez le **lien de partage**
4. Envoyez-le par email/chat

**Partage avec Permissions Spécifiques**:
1. Ouvrez la ressource
2. Onglet **"Permissions"**
3. Bouton **"Ajouter Utilisateur/Groupe"**
4. Recherchez la personne ou le groupe
5. Sélectionnez les **permissions** (lecture, édition, etc.)
6. **Sauvegardez**
7. La personne reçoit une notification

**Partage Temporaire**:
- Définissez une **date d'expiration**
- Les permissions sont automatiquement révoquées après cette date

---

### Q: Comment retirer l'accès à quelqu'un ?

**R**: Si vous êtes propriétaire ou admin:

1. Ouvrez la ressource
2. Onglet **"Permissions"**
3. Trouvez l'utilisateur/groupe
4. Cliquez sur l'icône **🗑️** ou **"Retirer"**
5. **Confirmez**
6. L'accès est immédiatement révoqué

L'utilisateur ne sera plus notifié de cette ressource.

---

## 🔍 Recherche

### Q: Pourquoi ma recherche ne retourne aucun résultat ?

**R**: Vérifiez ces points:

**1. Orthographe**:
- Vérifiez l'orthographe des mots-clés
- Essayez des variantes (singulier/pluriel)

**2. Filtres trop restrictifs**:
- Désactivez les filtres de recherche avancée
- Élargissez les plages de dates

**3. Permissions**:
- Vous ne voyez que ce que vous pouvez accéder
- La ressource existe peut-être mais vous n'avez pas les droits

**4. Indexation**:
- Les ressources très récentes (<5 min) peuvent ne pas être indexées
- Attendez quelques minutes et réessayez

**Conseils**:
- Utilisez des **mots-clés généraux** d'abord
- Recherchez par **ID** si vous l'avez
- Essayez la **recherche avancée** avec moins de filtres

---

### Q: Comment rechercher dans un dossier spécifique uniquement ?

**R**: Deux méthodes:

**Méthode 1: Navigation + Recherche**
1. **Naviguez** vers le dossier souhaité
2. Utilisez la **barre de recherche**
3. La recherche est automatiquement **limitée** à ce dossier et ses sous-dossiers

**Méthode 2: Recherche Avancée**
1. Cliquez sur **"Recherche Avancée"**
2. Filtre **"Emplacement"** ou **"Dossier"**
3. Sélectionnez le dossier dans l'arborescence
4. Lancez la recherche

---

### Q: Puis-je sauvegarder mes recherches fréquentes ?

**R**: **Oui**, fonctionnalité de recherches sauvegardées:

1. Effectuez une recherche (simple ou avancée)
2. Une fois les résultats affichés, cliquez sur **"Sauvegarder cette recherche"**
3. Donnez un **nom** à votre recherche
4. **Sauvegardez**

**Utiliser une recherche sauvegardée**:
- Icône **⭐ Recherches Sauvegardées** dans la barre de recherche
- Sélectionnez la recherche
- Résultats instantanés

**Gérer vos recherches**:
- Menu > Préférences > Recherches Sauvegardées
- Modifier, supprimer, réorganiser

---

### Q: Comment rechercher par date ?

**R**: Utilisez la **Recherche Avancée**:

1. Cliquez sur **"Recherche Avancée"**
2. Section **"Date"**:
   - **Date de création**
   - **Date de modification**
   - **Date de publication**
3. Sélectionnez:
   - **Plage de dates** (du ... au ...)
   - **Date exacte**
   - **Période relative** (aujourd'hui, cette semaine, ce mois, cette année)
4. Lancez la recherche

**Exemples**:
- "Documents créés cette semaine"
- "Livres publiés en 2024"
- "Modifications du 1er au 15 janvier"

---

### Q: La recherche est-elle sensible à la casse ?

**R**: **Non**, la recherche est **insensible à la casse** par défaut:

- `rapport` = `Rapport` = `RAPPORT`
- `Document` = `document` = `DOCUMENT`

**Pour une recherche exacte** (sensible à la casse):
- Utilisez l'option **"Respect de la casse"** en recherche avancée
- Rare nécessité

---

## 📋 Workflows et Approbations

### Q: Comment soumettre un document pour approbation ?

**R**: Processus standard:

1. **Créez** votre document (statut "Brouillon")
2. **Remplissez** tous les champs requis
3. **Attachez** les fichiers nécessaires
4. **Sauvegardez** le brouillon
5. Bouton **"Soumettre pour Révision"** ou **"Demander Approbation"**
6. (Optionnel) **Ajoutez un message** pour les réviseurs
7. **Confirmez** la soumission

Le document passe en statut **"En Révision"**.

**Notifications**:
- Vous recevez une confirmation
- Les approbateurs reçoivent une notification

---

### Q: Qui peut approuver mes documents ?

**R**: Les approbateurs sont déterminés par:

**1. Permissions**:
- Utilisateurs avec droit **"Approbation"** sur le dossier/ressource

**2. Rôles**:
- Administrateurs
- Superviseurs
- Réviseurs désignés

**3. Configuration du workflow**:
- Certains workflows nécessitent plusieurs approbations
- Ordre séquentiel ou parallèle

**Vérifier les approbateurs**:
- Vue du document > Onglet **"Workflow"**
- Section **"Approbateurs"**

---

### Q: Puis-je annuler une soumission ?

**R**: **Oui**, tant que le document n'a pas été approuvé/rejeté:

1. Ouvrez le **document**
2. Bouton **"Retirer de la Révision"** ou **"Annuler la Soumission"**
3. **Confirmez**
4. Le document retourne en statut **"Brouillon"**

**Limitations**:
- Si déjà **approuvé/rejeté**: impossible d'annuler
- Si **verrouillé** par un approbateur: attendez qu'il finisse

---

### Q: Combien de temps prend une approbation ?

**R**: Cela dépend de:

**SLA standard** (selon configuration):
- Approbations urgentes: 24 heures
- Approbations normales: 3 jours ouvrés
- Approbations complexes: 1 semaine

**Facteurs**:
- Charge de travail des approbateurs
- Complexité du document
- Priorité de la demande

**Accélérer**:
- Marquez comme **"Urgent"** (si justifié)
- Contactez directement l'approbateur
- Assurez-vous que le document est complet

**Suivre l'avancement**:
- Onglet **"Workflow"** > **"Historique"**
- Notifications automatiques à chaque étape

---

### Q: Mon document a été rejeté, que faire ?

**R**: Quand un document est rejeté:

1. **Lisez les commentaires** de l'approbateur:
   - Onglet "Workflow" > "Historique"
   - Section "Commentaires du Rejet"

2. **Corrigez** les problèmes identifiés:
   - Le document retourne automatiquement en "Brouillon"
   - Effectuez les modifications nécessaires

3. **Répondez** aux commentaires (optionnel):
   - Expliquez les changements effectués

4. **Resoumettez**:
   - Bouton "Soumettre à Nouveau"
   - Ajoutez un message: "Corrections effectuées suite à..."

**Conseil**: Contactez l'approbateur si les raisons du rejet ne sont pas claires.

---

## 📁 Digital Folders

### Q: Quelle est la profondeur maximale de l'arborescence ?

**R**: 
- **Limite technique**: 10 niveaux de profondeur
- **Recommandation**: 4-5 niveaux maximum pour la lisibilité

**Exemple d'arborescence recommandée**:
```
📁 Bibliothèque
  └─ 📁 Rapports
      └─ 📁 2025
          └─ 📁 Trimestriel
              └─ 📁 Q1
```

Si vous atteignez la limite, réorganisez votre structure.

---

### Q: Puis-je déplacer plusieurs ressources en même temps ?

**R**: **Oui**, déplacement en masse:

**Méthode Drag & Drop**:
1. **Sélectionnez** plusieurs ressources (Ctrl+clic)
2. **Glissez** vers le dossier de destination
3. **Déposez**
4. Confirmez le déplacement

**Méthode Action de Masse**:
1. **Cochez** les ressources à déplacer
2. Menu **"Actions"** > **"Déplacer"**
3. **Sélectionnez** le dossier de destination
4. **Confirmez**

**Limites**:
- Maximum 100 ressources simultanées
- Vous devez avoir les permissions sur source et destination

---

### Q: Comment créer un raccourci vers un dossier fréquemment utilisé ?

**R**: Utilisez les **Favoris**:

1. Naviguez vers le dossier
2. Cliquez sur l'icône **⭐ Ajouter aux Favoris**
3. Le dossier apparaît dans **"Mes Favoris"** (menu latéral)

**Accès rapide**:
- Cliquez sur **"Mes Favoris"** dans le menu
- Tous vos dossiers favoris listés
- Clic direct pour navigation

**Gérer les favoris**:
- Menu > Profil > Favoris
- Réorganiser par drag & drop
- Renommer (nom d'affichage uniquement)
- Supprimer

---

## 📄 Documents Numériques

### Q: Quelle est la différence entre une version et une révision ?

**R**: 

**Version**:
- Changement **majeur** du contenu
- Numérotation: 1.0, 2.0, 3.0
- Crée une nouvelle entrée dans l'historique
- Exemple: Refonte complète du document

**Révision**:
- Changement **mineur** (corrections, ajustements)
- Numérotation: 1.1, 1.2, 2.1, 2.2
- Conserve la même version majeure
- Exemple: Correction de typos, mise à jour d'une date

**Création**:
- **Version**: Upload d'un nouveau fichier principal
- **Révision**: Upload en remplaçant le fichier actuel

---

### Q: Puis-je restaurer une version précédente ?

**R**: **Oui**, fonctionnalité de restauration:

1. Ouvrez le **document**
2. Onglet **"Historique des Versions"**
3. Trouvez la version à restaurer
4. Cliquez sur **"👁️ Prévisualiser"** pour vérifier
5. Bouton **"🔄 Restaurer cette Version"**
6. **Confirmez**

**Que se passe-t-il ?**:
- La version restaurée devient la version **courante**
- L'ancienne version courante est conservée dans l'historique
- Numérotation incrémentée (ex: 3.5 restaurée devient 4.0)

**Attention**: Vous perdrez les modifications entre la version restaurée et l'actuelle (elles restent dans l'historique).

---

### Q: Comment comparer deux versions d'un document ?

**R**: Fonctionnalité de comparaison:

1. Ouvrez le document
2. Onglet **"Historique des Versions"**
3. **Sélectionnez** deux versions (Ctrl+clic)
4. Bouton **"Comparer les Versions Sélectionnées"**
5. Vue côte à côte s'affiche:
   - **Gauche**: Version ancienne
   - **Droite**: Version récente
   - **Surbrillance**: Différences

**Formats supportés**:
- Texte (TXT, MD): Diff ligne par ligne
- PDF: Comparaison visuelle
- Word/Excel: Nécessite téléchargement et comparaison externe

---

## 🏛️ Artifacts (Objets)

### Q: Quelle est la différence entre un artifact et un document ?

**R**:

**Artifact** (Objet de Musée):
- Objet **physique** (sculpture, tableau, outil ancien, etc.)
- Catalogage muséologique
- Tracking: Expositions, prêts, conservation
- Métadonnées: Dimensions, matériaux, provenance, état
- **Exemple**: Vase grec antique, tableau de Monet

**Document**:
- Fichier **numérique** ou référence documentaire
- Workflows d'approbation
- Versioning
- **Exemple**: Rapport PDF, contrat scanné

**Cas mixte**: Un artifact peut avoir des documents attachés (photos, certificats d'authenticité, rapports de conservation).

---

### Q: Comment enregistrer un prêt d'objet ?

**R**: Processus de prêt:

1. Ouvrez l'**artifact**
2. Onglet **"Prêts"**
3. Bouton **"+ Nouveau Prêt"**
4. Remplissez:
   - **Emprunteur**: Personne/institution
   - **Date de départ**
   - **Date de retour prévue**
   - **Raison**: Exposition, recherche, restauration...
   - **Conditions**: Assurance, transport, etc.
   - **Documents**: Contrat de prêt, certificat, etc.
5. **Sauvegardez**

**Statuts**:
- 📤 **Prêté**: L'objet est sorti
- 📥 **Retourné**: L'objet est revenu
- ⏰ **En retard**: Date de retour dépassée (alerte automatique)

**Historique**: Tous les prêts passés sont conservés dans l'historique.

---

### Q: Comment planifier une exposition ?

**R**: Création d'exposition:

1. Menu **Artifacts** > **Expositions**
2. Bouton **"+ Nouvelle Exposition"**
3. Remplissez:
   - **Titre** de l'exposition
   - **Dates** (début et fin)
   - **Lieu**
   - **Description** et thématique
   - **Curateur** (responsable)
4. **Sauvegardez** l'exposition
5. **Ajoutez des objets**:
   - Onglet "Objets Exposés"
   - Bouton "+ Ajouter Objet"
   - Recherchez et sélectionnez les artifacts
   - Définissez l'ordre d'exposition

**Suivi**:
- Statut: Planifiée, En cours, Terminée, Annulée
- Notifications automatiques avant début/fin
- Rapport de fréquentation (si intégré)

---

## 📚 Livres

### Q: Comment cataloguer un livre sans ISBN ?

**R**: Pour les livres anciens ou sans ISBN:

1. Créez le livre normalement
2. Laissez le champ **ISBN vide**
3. Remplissez les autres champs:
   - **Titre** (obligatoire)
   - **Auteur(s)** (obligatoire)
   - **Année de publication**
   - **Éditeur**
   - **Lieu de publication**
4. Utilisez les **identifiants alternatifs**:
   - **OCLC Number**
   - **LCCN** (Library of Congress Control Number)
   - **Numéro de catalogue interne**
5. **Sauvegardez**

**Conseil**: Ajoutez des notes détaillées pour identifier le livre unique.

---

### Q: Comment gérer plusieurs exemplaires du même livre ?

**R**: Système de copies/exemplaires:

1. Créez le **livre** (notice bibliographique unique)
2. Onglet **"Exemplaires"**
3. Bouton **"+ Ajouter Exemplaire"**
4. Pour chaque exemplaire, définissez:
   - **Code-barres** ou **Numéro d'inventaire**
   - **Localisation** (rayon, étagère)
   - **État**: Neuf, Bon, Usé, Abîmé, etc.
   - **Disponibilité**: Disponible, Emprunté, En réparation, etc.
   - **Prix d'acquisition**
   - **Date d'acquisition**
5. **Sauvegardez** chaque exemplaire

**Avantages**:
- Tracking individuel de chaque exemplaire physique
- Gestion des prêts par exemplaire
- Statistiques d'utilisation

**Exemple**:
- Livre: "Les Misérables" de Victor Hugo
  - Exemplaire #1: Code-barres 12345, Rayon A-15, Disponible
  - Exemplaire #2: Code-barres 12346, Rayon A-15, Emprunté
  - Exemplaire #3: Code-barres 12347, Réserve, En réparation

---

### Q: Comment exporter une bibliographie ?

**R**: Export bibliographique:

1. **Sélectionnez** les livres à exporter:
   - Recherchez ou filtrez
   - Cochez les livres souhaités (ou "Tout sélectionner")
2. Bouton **"Actions"** > **"Exporter"**
3. Choisissez le **format**:
   - **BibTeX** (.bib) - Pour LaTeX
   - **RIS** (.ris) - Pour EndNote, Zotero, Mendeley
   - **CSV** (.csv) - Pour Excel
   - **JSON** (.json) - Format structuré
4. **Téléchargez** le fichier

**Utilisation**:
- Importez dans votre gestionnaire de références
- Incluez dans vos publications académiques
- Sauvegardez pour backup

---

## 📰 Périodiques

### Q: Quelle est la différence entre un périodique et un numéro ?

**R**:

**Périodique** (Publication):
- La **série complète** (le titre général)
- Informations globales: ISSN, éditeur, fréquence
- **Exemple**: "Nature", "Le Monde", "National Geographic"

**Numéro** (Issue):
- Une **parution spécifique** du périodique
- Identifié par: Volume, Numéro, Date
- **Exemple**: "Nature, Volume 615, Numéro 7954, Mars 2025"

**Relation**:
```
📰 Périodique: "Nature"
  ├─ 📅 Numéro: Vol 614, #7952, Jan 2025
  ├─ 📅 Numéro: Vol 614, #7953, Fév 2025
  └─ 📅 Numéro: Vol 615, #7954, Mar 2025
      ├─ 📄 Article 1
      ├─ 📄 Article 2
      └─ 📄 Article 3
```

---

### Q: Comment ajouter un numéro à un périodique existant ?

**R**: Ajout de numéro:

1. Ouvrez le **périodique**
2. Onglet **"Numéros"**
3. Bouton **"+ Ajouter Numéro"**
4. Remplissez:
   - **Volume**
   - **Numéro**
   - **Date de publication**
   - **Titre du numéro** (si thématique spéciale)
   - **Pages** (ex: 1-250)
   - **DOI** du numéro (si disponible)
5. **Sauvegardez**

**Ensuite**, ajoutez les **articles**:
1. Depuis le numéro, onglet **"Articles"**
2. Bouton **"+ Ajouter Article"**
3. Pour chaque article:
   - Titre, auteur(s), pages, DOI
   - Résumé, mots-clés

---

### Q: Comment suivre un abonnement à un périodique ?

**R**: Gestion d'abonnement:

1. Ouvrez le **périodique**
2. Onglet **"Abonnement"**
3. Bouton **"+ Nouvel Abonnement"** ou **"Renouveler"**
4. Informations:
   - **Date de début**
   - **Date de fin**
   - **Type**: Print, Électronique, Les deux
   - **Fournisseur**
   - **Coût**
   - **Numéro de contrat**
   - **Modalités d'accès** (pour électronique)
5. **Sauvegardez**

**Notifications**:
- Alerte 30 jours avant expiration
- Rappels de renouvellement

**Statistiques**:
- Coût par an
- Taux d'utilisation (si tracking activé)
- ROI de l'abonnement

---

## 🔌 API

### Q: Comment obtenir une clé API ?

**R**: Génération de clé API (si autorisé):

1. Menu **Profil** > **API**
2. Section **"Tokens d'Accès"**
3. Bouton **"+ Générer Nouveau Token"**
4. Remplissez:
   - **Nom** du token (ex: "Script de Backup")
   - **Expiration**: 30 jours, 90 jours, 1 an, jamais
   - **Permissions**: Lecture seule, Lecture/Écriture, Admin
   - **Scope**: API endpoints autorisés
5. **Générez**
6. **Copiez** le token immédiatement (affiché une seule fois)
7. **Sauvegardez-le** en sécurité

**Utilisation**:
```bash
curl -H "Authorization: Bearer VOTRE_TOKEN" \
  https://shelve.local/api/v1/books
```

**Sécurité**:
- Ne partagez JAMAIS votre token
- Révoquez les tokens inutilisés
- Utilisez des tokens courts pour scripts temporaires

---

### Q: Quelle est la limite de requêtes API ?

**R**: **Rate limiting** par défaut:

**Par Token**:
- **60 requêtes par minute** (tier gratuit/standard)
- **300 requêtes par minute** (tier premium - si applicable)

**Quotas**:
- **10,000 requêtes par jour** (standard)
- **100,000 requêtes par jour** (premium)

**Headers de Réponse**:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1699876543
```

**Que se passe-t-il si dépassement ?**:
- **429 Too Many Requests**
- Attendez jusqu'à `X-RateLimit-Reset`
- Ou implémentez un backoff exponentiel

**Augmenter les limites**:
- Contactez votre administrateur
- Justifiez le besoin (usage légitime)

---

### Q: Où trouver la documentation API ?

**R**: Documentation API interactive:

**1. Swagger UI Intégré**:
- URL: `https://votre-shelve.local/api/documentation`
- Documentation complète OpenAPI 3.0
- **Try it out** pour tester directement

**2. Fichier OpenAPI**:
- URL: `https://votre-shelve.local/api-docs/openapi.yaml`
- Format: OpenAPI 3.0 (YAML ou JSON)
- Importable dans Postman, Insomnia, etc.

**3. Guide API Utilisateurs**:
- Documentation: `/docs/API_USER_GUIDE.md`
- Exemples par langage (PHP, Python, JavaScript, etc.)
- Cas d'usage courants

---

## ⚡ Performance

### Q: Le système est lent, que faire ?

**R**: Étapes de dépannage:

**1. Vérifiez votre connexion**:
- Test de vitesse: https://fast.com
- Minimum recommandé: 5 Mbps

**2. Videz le cache du navigateur**:
- Chrome: Ctrl+Shift+Del > Vider le cache
- Firefox: Ctrl+Shift+Del > Cookies et Cache
- Redémarrez le navigateur

**3. Rechargez la page**:
- **Rafraîchissement simple**: F5
- **Rafraîchissement forcé**: Ctrl+F5 (ignore le cache)

**4. Essayez un autre navigateur**:
- Recommandés: Chrome, Firefox, Edge (dernières versions)

**5. Vérifiez le statut du système**:
- Page de statut: `https://votre-shelve.local/status`
- Contactez le support si problème général

**Optimisations côté utilisateur**:
- Fermez les onglets inutilisés
- Désactivez les extensions de navigateur (mode incognito pour tester)
- Utilisez un réseau filaire plutôt que Wi-Fi si possible

---

### Q: Combien de temps prend un upload ?

**R**: Temps estimés selon connexion:

**Connexion Lente (1 Mbps)**:
- 1 MB: ~8 secondes
- 10 MB: ~80 secondes
- 50 MB: ~400 secondes (6-7 minutes)

**Connexion Moyenne (10 Mbps)**:
- 1 MB: ~1 seconde
- 10 MB: ~10 secondes
- 50 MB: ~50 secondes

**Connexion Rapide (100 Mbps)**:
- 1 MB: <1 seconde
- 10 MB: ~1 seconde
- 50 MB: ~5 secondes

**Facteurs**:
- Vitesse de votre connexion (upload, pas download)
- Charge du serveur
- Taille et nombre de fichiers
- Compression appliquée

**Conseil**: Compressez les gros fichiers (ZIP) avant upload si possible.

---

### Q: Pourquoi la recherche est-elle lente ?

**R**: Causes possibles:

**1. Volume de données**:
- Recherche sur des millions de ressources
- Normale sur grandes bases

**2. Requête trop générale**:
- Mot très commun (ex: "le", "de")
- Retourne trop de résultats
- **Solution**: Soyez plus spécifique

**3. Filtres complexes**:
- Multiples filtres combinés
- Plages de dates larges
- **Solution**: Réduisez les filtres

**4. Indexation en cours**:
- Après ajout massif de ressources
- Index en reconstruction
- **Solution**: Attendez la fin de l'indexation

**Optimisations**:
- Utilisez des **mots-clés précis**
- **Limitez** le scope (recherche dans un dossier)
- Utilisez la **recherche avancée** avec filtres ciblés
- **Sauvegardez** les recherches fréquentes

---

## 💾 Données et Export

### Q: Puis-je exporter mes données ?

**R**: **Oui**, plusieurs formats d'export:

**Export Simple** (une ressource):
1. Ouvrez la ressource
2. Menu **"..."** > **"Exporter"**
3. Choisissez le format:
   - **PDF**: Vue imprimable
   - **JSON**: Données structurées
   - **XML**: Format standard
4. Téléchargez

**Export en Masse** (plusieurs ressources):
1. **Sélectionnez** les ressources (liste ou recherche)
2. **Cochez** celles à exporter (ou "Tout sélectionner")
3. Menu **"Actions"** > **"Exporter"**
4. Choisissez:
   - **Excel** (.xlsx) - Tableau avec métadonnées
   - **CSV** (.csv) - Import dans autres systèmes
   - **JSON** (.json) - Format structuré
   - **ZIP** (.zip) - Avec fichiers attachés
5. **Téléchargez**

**Limites**:
- Export masse: Maximum 1,000 ressources à la fois
- Export avec fichiers: Maximum 2 GB total

---

### Q: Comment importer des données en masse ?

**R**: Import CSV/Excel:

**Préparation**:
1. **Téléchargez le template** pour votre type de ressource:
   - Menu > Import/Export > Templates
   - Choisissez (Books, Documents, Artifacts, etc.)
2. **Remplissez** le fichier Excel/CSV:
   - Suivez exactement les colonnes du template
   - Respectez les formats (dates, nombres, etc.)
   - Champs obligatoires marqués
3. **Sauvegardez** le fichier

**Import**:
1. Menu du module > **"Importer"**
2. **Uploadez** votre fichier
3. **Mappez** les colonnes (si nécessaire)
4. **Prévisualisez** (vérifiez avant import)
5. **Lancez l'import**

**Résultat**:
- Rapport d'import (succès/échecs)
- Erreurs détaillées pour corrections
- Possibilité de réessayer les échecs

**Conseils**:
- Testez avec 10-20 lignes d'abord
- Vérifiez les encodages (UTF-8 recommandé)
- Dates au format ISO: YYYY-MM-DD

---

### Q: Les suppressions sont-elles définitives ?

**R**: **Ça dépend du type de ressource**:

**Suppression Définitive** (immédiate):
- Fichiers attachés
- Commentaires
- Certaines métadonnées

**Suppression Douce** (archivage 30 jours):
- Documents
- Livres
- Périodiques
- Artifacts

**Comment ça marche ?**:
1. Vous **supprimez** une ressource
2. Elle va dans la **"Corbeille"**
3. **30 jours** de rétention
4. Suppression définitive automatique après 30 jours

**Restaurer depuis la Corbeille**:
1. Menu > **"Corbeille"**
2. Trouvez la ressource
3. Bouton **"🔄 Restaurer"**
4. La ressource revient à son emplacement d'origine

**Vider la Corbeille**:
- Supprime **définitivement** tout
- **Irréversible**
- Réservé aux administrateurs

---

## 🔒 Sécurité

### Q: Comment créer un mot de passe sécurisé ?

**R**: **Bonnes pratiques**:

**Règles**:
- **Minimum 12 caractères** (recommandé 16+)
- **Mélangez**:
  - Majuscules (A-Z)
  - Minuscules (a-z)
  - Chiffres (0-9)
  - Symboles (!@#$%^&*)
- **Évitez**:
  - Mots du dictionnaire
  - Informations personnelles (nom, date de naissance)
  - Séquences (123456, abcdef)
  - Mots de passe réutilisés

**Méthodes**:

**1. Phrase de passe** (recommandé):
```
J'adore$LesChats&LesCafés!2025
```
- Facile à retenir
- Très sécurisé (longueur + complexité)

**2. Générateur aléatoire**:
```
x9K#mL2@qR7$vN4!
```
- Maximum de sécurité
- Utilisez un gestionnaire de mots de passe

**Gestionnaires Recommandés**:
- 1Password
- LastPass
- Bitwarden (open source)
- KeePass (local)

---

### Q: L'authentification à deux facteurs (2FA) est-elle disponible ?

**R**: **Oui** (si activé par l'admin):

**Activation 2FA**:
1. Menu **Profil** > **Sécurité**
2. Section **"Authentification à Deux Facteurs"**
3. Bouton **"Activer 2FA"**
4. Choisissez la méthode:
   - **App d'authentification** (Google Authenticator, Authy) - Recommandé
   - **SMS** (moins sécurisé)
   - **Email** (moins sécurisé)
5. **Scannez** le QR code avec votre app
6. **Saisissez** le code de confirmation
7. **Sauvegardez** les codes de récupération (10 codes à usage unique)

**Utilisation**:
- Connexion normale (email + mot de passe)
- **Puis**: Code 2FA demandé
- Saisissez le code de votre app
- Connexion réussie

**Codes de Récupération**:
- Si vous perdez votre téléphone
- Utilisez un code de récupération
- **Conservez-les en sécurité** (coffre, gestionnaire de mots de passe)

---

### Q: Comment détecter une activité suspecte sur mon compte ?

**R**: **Surveillance du compte**:

**1. Consultez les Sessions Actives**:
- Menu **Profil** > **Sécurité** > **Sessions Actives**
- Voir:
  - Appareil (OS, navigateur)
  - Localisation (IP, ville)
  - Dernière activité
- **Déconnectez** les sessions suspectes

**2. Historique de Connexion**:
- Menu **Profil** > **Sécurité** > **Historique**
- Liste de toutes les connexions (réussies et échouées)
- Filtrez par date, IP, appareil

**3. Notifications d'Activité**:
- Activez: **Profil** > **Notifications**
- Options:
  - ✅ Connexion depuis nouveau appareil
  - ✅ Connexion depuis nouvelle localisation
  - ✅ Changement de mot de passe
  - ✅ Modification de permissions

**Signes d'Activité Suspecte**:
- 🚨 Connexions depuis pays inconnus
- 🚨 Appareils non reconnus
- 🚨 Activité aux heures inhabituelles
- 🚨 Modifications non autorisées

**En cas de Doute**:
1. **Changez votre mot de passe** immédiatement
2. **Déconnectez** toutes les sessions
3. **Activez 2FA** si pas déjà fait
4. **Contactez le support** si compromission confirmée

---

## 🛠️ Support Technique

### Q: Comment contacter le support ?

**R**: Plusieurs canaux disponibles:

**1. Support Email**:
- 📧 **Email**: support@shelve.local (remplacez par votre email réel)
- Réponse: < 24h ouvrées (urgences: < 4h)

**2. Support Téléphonique** (si disponible):
- 📞 **Téléphone**: +33 (0)1 XX XX XX XX
- Horaires: Lundi-Vendredi, 9h-18h (heure locale)

**3. Chat en Ligne** (si disponible):
- 💬 Icône de chat (coin inférieur droit)
- Disponible: Lundi-Vendredi, 9h-18h

**4. Support Interne**:
- Contactez votre **administrateur SHELVE** local
- **Forum interne** (si disponible)
- **Base de connaissance** interne

**5. Documentation**:
- 📘 Manuels utilisateur (`/docs`)
- ❓ FAQ (ce document)
- 🎥 Tutoriels vidéo (si disponibles)

---

### Q: Comment signaler un bug ?

**R**: **Processus de signalement**:

**1. Collectez les Informations**:
- **Quoi**: Décrivez le problème clairement
- **Quand**: Date et heure
- **Où**: Page/module concerné
- **Comment reproduire**: Étapes exactes
- **Message d'erreur**: Copie complète (si affiché)
- **Captures d'écran**: Très utiles
- **Environnement**:
  - Navigateur et version (ex: Chrome 120)
  - OS (Windows 11, macOS 14, etc.)
  - Taille d'écran (si problème d'affichage)

**2. Vérifiez si Déjà Connu**:
- Consultez la **FAQ**
- Recherchez dans les **bugs connus** (si liste disponible)

**3. Soumettez le Bug**:

**Via Formulaire Intégré**:
- Menu **Aide** > **Signaler un Bug**
- Remplissez le formulaire
- Attachez captures d'écran
- Soumettez

**Via Email**:
- Email à: bugs@shelve.local
- Sujet: `[BUG] Titre court du problème`
- Incluez toutes les infos collectées

**4. Suivi**:
- Vous recevez un **numéro de ticket**
- Mises à jour par email
- Délai de résolution selon gravité:
  - **Critique**: < 24h
  - **Élevé**: < 3 jours
  - **Moyen**: < 1 semaine
  - **Faible**: Prochaine release

---

### Q: Puis-je demander une nouvelle fonctionnalité ?

**R**: **Oui**, processus de demande:

**1. Vérifiez si Existe Déjà**:
- Consultez la **feuille de route** (roadmap - si publique)
- Recherchez dans les **demandes existantes**

**2. Soumettez votre Demande**:

**Via Formulaire**:
- Menu **Aide** > **Demander une Fonctionnalité**
- Remplissez:
  - **Titre** clair et concis
  - **Description** détaillée
  - **Bénéfice** attendu (pourquoi c'est utile)
  - **Cas d'usage** (exemples concrets)
  - **Priorité** (votre perception)
  - **Alternatives** (si vous en connaissez)

**Via Email**:
- Email à: features@shelve.local
- Sujet: `[FEATURE] Titre de la demande`

**3. Processus de Validation**:
1. **Réception**: Confirmation de réception
2. **Évaluation**: Faisabilité technique, priorité business
3. **Priorisation**: Intégration ou non dans la roadmap
4. **Communication**: Retour sur décision (accepté, refusé, reporté)

**4. Suivi**:
- Si acceptée: Ajout à la roadmap avec timeline estimée
- Si refusée: Explication des raisons
- Si reportée: Réévaluation ultérieure

**Conseil**: Plus votre demande est détaillée et justifiée, plus elle a de chances d'être acceptée.

---

### Q: Où trouver les tutoriels et formations ?

**R**: **Ressources d'apprentissage**:

**1. Documentation Écrite**:
- **Manuel Utilisateur**: `/docs/USER_MANUAL.md` (ce document)
- **Guides par Module**: `/docs/USER_GUIDE_*.md`
- **FAQ**: `/docs/FAQ.md` (ce document)
- **API Guide**: `/docs/API_USER_GUIDE.md`

**2. Tutoriels Vidéo** (si disponibles):
- Menu **Aide** > **Tutoriels Vidéo**
- Catégories:
  - Démarrage rapide (10 min)
  - Par module (15-30 min chacun)
  - Fonctionnalités avancées
  - API et intégrations

**3. Sessions de Formation**:
- **Formation Utilisateur de Base**: 2 heures
- **Formation Catalogueur**: 4 heures
- **Formation Administrateur**: 4 heures
- Contactez votre responsable pour inscription

**4. Webinaires** (si organisés):
- Sessions mensuelles sur nouveautés
- Q&A en direct
- Enregistrements disponibles

**5. Base de Connaissance** (si disponible):
- Articles pratiques
- Trucs et astuces
- Bonnes pratiques

**6. Communauté** (si disponible):
- Forum utilisateurs
- Partage d'expériences
- Entre-aide

---

## 📞 Contacts Utiles

**Support Technique**:
- Email: support@shelve.local
- Téléphone: +33 (0)1 XX XX XX XX
- Horaires: Lun-Ven 9h-18h

**Signalement Bugs**:
- Email: bugs@shelve.local

**Demandes de Fonctionnalités**:
- Email: features@shelve.local

**Formation**:
- Email: training@shelve.local

**Administrateur Local**:
- Contactez votre responsable interne

**Sécurité/DPO**:
- Email: security@shelve.local (incidents de sécurité)
- Email: dpo@shelve.local (RGPD, données personnelles)

---

## 📋 Annexe: Codes d'Erreur Courants

| Code | Message | Signification | Solution |
|------|---------|---------------|----------|
| 400 | Bad Request | Données invalides | Vérifiez le formulaire |
| 401 | Unauthorized | Non authentifié | Reconnectez-vous |
| 403 | Forbidden | Pas de permissions | Demandez accès à l'admin |
| 404 | Not Found | Ressource introuvable | Vérifiez l'URL/ID |
| 413 | Payload Too Large | Fichier trop gros | Réduisez taille < 50 MB |
| 422 | Unprocessable Entity | Données non valides | Corrigez les erreurs |
| 429 | Too Many Requests | Rate limit dépassé | Attendez et réessayez |
| 500 | Internal Server Error | Erreur serveur | Contactez le support |
| 503 | Service Unavailable | Maintenance en cours | Attendez ou contactez admin |

---

**Cette FAQ sera mise à jour régulièrement avec vos questions.**

**Dernière Mise à Jour**: 7 novembre 2025  
**Version**: 1.0  
**Prochaine Révision**: Décembre 2025

**💡 Votre question n'est pas ici ?** Contactez le support !
