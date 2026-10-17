'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { Icon } from '@/components/icons';

/**
 * Connexion au portail public — reproduit `opac/auth/login.blade.php`.
 * Le token est placé dans un cookie httpOnly par `/api/opac/auth/login`.
 */
export default function OpacLoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      const res = await fetch('/api/opac/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });
      const json = await res.json().catch(() => null);
      if (!res.ok) {
        setError(json?.message ?? 'Connexion refusée. Vérifiez vos identifiants.');
        setLoading(false);
        return;
      }
      router.push('/opac/search');
    } catch {
      setError('Impossible de contacter le serveur.');
      setLoading(false);
    }
  }

  return (
    <div className="mx-auto max-w-md px-4 py-12">
      <div className="rounded-xl border border-border bg-surface p-8 shadow-sm">
        <h1 className="font-serif text-xl font-bold">Connexion</h1>
        <p className="mt-1 text-sm text-muted-foreground">Accédez à votre espace lecteur</p>

        {error && (
          <p className="mt-4 flex items-center gap-2 rounded-lg bg-danger/10 px-3 py-2 text-sm text-danger">
            <Icon name="alertTriangle" className="h-4 w-4 shrink-0" />
            {error}
          </p>
        )}

        <form onSubmit={submit} className="mt-6 flex flex-col gap-4">
          <label className="flex flex-col gap-1 text-sm">
            <span className="font-medium">Adresse e-mail</span>
            <input
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
            />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="font-medium">Mot de passe</span>
            <input
              type="password"
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
            />
          </label>
          <button
            type="submit"
            disabled={loading}
            className="mt-2 inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            <Icon name="personCheck" className="h-4 w-4" />
            {loading ? 'Connexion…' : 'Se connecter'}
          </button>
        </form>

        <p className="mt-6 text-center text-sm text-muted-foreground">
          Pas encore de compte ?{' '}
          <Link href="/opac/auth/register" className="font-medium text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
            Créer un compte
          </Link>
        </p>
      </div>
    </div>
  );
}
