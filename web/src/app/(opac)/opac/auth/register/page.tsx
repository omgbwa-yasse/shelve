'use client';

import { useState } from 'react';
import Link from 'next/link';
import { Icon } from '@/components/icons';

/**
 * Inscription au portail public — reproduit `opac/auth/register.blade.php`.
 * Le compte est créé en attente d'approbation par l'administration.
 */
export default function OpacRegisterPage() {
  const [form, setForm] = useState({ first_name: '', name: '', email: '', password: '', password_confirmation: '' });
  const [message, setMessage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  function set<K extends keyof typeof form>(k: K, v: string) {
    setForm((p) => ({ ...p, [k]: v }));
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    setMessage(null);
    if (form.password !== form.password_confirmation) {
      setError('Les mots de passe ne correspondent pas.');
      return;
    }
    setLoading(true);
    try {
      const res = await fetch('/api/opac/auth/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });
      const json = await res.json().catch(() => null);
      if (!res.ok) {
        const first = json?.errors ? Object.values(json.errors)[0] : json?.message;
        setError(first ?? 'Inscription refusée.');
        setLoading(false);
        return;
      }
      setMessage(json?.message ?? 'Compte créé avec succès. En attente d\'approbation par l\'administrateur.');
      setForm({ first_name: '', name: '', email: '', password: '', password_confirmation: '' });
    } catch {
      setError('Impossible de contacter le serveur.');
    }
    setLoading(false);
  }

  const inputCls = 'rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary';

  return (
    <div className="mx-auto max-w-md px-4 py-12">
      <div className="rounded-xl border border-border bg-surface p-8 shadow-sm">
        <h1 className="font-serif text-xl font-bold">Créer un compte</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Inscrivez-vous pour accéder à votre espace lecteur du portail public
        </p>

        {error && (
          <p className="mt-4 flex items-center gap-2 rounded-lg bg-danger/10 px-3 py-2 text-sm text-danger">
            <Icon name="alertTriangle" className="h-4 w-4 shrink-0" />
            {error}
          </p>
        )}
        {message && (
          <p className="mt-4 flex items-center gap-2 rounded-lg bg-success/10 px-3 py-2 text-sm text-success">
            <Icon name="checkCircle" className="h-4 w-4 shrink-0" />
            {message}
          </p>
        )}

        <form onSubmit={submit} className="mt-6 flex flex-col gap-4">
          <div className="grid gap-4 sm:grid-cols-2">
            <label className="flex flex-col gap-1 text-sm">
              <span className="font-medium">Prénom</span>
              <input required value={form.first_name} onChange={(e) => set('first_name', e.target.value)} className={inputCls} />
            </label>
            <label className="flex flex-col gap-1 text-sm">
              <span className="font-medium">Nom</span>
              <input required value={form.name} onChange={(e) => set('name', e.target.value)} className={inputCls} />
            </label>
          </div>
          <label className="flex flex-col gap-1 text-sm">
            <span className="font-medium">Adresse e-mail</span>
            <input type="email" required value={form.email} onChange={(e) => set('email', e.target.value)} className={inputCls} />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="font-medium">Mot de passe</span>
            <input type="password" required minLength={8} value={form.password} onChange={(e) => set('password', e.target.value)} className={inputCls} />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="font-medium">Confirmation du mot de passe</span>
            <input type="password" required minLength={8} value={form.password_confirmation} onChange={(e) => set('password_confirmation', e.target.value)} className={inputCls} />
          </label>
          <button
            type="submit"
            disabled={loading}
            className="mt-2 inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            <Icon name="personPlus" className="h-4 w-4" />
            {loading ? 'Inscription…' : 'Créer le compte'}
          </button>
        </form>

        <p className="mt-6 text-center text-sm text-muted-foreground">
          Déjà inscrit ?{' '}
          <Link href="/opac/auth/login" className="font-medium text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
            Se connecter
          </Link>
        </p>
      </div>
    </div>
  );
}
