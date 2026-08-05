# Philosophie de l'application

> Ce document explique les choix structurants du frontal Next.js — le
> **pourquoi**, pas le **comment** (le comment est dans le code et dans
> [../PHASE-2-NEXTJS.md](../PHASE-2-NEXTJS.md)). À lire avant de contribuer.

## 1. Ce que ce dossier est, et n'est pas

C'est une **ossature** : l'arborescence, la coquille visuelle (navigation,
recherche, modales) et les conventions sont posées et fonctionnelles. Aucun
écran métier n'est branché sur l'API réelle — c'est volontaire, voir
[PHASE-2-NEXTJS.md](../PHASE-2-NEXTJS.md), étape 2.1, qui décrit le portage
domaine par domaine. Ajouter un écran métier ne doit jamais nécessiter de
toucher à ce qui suit ; sinon, c'est que l'ossature a un défaut à corriger
avant d'aller plus loin.

## 2. Un seul endroit pour changer d'apparence

Toutes les couleurs, tous les espacements et tous les rayons de bordure sont
déclarés dans **un seul fichier** : `src/styles/tokens.css`. Les composants
n'écrivent jamais `bg-blue-600` ou `#1e40af` — ils écrivent `bg-primary`, et
Tailwind résout cette classe vers la variable CSS correspondante
(`tailwind.config.ts` fait le pont).

Conséquence directe : **implémenter un nouveau template visuel, c'est
dupliquer un fichier dans `src/styles/themes/` et changer des valeurs de
variables — jamais rouvrir un composant.** Si un jour une évolution demande
« et si on avait 3 chartes graphiques selon l'organisation ? », la réponse est
déjà là : 3 fichiers dans `themes/`, sélectionnés selon la session, zéro
composant à toucher.

C'est la même logique que la règle « une seule URL de backend, dans un seul
fichier » (`lib/api/client.ts`) — le principe général est : **toute
information qui varie selon le contexte de déploiement vit dans un seul
endroit, jamais dispersée.**

## 3. Une seule façon d'afficher une modale

Deux composants, un seul système :

- **`Modal`** est la primitive : croix de fermeture en haut à droite,
  fermeture au clavier (Échap) et au clic sur l'overlay, toujours. Aucun
  écran ne réinvente sa propre boîte de dialogue.
- **`SelectionModal`** est construite sur `Modal` en taille `full` (elle
  couvre la page) : c'est le composant à utiliser chaque fois qu'un
  utilisateur doit **choisir un élément dans un grand volume de données** —
  rattacher une notice à un contenant parmi des milliers, choisir un contact
  parmi les organisations partenaires, sélectionner des mots-clés dans un
  thésaurus de plusieurs centaines d'entrées. Elle impose par construction
  une recherche et une pagination côté serveur : il ne doit **jamais** être
  possible de charger toute la liste côté client pour la filtrer en local.

**Au-delà de 26 résultats**, `SelectionModal` ajoute une aide à la navigation
— le seuil n'est pas arbitraire, c'est le nombre de lettres de l'alphabet.
L'écran appelant choisit laquelle selon la nature du jeu de données (jamais
les deux composants ne décident seuls) :

- un jeu **trié par libellé** (auteurs, mots-clés, contacts) reçoit la prop
  `alphabet` → bandeau A-Z en haut, chaque lettre filtre côté serveur ;
- un jeu **non alphabétique** (dates, statuts, notices récentes) reçoit
  `page`/`totalPages`/`onPageChange` → pagination numérique 1..X ;
- les deux peuvent être fournis en même temps (ex. lettre choisie, puis
  pagination à l'intérieur de cette lettre) — rien n'empêche de les combiner,
  mais rien ne les rend obligatoires ensemble.

En dessous de 26 résultats, aucun des deux ne s'affiche : la liste tient sur
un écran, ajouter une navigation serait du bruit.

Le `ModalProvider` (dans `src/context/`) offre en plus une façon d'ouvrir
n'importe quelle modale de façon impérative (`useModal().open(...)`) depuis
n'importe quel composant, sans que chaque écran ne porte son propre état
`isOpen`. Un seul registre, une seule modale visible à la fois — pas
d'empilement de fenêtres qui perd l'utilisateur.

## 4. Deux niveaux de navigation à gauche, jamais repliés

Le choix visuel central de l'application est une navigation en deux bandes
fixes à gauche :

1. **Le rail** (`Sidebar`) : une icône + un libellé court par domaine
   (Répertoire, Courriers, Thésaurus, Workflow, Paramètres…). Toujours
   visible, jamais réduit aux seules icônes — un utilisateur qui archive des
   documents toute la journée ne doit pas avoir à deviner ce que représente
   un pictogramme.
2. **Le bandeau sous-menu** (`Submenu`) : les actions/écrans du domaine actif,
   sous forme de boutons icône + texte, empilés verticalement.

Ce choix a un coût (deux colonnes fixes consomment de la largeur d'écran) et
un bénéfice assumé : l'utilisateur voit en permanence **où il est** (rail) et
**ce qu'il peut faire ici** (sous-menu), sans avoir à ouvrir un menu
hamburger qui masque le contexte. C'est un choix délibéré pour une
application de gestion documentaire utilisée toute la journée par des
agents, pas pour un site grand public visité occasionnellement (le portail
OPAC, lui, a sa propre coquille — voir `app/(opac)/layout.tsx` — précisément
parce que ses contraintes sont différentes : SEO, visite ponctuelle,
responsive mobile prioritaire).

## 5. Ce qui doit rester visible en permanence

La barre supérieure (`Topbar`) porte trois choses, et seulement trois : la
recherche globale, l'organisation courante, la langue. Ce ne sont pas des
réglages qu'on va chercher dans un menu « Paramètres » — ce sont des
informations dont dépend le sens de tout ce qui s'affiche en dessous (une
notice n'a pas le même contenu selon l'organisation courante ; un libellé
n'a pas le même sens selon la langue). Un utilisateur qui change
d'organisation doit le faire sans quitter l'écran sur lequel il travaille.

## 6. Le contrat d'API est extérieur, pas une supposition

`src/lib/api/client.ts` est le seul fichier qui connaît l'URL du backend.
`src/lib/api/schema.d.ts` sera généré depuis le contrat OpenAPI gelé en
phase 1 (voir [PHASE-1-API-LARAVEL.md](../PHASE-1-API-LARAVEL.md)) — pas
écrit à la main, pour qu'une dérive du contrat devienne une erreur de
compilation plutôt qu'un bug découvert en recette. Cette ossature ne
présuppose rien sur la forme exacte des réponses API : `lib/api/endpoints/`
est volontairement un squelette à un seul domaine (`records.ts`), le patron
à reproduire pour chaque domaine au moment de son portage réel.

## 7. Ce que cette ossature ne décide pas

Elle ne décide pas de la charte graphique définitive (c'est le rôle de
`styles/tokens.css`, modifiable sans toucher au code), ni de l'ordre de
portage des domaines (c'est [PHASE-2-NEXTJS.md](../PHASE-2-NEXTJS.md)), ni
des règles d'autorisation fines (c'est `lib/permissions.ts`, qui reste un
miroir d'ergonomie — la protection réelle est et reste côté serveur). Ce
document décrit une forme ; il ne fige pas le fond.
