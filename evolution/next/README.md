# SHELVE — Frontal Next.js

> Ossature uniquement — voir [PHILOSOPHY.md](PHILOSOPHY.md) pour les principes directeurs et [../PHASE-2-NEXTJS.md](../PHASE-2-NEXTJS.md) pour le plan de portage complet.

## Démarrer

```bash
cp .env.local.example .env.local
npm install
npm run dev
```

## Arborescence

```
src/
├── app/
│   ├── (auth)/login/          # hors coquille back-office
│   ├── (back-office)/         # coquille agent : Sidebar + Submenu + Topbar
│   │   └── records/           # 1 dossier par domaine — voir PHASE-2-NEXTJS.md
│   └── (opac)/                # portail public, RSC
├── components/
│   ├── layout/                # Sidebar, Submenu, Topbar, switchers
│   ├── ui/                    # Modal, SelectionModal, Button, …
│   └── icons/                 # registre central des icônes lucide-react
├── context/                   # ModalProvider (registre central des modales)
├── hooks/
├── lib/
│   ├── api/                   # client.ts = SEUL point qui connaît l'URL backend
│   ├── auth/
│   ├── navigation.ts          # source unique de la navigation à 2 niveaux
│   └── permissions.ts
├── styles/
│   ├── tokens.css             # SOURCE UNIQUE des couleurs/espacements/rayons
│   └── themes/                # 1 fichier par template, consomme tokens.css
└── types/
```

## Règles non négociables

1. Aucune URL de backend en dur hors `lib/api/client.ts`.
2. Aucune couleur/espacement en dur dans un composant — toujours une variable de `styles/tokens.css` via Tailwind.
3. Toute modale passe par `components/ui/Modal.tsx` (ou `SelectionModal.tsx` pour les gros volumes), jamais un `<div>` ad hoc.
