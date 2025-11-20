# 🎨 BPMN Workflow Designer - Guide d'utilisation

## 📋 Vue d'ensemble

L'interface **BPMN Workflow Designer** permet de créer visuellement des workflows conformes au standard BPMN 2.0 grâce à un système de **drag & drop** intuitif.

---

## 🚀 Fonctionnalités principales

### ✨ Interface Double Mode

#### 1. **Mode Visuel** (Recommandé)
- 🎯 Création par glisser-déposer
- 🖱️ Interface intuitive
- 🎨 Prévisualisation en temps réel
- ⚙️ Éditeur de propriétés
- 🔄 Génération XML automatique

#### 2. **Mode Code XML**
- 💻 Édition directe du XML BPMN
- 🔍 Contrôle total sur la configuration
- 📝 Syntaxe coloration
- ✅ Validation en temps réel

---

## 🎨 Palette d'éléments BPMN

### 🟢 Événements

#### Début (Start Event)
- **Icône:** 🟢 Play Circle
- **Utilisation:** Point de départ du workflow
- **Règle:** Un seul par processus
- **Exemple:** "Demande reçue", "Processus lancé"

#### Fin (End Event)
- **Icône:** 🔴 Stop Circle
- **Utilisation:** Point de terminaison du workflow
- **Règle:** Peut avoir plusieurs fins (succès, échec, annulation)
- **Exemple:** "Demande approuvée", "Demande rejetée"

#### Intermédiaire (Intermediate Event)
- **Icône:** 🟡 Circle
- **Utilisation:** Événement durant le processus
- **Exemple:** "Attendre validation", "Notification envoyée"

---

### 📋 Activités

#### Tâche (Task)
- **Icône:** ✅ Check Square
- **Utilisation:** Activité générique
- **Exemple:** "Traiter document"

#### Tâche Utilisateur (User Task)
- **Icône:** 👤 Person Check
- **Utilisation:** Action nécessitant une intervention humaine
- **Exemple:** "Valider la demande", "Remplir formulaire"
- **Propriétés:** Assignation, formulaire, délai

#### Tâche Service (Service Task)
- **Icône:** ⚙️ Gear
- **Utilisation:** Appel à un service automatique
- **Exemple:** "Envoyer email", "Appel API", "Calcul automatique"

#### Script (Script Task)
- **Icône:** 💻 Code Square
- **Utilisation:** Exécution de code/script
- **Exemple:** "Calcul de scores", "Transformation de données"

---

### 💎 Portes logiques (Gateways)

#### XOR (Exclusive Gateway)
- **Icône:** 💛 Diamond
- **Utilisation:** Choix exclusif (UN seul chemin)
- **Exemple:** "Si montant > 1000€ ALORS validation manager SINON auto-approuvé"
- **Symbole:** ❌ ou vide

#### AND (Parallel Gateway)
- **Icône:** 💚 Plus Diamond
- **Utilisation:** Tous les chemins en parallèle
- **Exemple:** "Validation juridique ET validation financière ET validation technique"
- **Symbole:** +

#### OR (Inclusive Gateway)
- **Icône:** 💙 Circle Square
- **Utilisation:** Un ou plusieurs chemins
- **Exemple:** "Notification email OU notification SMS OU notification push"
- **Symbole:** ○

---

### 📦 Sous-processus

#### Sous-processus (Sub-Process)
- **Icône:** 📦 Box
- **Utilisation:** Processus imbriqué
- **Exemple:** "Processus de validation complète", "Workflow d'approbation"

---

## 🎯 Guide d'utilisation pas à pas

### Étape 1: Accéder au designer
```
Navigation > Workflows > Définitions > Créer une définition
```

### Étape 2: Informations générales
1. Saisir le **Nom** du workflow
2. Ajouter une **Description**
3. Choisir le **Statut** (Brouillon/Actif/Archivé)

### Étape 3: Créer le workflow visuellement

#### A. Glisser les éléments
1. Dans la palette de gauche, choisir un élément BPMN
2. Maintenir le clic et glisser vers le canvas (zone grise quadrillée)
3. Relâcher pour placer l'élément

**💡 Astuce:** Commencez toujours par un événement "Début"

#### B. Positionner les éléments
- Les éléments sont **déplaçables** après placement
- Organisez votre workflow de **gauche à droite**
- Laissez de l'espace entre les éléments

#### C. Configurer les propriétés
1. **Cliquer** sur un élément pour le sélectionner
2. Le panneau "Propriétés" s'affiche à droite
3. Modifier:
   - **Nom:** Label affiché
   - **Description:** Détails supplémentaires
4. Les modifications sont sauvegardées automatiquement

#### D. Supprimer un élément
- **Méthode 1:** Cliquer sur le badge ❌ rouge en haut à droite de l'élément
- **Méthode 2:** Sélectionner l'élément > Bouton "Supprimer" dans le panneau propriétés

---

### Étape 4: Générer le XML BPMN

#### Option 1: Génération automatique
1. Cliquer sur le bouton **"Générer XML"** en haut du canvas
2. Le système bascule automatiquement en mode Code
3. Le XML BPMN 2.0 est généré avec:
   - Structure `<bpmn:definitions>`
   - Tous les éléments placés
   - Informations de diagramme (positions)
   - IDs uniques

#### Option 2: Édition manuelle
1. Basculer sur l'onglet **"Mode Code XML"**
2. Éditer directement le XML
3. Respecter la structure BPMN 2.0

---

### Étape 5: Sauvegarder
1. Vérifier que le XML est présent dans l'onglet Code
2. Cliquer sur **"Enregistrer"**
3. Le workflow est créé et disponible

---

## 📊 Exemples de workflows

### Exemple 1: Workflow d'approbation simple

```
[Début] → [Tâche Utilisateur: Soumettre demande] → [XOR Gateway]
                                                          ↓
                                    Approuvé ← [Tâche Utilisateur: Valider]
                                                          ↓
                                    Rejeté   ← [Tâche Utilisateur: Valider]
                                    
[Fin Succès] ← Approuvé
[Fin Rejet]  ← Rejeté
```

**Éléments à placer:**
1. Start Event: "Début"
2. User Task: "Soumettre demande"
3. User Task: "Valider demande"
4. Exclusive Gateway (XOR)
5. End Event: "Fin - Approuvé"
6. End Event: "Fin - Rejeté"

---

### Exemple 2: Validation parallèle

```
[Début] → [Tâche: Préparer dossier] → [AND Gateway: Split]
                                              ↓
                        ┌─────────────────────┼─────────────────────┐
                        ↓                     ↓                     ↓
            [User Task: Validation   [User Task: Validation   [User Task: Validation
             Juridique]               Financière]              Technique]
                        ↓                     ↓                     ↓
                        └─────────────────────┼─────────────────────┘
                                              ↓
                                    [AND Gateway: Join]
                                              ↓
                                    [Tâche: Finaliser]
                                              ↓
                                          [Fin]
```

**Éléments à placer:**
1. Start Event
2. Task: "Préparer dossier"
3. Parallel Gateway (Split)
4. User Task: "Validation Juridique"
5. User Task: "Validation Financière"
6. User Task: "Validation Technique"
7. Parallel Gateway (Join)
8. Task: "Finaliser"
9. End Event

---

### Exemple 3: Processus avec notification

```
[Début] → [User Task: Créer document] → [Service Task: Sauvegarder]
                                                    ↓
                                        [Service Task: Envoyer notification]
                                                    ↓
                                        [Intermediate Event: Email envoyé]
                                                    ↓
                                                 [Fin]
```

**Éléments à placer:**
1. Start Event
2. User Task: "Créer document"
3. Service Task: "Sauvegarder dans base"
4. Service Task: "Envoyer notification email"
5. Intermediate Event: "Email envoyé"
6. End Event

---

## 🎨 Bonnes pratiques de design

### Layout et organisation
- ✅ **Flux de gauche à droite**
- ✅ **Espacement régulier** entre éléments
- ✅ **Alignement vertical** des branches parallèles
- ✅ **Noms descriptifs** pour chaque élément
- ❌ Éviter les croisements de flux
- ❌ Éviter les éléments trop rapprochés

### Nommage
```
✅ BON: "Valider la demande de congé"
❌ MAUVAIS: "Validation"

✅ BON: "Envoyer email de confirmation"
❌ MAUVAIS: "Email"

✅ BON: "Vérifier si montant > 1000€"
❌ MAUVAIS: "Check"
```

### Structure du workflow
1. **Toujours commencer** par un Start Event
2. **Au moins un** End Event
3. **Équilibrer les gateways:**
   - Un AND split → Un AND join
   - Un XOR split → Un XOR join
4. **Éviter les boucles infinies**
5. **Tester chaque chemin**

---

## ⚙️ Propriétés des éléments

### Configuration disponible

| Propriété | Description | Obligatoire |
|-----------|-------------|-------------|
| **ID** | Identifiant unique (auto-généré) | ✅ Oui |
| **Nom** | Label affiché sur le diagramme | ✅ Oui |
| **Type** | Type d'élément BPMN | ✅ Oui |
| **Description** | Documentation détaillée | ❌ Non |

### IDs générés automatiquement
```
startEvent_1
userTask_2
exclusiveGateway_3
endEvent_4
```

---

## 🔧 Fonctionnalités avancées

### Boutons d'action

#### Effacer le Canvas
- **Bouton:** 🗑️ Effacer
- **Action:** Supprime tous les éléments
- **Confirmation:** Demande de confirmation
- **Utilisation:** Recommencer de zéro

#### Générer XML
- **Bouton:** 💻 Générer XML
- **Action:** Convertit le diagramme visuel en XML BPMN 2.0
- **Résultat:** Bascule vers l'onglet Code avec XML généré
- **Format:** Conforme BPMN 2.0 avec diagramme (BPMNDiagram)

---

## 📝 Structure XML générée

```xml
<?xml version="1.0" encoding="UTF-8"?>
<bpmn:definitions xmlns:bpmn="http://www.omg.org/spec/BPMN/20100524/MODEL" 
                  xmlns:bpmndi="http://www.omg.org/spec/BPMN/20100524/DI" 
                  xmlns:dc="http://www.omg.org/spec/DD/20100524/DC" 
                  xmlns:di="http://www.omg.org/spec/DD/20100524/DI">
  
  <!-- Définition du processus -->
  <bpmn:process id="Process_1" isExecutable="true">
    <bpmn:startEvent id="startEvent_1" name="Début" />
    <bpmn:userTask id="userTask_2" name="Valider" />
    <bpmn:endEvent id="endEvent_3" name="Fin" />
  </bpmn:process>
  
  <!-- Informations de présentation -->
  <bpmndi:BPMNDiagram id="BPMNDiagram_1">
    <bpmndi:BPMNPlane id="BPMNPlane_1" bpmnElement="Process_1">
      <bpmndi:BPMNShape id="startEvent_1_di" bpmnElement="startEvent_1">
        <dc:Bounds x="150" y="200" width="100" height="80" />
      </bpmndi:BPMNShape>
      <!-- ... autres éléments ... -->
    </bpmndi:BPMNPlane>
  </bpmndi:BPMNDiagram>
  
</bpmn:definitions>
```

---

## 🐛 Dépannage

### Les éléments ne se placent pas
- ✅ Vérifier que vous glissez depuis la palette vers le canvas
- ✅ Assurez-vous de relâcher le clic dans la zone grise
- ✅ Rafraîchir la page si nécessaire

### Le XML ne se génère pas
- ✅ Vérifier qu'au moins un élément est placé
- ✅ Cliquer sur "Générer XML" dans la barre d'outils
- ✅ Consulter la console du navigateur pour les erreurs

### Les propriétés ne se sauvegardent pas
- ✅ Cliquer sur l'élément pour le sélectionner (bordure verte)
- ✅ Modifier les champs dans le panneau de droite
- ✅ Les changements sont automatiques

### L'élément ne se déplace pas
- ✅ Cliquer et maintenir sur l'icône (pas sur le badge ❌)
- ✅ Glisser vers la nouvelle position
- ✅ Relâcher pour placer

---

## 🎓 Ressources supplémentaires

### Standards BPMN
- **BPMN 2.0:** [OMG Specification](https://www.omg.org/spec/BPMN/2.0/)
- **Guide BPMN:** Documentation complète des éléments

### Exemples de workflows
- Workflow d'approbation de congés
- Processus de recrutement
- Gestion des achats
- Validation de documents

---

## ✅ Checklist de création

Avant de sauvegarder votre workflow:

- [ ] Le workflow a un **Start Event**
- [ ] Le workflow a au moins un **End Event**
- [ ] Tous les éléments ont un **nom descriptif**
- [ ] Les **gateways** sont équilibrés (split/join)
- [ ] Le **XML a été généré**
- [ ] Le workflow a été **testé visuellement**
- [ ] La **description** du workflow est remplie

---

## 🎉 Conclusion

L'interface BPMN Workflow Designer vous permet de:
- ✅ Créer des workflows **visuellement**
- ✅ Générer du **XML BPMN 2.0** automatiquement
- ✅ **Éditer** les propriétés facilement
- ✅ **Organiser** vos processus de manière intuitive

**Support:** Pour toute question, consultez la documentation complète ou contactez le support technique.

---

**Version:** 1.0  
**Date:** 20 novembre 2025  
**Auteur:** Système Shelve
