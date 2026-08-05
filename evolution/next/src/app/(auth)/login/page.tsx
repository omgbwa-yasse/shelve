import { LoginForm } from '@/features/auth/components/login-form';

/**
 * Écran de connexion — hors coquille back-office. Délégué à la feature Auth
 * (`features/auth` : composants + server actions + cookie httpOnly).
 */
export default function LoginPage() {
  return (
    <div className="flex min-h-screen items-center justify-center bg-surface">
      <div className="w-full max-w-sm rounded-lg border border-border bg-background p-6 shadow-sm">
        <h1 className="text-lg font-semibold">Connexion — SHELVE</h1>
        <p className="mt-2 text-sm text-muted-foreground">Espace des agents d'archives</p>
        <LoginForm />
      </div>
    </div>
  );
}
