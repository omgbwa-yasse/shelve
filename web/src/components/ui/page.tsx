/**
 * Primitives d'écran partagées par les features (en-têtes, panneaux d'info,
 * écrans d'information). Infrastructure commune — pas de logique métier.
 */

export function PageHeader({ title, description, actions }: { title: string; description?: string; actions?: React.ReactNode }) {
  return (
    <header className="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h1 className="text-xl font-semibold">{title}</h1>
        {description && <p className="mt-1 text-sm text-muted-foreground">{description}</p>}
      </div>
      {actions && <div className="flex gap-2">{actions}</div>}
    </header>
  );
}

export function InfoPanel({ title, items }: { title: string; items: [string, React.ReactNode][] }) {
  return (
    <div className="rounded border border-border bg-surface p-4">
      <h2 className="mb-3 text-sm font-semibold">{title}</h2>
      <dl className="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
        {items.map(([k, v]) => (
          <div key={k} className="rounded bg-background p-2">
            <dt className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{k}</dt>
            <dd className="mt-1">{v}</dd>
          </div>
        ))}
      </dl>
    </div>
  );
}

export function StatCard({ label, value, accent }: { label: string; value: React.ReactNode; accent?: 'warn' | 'danger' | 'ok' }) {
  const color = accent === 'danger' ? 'text-danger' : accent === 'warn' ? 'text-yellow-600' : accent === 'ok' ? 'text-green-600' : '';
  return (
    <div className="rounded border border-border bg-surface p-4 text-center">
      <div className={`text-2xl font-semibold ${color}`}>{value}</div>
      <div className="mt-1 text-xs text-muted-foreground">{label}</div>
    </div>
  );
}

export function InfoScreen({ title, description, sections }: { title: string; description: string; sections: [string, string][] }) {
  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title={title} description={description} />
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        {sections.map(([k, v]) => (
          <div key={k} className="rounded border border-border bg-surface p-4">
            <h3 className="text-sm font-semibold">{k}</h3>
            <p className="mt-1 text-sm text-muted-foreground">{v}</p>
          </div>
        ))}
      </div>
    </div>
  );
}
