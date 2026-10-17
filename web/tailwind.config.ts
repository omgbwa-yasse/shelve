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
          strong: 'hsl(var(--color-primary-strong) / <alpha-value>)',
          foreground: 'hsl(var(--color-primary-foreground) / <alpha-value>)',
        },
        muted: {
          DEFAULT: 'hsl(var(--color-muted) / <alpha-value>)',
          foreground: 'hsl(var(--color-muted-foreground) / <alpha-value>)',
        },
        danger: 'hsl(var(--color-danger) / <alpha-value>)',
        'danger-strong': 'hsl(var(--color-danger-strong) / <alpha-value>)',
        success: 'hsl(var(--color-success) / <alpha-value>)',
        warning: 'hsl(var(--color-warning) / <alpha-value>)',
        info: 'hsl(var(--color-info) / <alpha-value>)',
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
        md: 'var(--radius-md)',
        lg: 'var(--radius-lg)',
        xl: 'var(--radius-xl)',
        '2xl': 'var(--radius-2xl)',
        '3xl': 'var(--radius-3xl)',
      },
      fontFamily: {
        sans: ['var(--font-sans)'],
        serif: ['var(--font-serif)'],
        mono: ['var(--font-mono)'],
      },
      /**
       * Échelle de texte du template church : agrandie (voir son index.css).
       * Sans hauteur de ligne explicite, le leading hérité (1.5) s'applique —
       * comme dans la charte d'origine.
       */
      fontSize: {
        xs: '0.875rem',
        sm: '1rem',
        base: '1.125rem',
        lg: '1.25rem',
        xl: '1.375rem',
        '2xl': '1.625rem',
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
