'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { Icon, type IconKey } from '@/components/icons';

/**
 * Coquille du portail public — reproduit `opac/layouts/app.blade.php`
 * (masthead Koha + navbar + footer) avec les tokens Tailwind du projet.
 * Délibérément distincte de la coquille back-office (pas de rail/sous-menu).
 */
const NAV_ITEMS: { href: string; label: string; icon: IconKey; match: RegExp }[] = [
  { href: '/opac/search', label: 'Recherche', icon: 'search', match: /^\/opac\/search/ },
  { href: '/opac/records', label: 'Catalogue', icon: 'book', match: /^\/opac\/records/ },
  { href: '/opac/news', label: 'Actualités', icon: 'newspaper', match: /^\/opac\/news/ },
  { href: '/opac/events', label: 'Événements', icon: 'calendar', match: /^\/opac\/events/ },
  { href: '/opac/pages', label: 'Informations', icon: 'fileText', match: /^\/opac\/pages/ },
];

export function OpacShell({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();

  return (
    <div className="flex min-h-screen flex-col bg-background text-foreground">
      {/* ===================== MASTHEAD ===================== */}
      <header className="sticky top-0 z-30 bg-gradient-to-r from-primary to-primary/85 text-primary-foreground shadow">
        <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
          <Link href="/opac/search" className="flex items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70">
            <span className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-foreground/15">
              <Icon name="book" className="h-5 w-5" />
            </span>
            <span className="leading-tight">
              <span className="block font-serif text-xl font-bold tracking-tight">Shelve</span>
              <span className="block text-[11px] uppercase tracking-widest text-primary-foreground/75">Catalogue en ligne</span>
            </span>
          </Link>

          <div className="flex items-center gap-2">
            <a
              href="/opac/auth/login"
              className="inline-flex items-center gap-2 rounded-lg border border-primary-foreground/25 bg-primary-foreground/10 px-4 py-2 text-sm font-medium transition hover:bg-primary-foreground/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
            >
              <Icon name="person" className="h-4 w-4" />
              Connexion
            </a>
            <a
              href="/opac/auth/register"
              className="hidden rounded-lg bg-primary-foreground/15 px-4 py-2 text-sm font-medium transition hover:bg-primary-foreground/25 sm:inline-flex focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
            >
              Créer un compte
            </a>
          </div>
        </div>
      </header>

      {/* ===================== NAVBAR ===================== */}
      <nav className="sticky top-[68px] z-20 border-b border-border bg-surface/95 backdrop-blur">
        <div className="mx-auto flex max-w-6xl items-center gap-1 px-4">
          {NAV_ITEMS.map((item) => {
            const active = item.match.test(pathname);
            return (
              <Link
                key={item.href}
                href={item.href}
                className={`inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary ${
                  active
                    ? 'border-primary text-primary'
                    : 'border-transparent text-muted-foreground hover:bg-muted hover:text-foreground'
                }`}
              >
                <Icon name={item.icon} className="h-4 w-4" />
                {item.label}
              </Link>
            );
          })}
        </div>
      </nav>

      {/* ===================== CONTENU ===================== */}
      <main className="flex-1">{children}</main>

      {/* ===================== FOOTER ===================== */}
      <footer className="mt-12 border-t-4 border-primary bg-rail text-rail-foreground">
        <div className="mx-auto grid max-w-6xl grid-cols-1 gap-8 px-4 py-10 text-sm sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <h6 className="mb-3 text-sm font-semibold uppercase tracking-wide">Shelve</h6>
            <p className="opacity-80">
              Votre passerelle vers la découverte et l&apos;accès aux collections : recherchez,
              parcourez et consultez les documents en ligne.
            </p>
          </div>
          <div>
            <h6 className="mb-3 text-sm font-semibold uppercase tracking-wide">Découvrir</h6>
            <ul className="space-y-2 opacity-80">
              <li><Link className="transition hover:pl-1 hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70" href="/opac/search">Rechercher</Link></li>
              <li><Link className="transition hover:pl-1 hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70" href="/opac/records">Parcourir les collections</Link></li>
              <li><Link className="transition hover:pl-1 hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70" href="/opac/news">Dernières actualités</Link></li>
              <li><Link className="transition hover:pl-1 hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70" href="/opac/events">Événements à venir</Link></li>
            </ul>
          </div>
          <div>
            <h6 className="mb-3 text-sm font-semibold uppercase tracking-wide">Mon compte</h6>
            <ul className="space-y-2 opacity-80">
              <li><Link className="transition hover:pl-1 hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70" href="/opac/auth/login">Connexion</Link></li>
              <li><Link className="transition hover:pl-1 hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70" href="/opac/auth/register">Créer un compte</Link></li>
            </ul>
          </div>
          <div>
            <h6 className="mb-3 text-sm font-semibold uppercase tracking-wide">Aide &amp; contact</h6>
            <ul className="space-y-2 opacity-80">
              <li><Link className="transition hover:pl-1 hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70" href="/opac/pages">Guide utilisateur</Link></li>
              <li><Link className="transition hover:pl-1 hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70" href="/opac/feedback">Nous contacter</Link></li>
            </ul>
          </div>
        </div>
        <div className="border-t border-rail-foreground/10 py-4 text-center text-xs opacity-70">
          © {new Date().getFullYear()} Shelve. Tous droits réservés — Propulsé par Shelve.
        </div>
      </footer>
    </div>
  );
}
