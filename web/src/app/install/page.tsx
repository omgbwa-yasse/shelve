'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { Icon } from '@/components/icons';
import * as api from '@/features/install/services/install.service';

/**
 * Assistant d'installation — première configuration de SHELVE.
 *
 * Étapes :
 *  1. Nom de l'organisation (écrit dans les settings, clé `organisation_name`) ;
 *  2. Choix des modules à installer (4 obligatoires verrouillés : records,
 *     transfers, tools, settings ; 6 facultatifs : workplace, workflow, projects,
 *     ai, email, mail) ;
 *  3. Récapitulatif (modules + comptes) et exécution ;
 *  4. Terminé → lien vers la connexion.
 */
export default function InstallPage() {
  const router = useRouter();
  const [loading, setLoading] = useState(true);
  const [installed, setInstalled] = useState(false);
  const [step, setStep] = useState(1);
  const [organisationName, setOrganisationName] = useState('');
  const [options, setOptions] = useState<api.ModuleOption[]>([]);
  const [required, setRequired] = useState<string[]>([]);
  const [selected, setSelected] = useState<Set<string>>(new Set());
  const [running, setRunning] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [result, setResult] = useState<api.InstallationResult | null>(null);

  useEffect(() => {
    api
      .getInstallationOptions()
      .then((res) => {
        const modules = res.options ?? [];
        const req = res.required_modules ?? [];
        setInstalled(Boolean(res.installed));
        setOptions(modules);
        setRequired(req);
        // Modules obligatoires toujours cochés ; facultatifs cochés par défaut.
        setSelected(new Set(modules.map((o) => o.key)));
      })
      .catch(() => setError('Impossible de charger les options d\'installation.'))
      .finally(() => setLoading(false));
  }, []);

  const nameReady = organisationName.trim().length >= 3;

  function toggle(key: string) {
    if (required.includes(key)) return; // obligatoires verrouillés
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(key)) next.delete(key);
      else next.add(key);
      return next;
    });
  }

  async function run() {
    setRunning(true);
    setError(null);
    try {
      const res = await api.runInstallation(organisationName.trim(), [...selected]);
      setResult(res.data);
      setStep(4);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Échec de l\'installation.');
    } finally {
      setRunning(false);
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-surface p-4">
      <div className="w-full max-w-2xl rounded-xl border border-border bg-background p-6 shadow-sm">
        {loading ? (
          <p className="py-8 text-center text-sm text-muted-foreground">Chargement…</p>
        ) : installed ? (
          <div className="flex flex-col items-center gap-4 py-8 text-center">
            <span className="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
              <Icon name="shieldLock" className="h-7 w-7" />
            </span>
            <div>
              <h1 className="text-lg font-semibold">Installation déjà réalisée</h1>
              <p className="mx-auto mt-1 max-w-md text-sm text-muted-foreground">
                SHELVE est déjà configuré. Pour gérer les modules actifs, connectez-vous en tant que
                superadmin puis ouvrez <span className="font-medium text-foreground">Paramètres → Modules</span>.
              </p>
            </div>
            <button
              type="button"
              onClick={() => router.push('/login')}
              className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
              Aller à la connexion
            </button>
          </div>
        ) : (
        <>
        <header className="mb-6 flex items-center gap-3">
          <span className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-primary-foreground">
            <Icon name="briefcase" className="h-5 w-5" />
          </span>
          <div>
            <h1 className="text-lg font-semibold">Installation de SHELVE</h1>
            <p className="text-sm text-muted-foreground">Configurez votre espace d&apos;archives.</p>
          </div>
        </header>

        {/* Progression */}
        <ol className="mb-6 flex items-center gap-2 text-xs">
          {['Organisation', 'Modules', 'Installation', 'Terminé'].map((label, i) => {
            const n = i + 1;
            const active = step === n;
            const done = step > n;
            return (
              <li key={label} className="flex items-center gap-2">
                <span
                  className={`flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-semibold ${
                    done ? 'bg-primary text-primary-foreground' : active ? 'bg-primary/15 text-primary' : 'bg-muted text-muted-foreground'
                  }`}
                >
                  {done ? <Icon name="checkCircle" className="h-3.5 w-3.5" /> : n}
                </span>
                <span className={active ? 'font-medium text-foreground' : 'text-muted-foreground'}>{label}</span>
                {n < 4 && <span className="h-px w-6 bg-border" />}
              </li>
            );
          })}
        </ol>

        {/* ÉTAPE 1 — Nom de l'organisation */}
        {step === 1 && (
          <div className="flex flex-col gap-4">
            <label className="flex flex-col gap-1 text-sm">
              <span>Nom de l&apos;organisation *</span>
              <input
                autoFocus
                value={organisationName}
                onChange={(e) => setOrganisationName(e.target.value)}
                placeholder="Ex. Archives nationales, Mairie de Lyon, …"
                className="rounded border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
              />
              <span className="text-xs text-muted-foreground">
                Ce nom sera enregistré dans les paramètres (clé <code>organisation_name</code>) et utilisé
                comme référence pour votre espace.
              </span>
            </label>
            <div className="flex justify-end">
              <button
                type="button"
                disabled={!nameReady}
                onClick={() => setStep(2)}
                className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              >
                Suivant
              </button>
            </div>
          </div>
        )}

        {/* ÉTAPE 2 — Choix des modules */}
        {step === 2 && (
          <div className="flex flex-col gap-4">
            <div className="rounded-lg bg-muted p-3 text-xs text-muted-foreground">
              <p>
                <span className="font-semibold text-foreground">Obligatoires :</span>{' '}
                {options.filter((o) => o.required).map((o) => o.label).join(', ')}.
              </p>
              <p className="mt-1">
                Les autres modules sont facultatifs : décochez ceux que vous ne souhaitez pas installer.
              </p>
            </div>
            <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
              {options.map((o) => {
                const checked = selected.has(o.key);
                const locked = o.required;
                return (
                  <label
                    key={o.key}
                    className={`flex cursor-pointer items-start gap-3 rounded-lg border p-3 ${checked ? 'border-primary bg-primary/5' : 'border-border'} ${locked ? 'opacity-90' : ''}`}
                  >
                    <input
                      type="checkbox"
                      checked={checked}
                      disabled={locked}
                      onChange={() => toggle(o.key)}
                      className="mt-0.5 h-4 w-4"
                    />
                    <span>
                      <span className="block text-sm font-medium">
                        {o.label}
                        {locked && <span className="ml-1 rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-semibold text-primary">obligatoire</span>}
                      </span>
                      <span className="block text-xs text-muted-foreground">{o.description}</span>
                    </span>
                  </label>
                );
              })}
            </div>
            <div className="flex justify-between">
              <button type="button" onClick={() => setStep(1)} className="rounded border border-border px-4 py-2 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                Précédent
              </button>
              <button
                type="button"
                disabled={selected.size === 0}
                onClick={() => setStep(3)}
                className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              >
                Suivant
              </button>
            </div>
          </div>
        )}

        {/* ÉTAPE 3 — Récapitulatif / installation */}
        {step === 3 && (
          <div className="flex flex-col gap-4">
            <div className="rounded-lg border border-border bg-surface p-4 text-sm">
              <p><span className="font-medium">Organisation :</span> {organisationName.trim()}</p>
              <p className="mt-1">
                <span className="font-medium">Modules ({selected.size}) :</span>{' '}
                {options.filter((o) => selected.has(o.key)).map((o) => o.label).join(', ')}
              </p>
              <div className="mt-3 rounded bg-muted p-3 text-xs">
                <p className="font-semibold">Comptes créés</p>
                <p className="mt-1">
                  Compte superadmin <code>superadmin@example.com / superadmin</code>, rattaché aux bureaux
                  DG, DF, DRH et DADA.
                </p>
                <p className="mt-1">
                  Compte administrateur <code>admin@example.com / admin123</code>.
                </p>
              </div>
            </div>
            {error && <p className="text-sm text-danger">{error}</p>}
            <div className="flex justify-between">
              <button type="button" onClick={() => setStep(2)} className="rounded border border-border px-4 py-2 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                Précédent
              </button>
              <button
                type="button"
                disabled={running}
                onClick={() => void run()}
                className="flex items-center gap-2 rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              >
                {running && <Icon name="refresh" className="h-4 w-4 animate-spin" />}
                {running ? 'Installation en cours…' : 'Installer'}
              </button>
            </div>
          </div>
        )}

        {/* ÉTAPE 4 — Terminé */}
        {step === 4 && result && (
          <div className="flex flex-col items-center gap-4 py-4 text-center">
            <span className="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
              <Icon name="checkCircle" className="h-7 w-7" />
            </span>
            <div>
              <h2 className="text-base font-semibold">Installation terminée</h2>
              <p className="mt-1 text-sm text-muted-foreground">
                L&apos;organisation <strong>{result.organisation_name}</strong> a été configurée avec{' '}
                {result.modules.length} module(s).
              </p>
            </div>
            <div className="flex flex-wrap justify-center gap-2 text-xs">
              {options
                .filter((o) => result.modules.includes(o.key))
                .map((o) => (
                  <span key={o.key} className="rounded-full border border-border bg-surface px-3 py-1">
                    {o.label}
                  </span>
                ))}
            </div>
            <div className="mt-2 flex gap-2">
              <button
                type="button"
                onClick={() => router.push('/login')}
                className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              >
                Aller à la connexion
              </button>
            </div>
          </div>
        )}
        </>
        )}
      </div>
    </div>
  );
}
