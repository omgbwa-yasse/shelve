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

La **bulle rouge** sur « Courrier » = nombre de courriers non lus.

### Le tableau de bord — ce qu'il montre

C'est l'écran d'ouverture de la démo : chacun y voit **exactement ce qu'il a à faire**.

- **7 bulles cliquables**, filtrées par rôle : Non lus, À coter *(DG)*, À signer *(DG)*,
  À viser (N+1), Réception à valider, À reprendre, **En retard**.
- **Bandeau d'intérim** : un utilisateur qui remplace un responsable voit en tête
  « Vous assurez un intérim — *entité*, en remplacement de *X*, volet *Y*, jusqu'au *date* »,
  avec le rappel qu'il ne traite que les courriers de ce volet.
- **Courriers en attente de votre action** : chaque ligne indique **depuis combien de temps**
  il attend et son **échéance**, en rouge si elle est dépassée.
- **Suivi des cotations** *(DG)* : pour chaque courrier coté à plusieurs directions, une barre
  de progression **X/Y directions ont répondu** et le détail par direction
  (✓ validée / ⏳ en attente). C'est là que le DG voit d'un coup d'œil qui traîne.
- **Délais dépassés** : la liste des courriers hors délai avec le nombre de jours de retard.
- **Activité récente** : les dernières actions tracées, y compris les réponses créées
  (« Réponse créée : OUT-2026-005 »).

*Jeu de démo* : `IN-2026-004` (circulaire interministérielle) est coté à **3 directions**,
la DSI a répondu, la DRH et la DAG non → le tableau affiche **1/3** et un retard de 2 jours.

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
3. Le **DG cote** : affecte à **une ou plusieurs directions** + instruction (« Donner suite », « Classer »…).
4. **Chaque direction cotée valide sa propre réception** ; le courrier n'est *Terminé*
   que lorsque **toutes** les directions ont validé.

#### Cotation à plusieurs directions (multi-cotation)
- Sur la fiche du courrier entrant, le DG clique **« Coter (affecter à des directions) »**
  et **coche plusieurs directions** (ex. DSI + DRH + DAG) avec une instruction commune.
- Le DG peut **re-coter** plus tard pour **ajouter** une direction : les directions déjà
  cotées restent pré-cochées et **conservent leur statut** (une direction qui a déjà validé
  n'est pas réinitialisée).
- Un bloc **« Suivi de la cotation »** s'affiche sur la fiche : compteur `X/Y direction(s) ont
  validé`, puis **une ligne par direction** (instruction, statut *En attente* / *Réception
  validée*, date, et par qui). C'est là que le DG suit **chaque réponse individuellement**.
- Le bouton **« Valider la réception (ma direction) »** n'apparaît qu'aux agents de la
  direction dont la cotation est **encore en attente** — il disparaît une fois sa réception
  validée, sans bloquer les autres directions.
- Les compteurs du **tableau de bord** et les **bulles de notification** tiennent compte des
  cotations : une direction ne voit le courrier « à réceptionner » que si **sa** cotation est
  en attente.
- L'**historique** journalise la cotation (avec la liste des directions) et **chaque**
  validation de réception séparément.

#### Rattachement au plan de classement (activité)
- Chaque direction possède ses **activités** (plan de classement : *Gestion du personnel →
  Paie et avantages*, *Applications métier → Maintenance applicative*…).
- Dans le formulaire de cotation, **en face de chaque direction cochée**, le DG choisit
  l'**activité** au titre de laquelle cette direction traite le courrier. Deux directions
  peuvent donc traiter le même courrier sous **deux activités différentes**
  (ex. DSI → *Sécurité des accès*, DRH → *Dossiers individuels*).
- L'activité est reprise sur la fiche du courrier et dans le tableau « Suivi de la
  cotation ». C'est ce qui relie le courrier au **classement** et, à terme, à sa **durée de
  conservation**.

### Répondre / donner suite — chaînage des courriers
Un courrier reçu débouche souvent sur plusieurs autres courriers. Le bouton
**« Répondre / donner suite »** (fiche du courrier) crée un nouveau courrier **rattaché**
à l'original :
- on choisit l'**objet**, le **type** (sortant vers le tiers, ou interne vers un service)
  et on rédige les **éléments de réponse** ;
- le nouveau courrier hérite de l'**activité**, de la typologie et de la priorité,
  et son destinataire est pré-rempli avec l'expéditeur du courrier d'origine ;
- il part en **brouillon** et suit ensuite le circuit normal (soumission → visas → signature DG) ;
- la fiche de la réponse affiche **« En réponse à : … »**, et la fiche du courrier d'origine
  liste **« Suites données à ce courrier »** avec le statut de chacune.

**Exemple préchargé** : `IN-2026-001` (Lettre du Ministère) a produit `OUT-2026-005`
(la réponse officielle) et `INT-2026-002` (note interne de suivi).

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

Menu **Outils → Intérims → Intérims des responsables** (URL `/tools/organisation-interims`,
DG uniquement). Le DG désigne un ou plusieurs **intérimaires** pour le responsable d'un
service, sur une période.

### Plusieurs intérimaires, un volet chacun
- Dans **« Désigner un intérimaire »**, le formulaire propose **2 lignes d'intérimaires**
  et le bouton **« Ajouter un intérimaire »** (jusqu'à **5**).
- Chaque ligne porte un **volet géré**, qui peut être une **activité du plan de classement**
  de la direction choisie : la liste d'activités se met à jour automatiquement quand on
  sélectionne l'entité (ex. DRH → *Paie et avantages*, *Formation et développement*).
  Un champ texte libre permet en plus de préciser le volet
  (*« Volet technique (infrastructures, exploitation) »*).
- Une case **« Principal »** désigne l'intérimaire vers qui **le courrier du service est
  routé** quand aucune activité ne permet de trancher. Les autres volets restent actifs.

### L'intérimaire ne voit que les courriers de son volet
C'est le cœur du dispositif : **on ne répond qu'aux courriers auxquels l'intérim donne accès.**
- Le courrier coté à une direction porte une **activité** ; l'intérimaire dont le volet
  correspond à cette activité est le seul à pouvoir **valider la réception** et **répondre**.
- Les courriers relevant d'une autre activité **ne lui sont pas accessibles** (le titulaire,
  lui, voit tout).
- Le **routage automatique** suit la même règle : un courrier « Gestion des infrastructures »
  part chez l'intérimaire du volet technique, un courrier « Support aux utilisateurs » chez
  celui du volet administratif.
- Les **compteurs du tableau de bord** sont cloisonnés de la même façon : chaque intérimaire
  ne compte que les réceptions de son volet.
- Sur la fiche, un bandeau rappelle : « Vous intervenez sur ce courrier au titre de votre
  intérim à … — volet … ».

*Démo (vérifiée)* : `resp.infra` (volet **Gestion des infrastructures**) et `resp.reseaux`
(volet **Support aux utilisateurs**) remplacent le directeur DSI. Un courrier coté DSI sous
« Gestion des infrastructures » n'est traitable que par `resp.infra` ; `resp.reseaux` n'y a
pas accès — et inversement. `dir.dsi` (le titulaire) voit les deux.
- Depuis la liste, le bouton **« Principal »** bascule le routage vers un autre intérimaire
  sans rien supprimer ; **« Clôturer »** met fin à un intérim.
- Le tableau affiche : entité, titulaire, intérimaire (badge **Principal**), **volet géré**,
  période, état.

**Exemple préchargé pour la démo** : le **directeur DSI** est en mission ;
`resp.infra` assure le **volet technique** — activité *Gestion des infrastructures*
(principal, reçoit le courrier DSI) — et `resp.reseaux` le **volet administratif**
— activité *Support aux utilisateurs*.

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
R : Le DG désigne un ou plusieurs **intérimaires** (Outils → Intérims) ; le courrier est
automatiquement routé vers celui dont le volet couvre l'activité concernée, pendant la période.

**Q : Peut-on désigner plusieurs intérimaires pour un même responsable ?**
R : Oui, **jusqu'à 5**, chacun avec **son volet** pris dans le plan de classement. Un seul est
**principal** : il reçoit le courrier quand aucune activité ne permet de trancher. Le DG peut
basculer le principal à tout moment.

**Q : Un intérimaire voit-il tout le courrier de la direction qu'il remplace ?**
R : Non — et c'est voulu. Il n'accède qu'aux courriers **relevant de l'activité qui lui a été
déléguée**. Un courrier d'une autre activité ne lui est ni visible ni traitable : il revient à
l'autre intérimaire. Le titulaire, lui, garde la vue complète.

**Q : Peut-on préciser l'activité que l'intérimaire doit gérer ?**
R : Oui, et elle est prise **dans le plan de classement** de la direction : en choisissant
l'entité, la liste des activités de cette entité s'affiche. On délègue donc un volet réel
(ex. *Paie et avantages*) et non une simple mention libre.

**Q : Un courrier est-il rattaché à une activité du plan de classement ?**
R : Oui. Au moment de la cotation, le DG choisit l'activité **pour chaque direction cotée** —
deux directions peuvent traiter le même courrier au titre de deux activités différentes.
L'activité apparaît sur la fiche et dans le suivi de cotation ; c'est le lien avec le
classement et la conservation.

**Q : Peut-on répondre à un courrier et garder la trace du circuit ?**
R : Oui. Le bouton **« Répondre / donner suite »** crée un courrier **rattaché** au courrier
d'origine. La fiche d'origine liste toutes les **suites données** (avec leur statut), et
chaque réponse affiche **« En réponse à … »**. On voit ainsi qu'un seul courrier reçu a
débouché sur plusieurs courriers (réponse au tiers + notes internes).

**Q : Un courrier entrant peut-il concerner plusieurs directions ?**
R : Oui. Le DG **cote le même courrier à plusieurs directions** en une seule opération.
Chaque direction **valide sa propre réception**, et le DG suit **chaque réponse
individuellement** dans le bloc « Suivi de la cotation ». Le courrier n'est clos que
lorsque **toutes** les directions ont répondu. Le DG peut aussi **ajouter une direction**
après coup sans perdre les validations déjà faites.

**Q : Le préremplissage IA marche comment ?**
R : Sur les formulaires de création, on **dépose un PDF/image** ; l'IA (Mistral, configurée en
local) **propose un préremplissage** des champs, que l'agent peut corriger avant de valider.

**Q : Pourquoi certains comptes ne voient pas tous les modules ?**
R : Les accès sont **différenciés par rôle** : un agent ne voit que le courrier ; un directeur
voit le courrier, le répertoire, les outils ; la DSI a un accès transverse (presque tous les
modules) ; le DG supervise l'ensemble.

---

## 9. Mise en ligne (VPS) — à faire avant la démo

Après un `git pull` sur le serveur :

```bash
php artisan migrate --force && php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

Points de vigilance :

1. **Langue de l'interface.** Le menu s'affiche en anglais si `APP_LOCALE=en`.
   Pour une démo en français, mettre dans le `.env` du serveur :
   `APP_LOCALE=fr` et `APP_FALLBACK_LOCALE=fr`, puis `php artisan config:clear`.

2. **Nouvelles tables et colonnes.** Arrivent par migration (d'où le `migrate --force`
   obligatoire) :
   - `mail_cotations` — cotation multi-directions, avec `activity_id` par direction ;
   - `organisation_interims.scope` / `.is_primary` / `.activity_id` — volets d'intérim ;
   - `mails.activity_id` — rattachement au plan de classement ;
   - `mails.parent_mail_id` — chaînage des réponses et suites données.

3. **Erreur « Table 'shelves.records' doesn't exist ».** Corrigée à la source :
   la migration `2025_11_07_000001_rename_records_to_record_physicals` renommait
   `records` **sans vérifier** que la table existait encore, et **insérait elle-même**
   sa ligne dans la table `migrations` (d'où une entrée en double et un numéro de batch
   faussé). Elle est désormais idempotente. Après le `git pull`, relancer simplement
   `php artisan migrate --force`, puis vérifier :

```bash
php artisan migrate:status | grep -i record
```

6. **Migrations nettoyées** (toutes corrigées *en place*, aucun fichier renommé ni
   supprimé — le journal `migrations` de la production reste valide) :
   - `2025_11_07_000001_rename_records_to_record_physicals` — garde d'idempotence +
     suppression de l'écriture manuelle dans la table `migrations` ;
   - `2025_07_03_000001_update_mcp_permissions` — écrivait dans une table
     `permission_role` **inexistante** (le pivot réel est `role_permissions`) : plantait
     dès qu'un rôle `superadmin` était présent ;
   - `2025_07_04_023045_add_external_relations_to_mails_table` — `down()` supprimait une
     colonne deux fois et deux colonnes appartenant à une autre migration ;
   - `2025_07_04_124001_fix_external_organization_fields` et
     `2025_06_30_210636_add_return_dates_to_reservations_table` — `down()` supprimaient des
     colonnes qu'elles n'avaient pas créées (rollback cassé) ;
   - `2025_08_25_221340_drop_container_types_table` et
     `2025_06_28_011830_update_communications_table_use_enum_status` — supprimaient des
     tables encore référencées par une contrainte FK sous SQLite, ce qui cassait
     `migrate:fresh` en développement.

   Les migrations du chantier courrier ont par ailleurs été **regroupées** (5 fichiers → 3) :
   `create_mail_cotations_table`, `add_volets_to_organisation_interims_table`,
   `add_activity_and_parent_to_mails_table`.

7. **Dossier des migrations réorganisé et allégé** (voir
   `database/migrations/README.md`) : les fichiers sont rangés par module dans des
   sous-dossiers (`core/`, `mails/`, `records/`, `thesaurus/`…), enregistrés par
   `AppServiceProvider::loadModuleMigrations()`. Le nom d'une migration restant son nom de
   fichier, **le journal `migrations` de la production reste valide et rien n'est rejoué**
   (`migrate:status` affiche bien les 125 migrations en « Ran »).
   Le nombre de fichiers passe de 132 à 125, et une migration de nettoyage
   supprime **20 tables mortes** (220 → 200 tables) : module LDAP jamais développé,
   module « ouvrages », reliquats du module workflow, pivots du tableau d'affichage.

4. **Modèle IA.** Le fournisseur est Mistral : le réglage `ai_default_model` doit valoir
   `mistral-large-latest` (et non un modèle Ollama), suivi de `php artisan cache:clear`
   (le cache des réglages dure 1 h).

5. **Jeu de démo.** Pour recharger les comptes, organisations et l'exemple d'intérim à
   deux volets : `php artisan db:seed --force` (⚠️ à ne faire que sur la base de démo).
