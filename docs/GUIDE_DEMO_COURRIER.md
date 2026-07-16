# Guide de démonstration — Module Courrier (zéro papier)

> Document de préparation pour la démo CDEC. À garder ouvert pendant la présentation
> pour ne pas se perdre : rôle de chaque écran, circuits, comptes, et réponses aux
> questions probables.

---

## 1. Comptes de démonstration

**Mot de passe unique : `password`** (sauf superadmin).

| Compte | Rôle | Direction / Service | Supérieur (N+1) |
|--------|------|----------------------|------------------|
| `dg@example.com` | **DG** | Direction Générale | — (signe tout le sortant, cote l'entrant) |
| `secretariat@example.com` | agent | Direction Générale | dg |
| `accueil@example.com` | **responsable** | Service Courrier & Accueil (DAG) | dir.dag |
| `dir.dsi@example.com` | directeur *(accès quasi tous modules)* | DSI | dg |
| `agent.dsi@example.com` | agent | DSI | dir.dsi |
| `dir.drh@example.com` | directeur | DRH | dg |
| `agent.drh@example.com` | agent | DRH | dir.drh |
| `dir.dag@example.com` | directeur | DAG | dg |
| `agent.dag@example.com` | agent | DAG | dir.dag |
| **Cascade multi-niveaux (DSI) :** | | | |
| `resp.infra@example.com` | responsable | Service Infrastructures (DSI) | dir.dsi |
| `resp.reseaux@example.com` | responsable | Bureau Réseaux (DSI) | resp.infra |
| `agent.reseaux@example.com` | agent | Bureau Réseaux (DSI) | resp.reseaux |

`superadmin@example.com` / `superadmin` : accès total (à éviter pour la démo métier).

**Chaîne de cascade démontrable :**
`agent.reseaux` → `resp.reseaux` (bureau) → `resp.infra` (service) → `dir.dsi` (direction) → `dg` (signature).

---

## 2. Où cliquer (navigation)

| Écran | Chemin |
|-------|--------|
| **Tableau de bord** | Menu « Tableau de bord » (1er à gauche) ou le logo Shelve |
| **Courriers** | Menu « Mails » → sidebar |
| **Intérims** (DG uniquement) | Menu « Tools » → sidebar « Intérims » → *Désigner un intérimaire* |
| **Plan de classement** | Menu « Tools » → sidebar « Classification Plan » |
| **Organigramme** | Menu « Tools » → sidebar « Organization Chart » |

La **bulle rouge** sur « Mails » = nombre de courriers non lus. Le **tableau de bord** affiche
des bulles par action à faire (À coter, À signer, À viser, Réception à valider, À reprendre).

---

## 3. Les 4 circuits de courrier — importance réelle

### ⭐ Reçu externe (`Mails → Receive` / création « Incoming ») — ESSENTIEL
Point d'entrée de **tout courrier venu de l'extérieur** (ministère, citoyen, fournisseur)
déposé à l'accueil. L'agent d'accueil l'enregistre → il monte au secrétariat DG → le DG **cote**.
*Sans lui, aucun courrier externe n'entre.*

### ⭐ Envoi externe (`Mails → Send` externe / création « Outgoing ») — ESSENTIEL
Tout courrier **sortant vers l'extérieur**. Circuit : rédaction → **visa(s) hiérarchique(s)**
→ **signature DG** → expédition. *Seul moyen qu'un document sorte signé du DG.*

### ✅ Envoi interne (`Mails → Sent` / création « Sent ») — UTILE
**Communication entre services** (ex. DSI → DRH). Circuit : visa N+1 → livraison au service
destinataire → le responsable **cote en interne** à un agent qui traite.

### ⚠️ Reçu interne (création « Received ») — SECONDAIRE (régularisation)
Dans le zéro-papier, un courrier interne envoyé (circuit ci-dessus) **arrive automatiquement**
chez le destinataire : pas besoin de le ré-enregistrer. Ce circuit ne sert qu'à **régulariser
un papier reçu hors système** (note imprimée remise en main propre). *Peut être ignoré en démo.*

---

## 4. Détail du menu « Mails » — à quoi sert chaque entrée

### Consultations
| Entrée | Importance | Rôle |
|--------|-----------|------|
| **Reçus** | ⭐ | Liste des courriers entrants du service |
| **Envoyés** | ⭐ | Liste des courriers sortants du service |
| **Retournés** | ✅ | Courriers renvoyés (rejet / révision) |
| **À retourner** | ✅ | Courriers dont l'action impose un retour |
| **Parapheurs** | ✅ | Lots de courriers (voir §6) |
| **Externe → Send / Receive** | ⭐ | Listes des courriers externes émis / reçus |
| **Archives → Mail / Boxes** | ✅ | Courriers archivés, boîtes de classement |
| **Recherche avancée → Typologies / Dates / Advanced** | ✅ | Retrouver un courrier par critères |

### Création
| Entrée | Importance |
|--------|-----------|
| **Reçu interne** | ⚠️ secondaire (régularisation) |
| **Envoi interne** | ✅ communication inter-services |
| **Sortant externe** | ⭐ essentiel |
| **Entrant externe** | ⭐ essentiel (accueil) |

### Administration
| Entrée | Importance | Rôle |
|--------|-----------|------|
| **Parapheur / Send / Receive parapher** | ✅ | Créer / transmettre / recevoir un lot |
| **Box & Chrono** | ✅ | Contenants d'archivage du courrier |

---

## 5. Le workflow en détail

### Circuit ENTRANT
1. **Accueil** enregistre le courrier externe (`accueil@example.com`).
2. Il monte au **secrétariat DG**.
3. Le **DG cote** : affecte à une direction + instruction (« Donner suite », « Classer »…).
4. Le **responsable** de la direction **valide la réception** → *Terminé*.

### Circuit SORTANT (avec cascade de visas)
1. Un **agent** initie et **soumet pour validation**.
2. Le courrier **remonte niveau par niveau** : chaque responsable **vise** avant de faire monter.
   - Exemple : `agent.reseaux` → visa `resp.reseaux` → visa `resp.infra` → visa `dir.dsi` → **DG**.
   - À chaque niveau, le validateur peut **renvoyer pour révision** (l'initiateur corrige,
     ajoute des pièces jointes, resoumet).
3. Le **DG signe** → *Transmis* (le courrier peut sortir).
4. **Exception directeur** : un directeur initie **sans visa intermédiaire** (va directement au DG).
5. **Exception DG** : le DG qui rédige lui-même un sortant **signe directement** (aucun visa).

### Circuit INTER-SERVICES
`agent DSI` soumet → **visa N+1 DSI** → livré à la **DRH** → **responsable DRH** cote à un
agent → l'agent **valide le traitement** → *Terminé*.

### Traçabilité
La fiche de chaque courrier affiche **l'historique complet** : toutes les étapes traversées,
qui a agi, quand, et **le temps passé à chaque étape**.

---

## 6. Le parapheur — à quoi ça sert

Le **parapheur** est le classeur de signature de l'administration. Il sert au **traitement
groupé** :
- **Parapheur d'envoi** : regrouper plusieurs courriers à faire signer au DG **en une fois**
  (le DG ne signe pas 30 courriers un par un — sa secrétaire prépare le parapheur du jour).
- **Parapheur de réception** : transmettre un **lot** de courriers entre services avec un bordereau.
- **Lot / bordereau** : tracer le déplacement d'une pile de courriers (qui envoie, qui reçoit).

**État actuel** : le parapheur gère le **regroupement et la transmission**. La **signature
groupée** (le DG signe tout un parapheur d'un coup) n'est **pas encore branchée** sur la nouvelle
signature DG — c'est la prochaine itération. En démo, présenter le parapheur comme
« transmission groupée » et le circuit unitaire comme cœur de la signature.

---

## 7. L'intérim

Menu **Tools → Intérims** (DG uniquement). Le DG désigne un **intérimaire** pour le responsable
d'un service, sur une période. **Tant que l'intérim est actif, tout courrier destiné au service
est routé vers l'intérimaire** au lieu du titulaire. (Testé : le routage bascule correctement.)

---

## 8. Questions probables & réponses

**Q : Si j'ai trois niveaux hiérarchiques au-dessus de moi, chacun valide-t-il avant de faire monter ?**
R : Oui. Le courrier remonte **niveau par niveau** ; chaque responsable vise à son tour, et ce
n'est qu'au sommet que le DG signe. (Démontrable avec `agent.reseaux`.)

**Q : Le DG doit-il se faire valider pour envoyer un courrier ?**
R : Non. Quand le DG rédige lui-même, il **signe et transmet directement**, sans visa.

**Q : Un directeur doit-il passer par un visa avant le DG ?**
R : Non. Un directeur initie **sans visa intermédiaire** ; son courrier va directement au DG.

**Q : Que se passe-t-il si un validateur refuse ?**
R : Il **renvoie pour révision** avec un motif. L'initiateur corrige, **ajoute des pièces
jointes** si besoin, et **resoumet** : le circuit repart. Ou **rejet définitif** par le DG.

**Q : Comment le service destinataire reçoit-il un courrier interne ?**
R : Après le visa N+1 de l'émetteur, il **apparaît automatiquement** chez le destinataire ; le
responsable le **cote** à un agent qui traite. Pas besoin de « reçu interne » manuel.

**Q : Peut-on voir tout le parcours d'un courrier ?**
R : Oui, l'onglet **Historique** de la fiche liste chaque étape, l'acteur, l'heure et **la durée
passée à chaque étape**.

**Q : Et si un responsable est absent ?**
R : Le DG désigne un **intérimaire** (Tools → Intérims) ; le courrier est automatiquement routé
vers lui pendant la période.

**Q : Le préremplissage IA marche comment ?**
R : Sur les formulaires de création, on **dépose un PDF/image** ; l'IA (Mistral, configurée en
local) **propose un préremplissage** des champs, que l'agent peut corriger avant de valider.

**Q : Pourquoi certains comptes ne voient pas tous les modules ?**
R : Les accès sont **différenciés par rôle** : un agent ne voit que le courrier ; un directeur
voit le courrier, le répertoire, les outils ; la DSI a un accès transverse (presque tous les
modules) ; le DG supervise l'ensemble.
