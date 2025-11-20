# 🛒 Système Dolly Digital - Guide d'utilisation

## 📖 Vue d'ensemble

Le système **Dolly Digital** permet de gérer des "chariots" (dollies) pour organiser et traiter par lots différents types d'entités numériques et documentaires.

### Entités gérées (15 types)
- 📧 Courriers (mail)
- 📝 Communications
- 📁 Archives physiques (record)
- 🏢 Bâtiments, salles, rayonnages
- 🗂️ **Dossiers numériques** ⭐
- 📄 **Documents numériques** ⭐
- 🏺 **Artefacts** ⭐
- 📚 **Livres** ⭐
- 📖 **Séries d'éditeur** ⭐

⭐ = Nouvelles entités ajoutées

---

## 🚀 Démarrage rapide

### 1. Créer un chariot
```
Menu > Gestion des chariots > Créer un chariot
├─ Nom: Mon chariot de livres
├─ Description: Livres à cataloguer
└─ Catégorie: 📚 Livres
```

### 2. Ajouter des éléments
```
Chariot créé > Rechercher des livres
├─ Sélectionner un livre
└─ Cliquer sur "Ajouter au chariot"
```

### 3. Exporter les données
```
Voir le chariot > Boutons d'export
├─ 📄 Export PDF (inventaire)
├─ 📋 Export ISBD (format bibliographique)
└─ 💾 Export MARC (format de catalogage)
```

---

## 📊 Fonctionnalités par entité

### 📁 Dossiers numériques
**Actions disponibles:**
- ➕ Ajouter/Retirer du chariot
- 📦 Export SEDA 2.1 (archivage électronique)
- 📄 Export inventaire PDF
- 🧹 Vider le chariot
- 🗑️ Supprimer les éléments

**Cas d'usage:**
- Préparer un versement d'archives numériques
- Générer un bordereau SEDA
- Créer un inventaire pour validation

### 📄 Documents numériques
**Actions disponibles:**
- ➕ Ajouter/Retirer du chariot
- 📦 Export SEDA 2.1
- 📄 Export inventaire PDF
- 🧹 Vider le chariot
- 🗑️ Supprimer les éléments

**Cas d'usage:**
- Traiter un lot de documents numérisés
- Préparer l'archivage définitif
- Générer un rapport de traitement

### 🏺 Artefacts (objets de musée)
**Actions disponibles:**
- ➕ Ajouter/Retirer du chariot
- 📄 Export inventaire PDF
- 🧹 Vider le chariot
- 🗑️ Supprimer les éléments

**Cas d'usage:**
- Préparer une exposition
- Inventorier une collection
- Créer un catalogue

### 📚 Livres
**Actions disponibles:**
- ➕ Ajouter/Retirer du chariot
- 📄 Export inventaire PDF
- 📋 Export ISBD (description bibliographique)
- 💾 Export MARC (catalogage)
- 📥 Import ISBD (ajouter des livres)
- 📥 Import MARC (ajouter des livres)
- 🧹 Vider le chariot
- 🗑️ Supprimer les éléments

**Cas d'usage:**
- Cataloguer de nouvelles acquisitions
- Générer des notices bibliographiques
- Échanger des données avec d'autres bibliothèques
- Importer des notices depuis un fichier

### 📖 Séries d'éditeur
**Actions disponibles:**
- ➕ Ajouter/Retirer du chariot
- 📄 Export inventaire PDF
- 📋 Export ISBD (publications en série)
- 💾 Export MARC (périodiques)
- 📥 Import ISBD
- 📥 Import MARC
- 🧹 Vider le chariot
- 🗑️ Supprimer les éléments

**Cas d'usage:**
- Gérer des collections complètes
- Cataloguer des périodiques
- Suivre les volumes d'une série

---

## 📤 Guide des exports

### 1. Export SEDA 2.1 XML
**Pour:** Dossiers et documents numériques  
**Format:** XML conforme au standard français d'archivage électronique  
**Utilisation:** Versement aux Archives nationales ou départementales

**Contenu:**
```xml
<ArchiveTransfer>
  <Date>2025-11-20T15:30:00+00:00</Date>
  <MessageIdentifier>DOLLY_123_...</MessageIdentifier>
  <DataObjectPackage>
    <ArchiveUnit id="FOLDER_45">
      <Content>
        <Title>Mon dossier</Title>
        <Description>Description du dossier</Description>
      </Content>
    </ArchiveUnit>
  </DataObjectPackage>
</ArchiveTransfer>
```

### 2. Export inventaire PDF
**Pour:** Toutes les entités  
**Format:** PDF avec tableau formaté  
**Utilisation:** Impression, validation, rapport

**Contenu:**
- En-tête du chariot (nom, description, date)
- Tableau des éléments (code, nom, description, dates)
- Compteur total
- Pied de page avec date de génération

### 3. Export ISBD (International Standard Bibliographic Description)
**Pour:** Livres et séries d'éditeur  
**Format:** Texte formaté selon norme IFLA  
**Utilisation:** Description bibliographique normalisée

**Exemple pour un livre:**
```
Les Misérables / Victor Hugo
. - Première édition
. - Paris : Librairie Générale Française, 1985
. - 1488 p.
ISBN 2-253-09681-1
```

**Zones ISBD:**
- Zone 1: Titre et responsabilité
- Zone 2: Édition
- Zone 4: Publication
- Zone 5: Description physique
- Zone 8: ISBN/ISSN

### 4. Export MARC21
**Pour:** Livres et séries d'éditeur  
**Format:** Machine-Readable Cataloging (Library of Congress)  
**Utilisation:** Échange avec systèmes de bibliothèques, OPAC

**Exemple:**
```
=LDR  00000nam  2200000   4500
=001  0000000123
=020  \\$a2253096811
=100  1\$aHugo, Victor
=245  10$aLes Misérables
=260  \\$bLibrairie Générale Française$c1985
=300  \\$a1488 p.
```

**Champs MARC:**
- LDR: Leader (type de notice)
- 020: ISBN
- 100: Auteur principal
- 245: Titre
- 260: Publication
- 300: Description physique

---

## 📥 Guide des imports

### Import ISBD (Livres)
**Étapes:**
1. Créer un chariot de type "Livre"
2. Cliquer sur "Importer ISBD"
3. Préparer un fichier `.txt` avec format ISBD
4. Sélectionner l'encodage (UTF-8 recommandé)
5. Uploader le fichier

**Format attendu:**
```
Les Misérables / Victor Hugo. -
Première édition. -
Paris : Librairie Générale Française, 1985. -
1488 p. -
ISBN 2-253-09681-1

(ligne vide = séparation entre livres)

Germinal / Émile Zola. -
...
```

### Import MARC (Livres)
**Étapes:**
1. Créer un chariot de type "Livre"
2. Cliquer sur "Importer MARC"
3. Préparer un fichier `.mrc` ou `.txt`
4. Sélectionner le format (texte lisible ou binaire)
5. Uploader le fichier

**Format attendu (MARC texte):**
```
=LDR  00000nam  2200000   4500
=001  123456789
=020  \\$a2253096811
=100  1\$aHugo, Victor
=245  10$aLes Misérables
=260  \\$bLibrairie Générale Française$c1985

(ligne vide = séparation entre notices)
```

---

## 🔧 Opérations avancées

### Vider un chariot (Clean)
**Action:** Retire tous les éléments du chariot sans les supprimer de la base  
**Résultat:** Chariot vide, éléments toujours dans le système  
**Utilisation:** Recommencer une sélection, réorganiser

### Supprimer un chariot (Delete)
**Action:** Supprime les éléments ET le chariot de la base de données  
**⚠️ ATTENTION:** Action irréversible !  
**Utilisation:** Nettoyage définitif, suppression de doublons

### Filtrage par organisation
**Automatique:** Seuls les éléments de votre organisation sont visibles  
**Sécurité:** Isolation des données entre organisations  
**Configuration:** Basé sur `current_organisation_id` de l'utilisateur

---

## 💡 Bonnes pratiques

### Nommage des chariots
```
✅ BON: "Acquisitions novembre 2025 - Livres"
✅ BON: "Versement archives numériques - Dossier RH"
❌ MAUVAIS: "Mon chariot"
❌ MAUVAIS: "Test123"
```

### Organisation du travail
1. **Un chariot = Un projet**
   - Ex: Catalogage mensuel, Préparation exposition, Versement annuel

2. **Description détaillée**
   - Mentionner le contexte, la date, l'objectif

3. **Export régulier**
   - Générer des PDF d'inventaire pour traçabilité

4. **Nettoyage périodique**
   - Vider ou supprimer les chariots terminés

### Workflows recommandés

**Catalogage de livres:**
```
1. Créer chariot "Acquisitions [mois]"
2. Ajouter les livres reçus
3. Export PDF pour validation
4. Export MARC pour intégration OPAC
5. Vider le chariot après traitement
```

**Archivage numérique:**
```
1. Créer chariot "Versement [année] - [service]"
2. Ajouter dossiers numériques
3. Export SEDA pour bordereau de versement
4. Export PDF pour dossier papier
5. Supprimer après archivage validé
```

**Préparation d'exposition:**
```
1. Créer chariot "Exposition [thème]"
2. Ajouter artefacts sélectionnés
3. Export PDF pour liste de récolement
4. Garder le chariot pendant l'exposition
5. Vider après retour en réserve
```

---

## 🆘 Dépannage

### Le bouton "Ajouter au chariot" ne fonctionne pas
- Vérifier que le chariot est de la bonne catégorie
- Vérifier que l'élément n'est pas déjà dans le chariot
- Vérifier les permissions de votre compte

### L'export PDF est vide
- Vérifier que le chariot contient des éléments
- Vérifier que la bibliothèque DomPDF est installée
- Consulter les logs Laravel

### L'import ISBD/MARC échoue
- Vérifier le format du fichier (zones correctes)
- Vérifier l'encodage (UTF-8 recommandé)
- Vérifier qu'il n'y a pas de caractères spéciaux invalides

### Les éléments ne s'affichent pas
- Vérifier votre organisation (filtrage automatique)
- Vérifier que les éléments ne sont pas archivés
- Rafraîchir la page

---

## 📞 Support

Pour toute question ou problème:
- 📧 Email: support@votre-organisation.fr
- 📚 Documentation: [lien vers doc complète]
- 🐛 Bug report: [lien vers système de tickets]

---

## 📝 Changelog

### Version 2.0 (20 novembre 2025)
- ➕ Ajout de 5 nouvelles entités digitales
- 📦 Export SEDA 2.1 pour archivage électronique
- 📋 Export ISBD pour description bibliographique
- 💾 Export MARC21 pour catalogage
- 📥 Formulaires d'import ISBD/MARC
- 🎨 Interface améliorée (layout 3 colonnes)
- 🔒 Filtrage par organisation renforcé

### Version 1.0 (Antérieur)
- Gestion des courriers, archives, communications
- Chariots basiques
- Exports PDF simples

---

**🎉 Merci d'utiliser le système Dolly Digital !**

**Support technique:** Consultez `IMPLEMENTATION_DOLLY_DIGITAL.md` pour détails techniques.
