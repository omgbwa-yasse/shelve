import type { Config } from 'tailwindcss';

/**
 * Aucune couleur/espacement n'est codé en dur ici : chaque token Tailwind lit une
 * variable CSS définie dans `src/styles/tokens.css` (source unique de vérité —
 * voir PHILOSOPHY.md, "Un seul fichier pour changer de template"). Changer de
 * template = changer les valeurs des variables, jamais les classes utilisées
 * dans les composants.
 */
const config: Config = {
  darkMode: ['class', '[data-theme="dark"]'],
  content: ['./src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        background: 'hsl(var(--color-background) / <alpha-value>)',
        foreground: 'hsl(var(--color-foreground) / <alpha-value>)',
        surface: 'hsl(var(--color-surface) / <alpha-value>)',
        border: 'hsl(var(--color-border) / <alpha-value>)',
        primary: {
          DEFAULT: 'hsl(var(--color-primary) / <alpha-value>)',
          foreground: 'hsl(var(--color-primary-foreground) / <alpha-value>)',
        },
        muted: {
          DEFAULT: 'hsl(var(--color-muted) / <alpha-value>)',
          foreground: 'hsl(var(--color-muted-foreground) / <alpha-value>)',
        },
        danger: 'hsl(var(--color-danger) / <alpha-value>)',
        // Bande de navigation (rail + sous-menu) : tokens dédiés, pensés pour
        // rester lisibles quel que soit le template de couleurs choisi.
        rail: {
          DEFAULT: 'hsl(var(--color-rail) / <alpha-value>)',
          foreground: 'hsl(var(--color-rail-foreground) / <alpha-value>)',
          active: 'hsl(var(--color-rail-active) / <alpha-value>)',
        },
      },
      borderRadius: {
        DEFAULT: 'var(--radius)',
        sm: 'var(--radius-sm)',
        lg: 'var(--radius-lg)',
      },
      fontFamily: {
        sans: ['var(--font-sans)'],
      },
      spacing: {
        rail: 'var(--width-rail)',
        submenu: 'var(--width-submenu)',
        topbar: 'var(--height-topbar)',
      },
    },
  },
  plugins: [],
};

export default config;
