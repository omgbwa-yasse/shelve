import clsx from 'clsx';

type ButtonVariant = 'primary' | 'secondary' | 'danger' | 'ghost';

export type ButtonProps = React.ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: ButtonVariant;
};

const VARIANT_CLASSES: Record<ButtonVariant, string> = {
  primary: 'bg-primary text-primary-foreground hover:bg-primary-strong',
  secondary: 'border border-border bg-background text-foreground hover:bg-muted',
  danger: 'bg-danger text-primary-foreground hover:bg-danger-strong',
  ghost: 'text-muted-foreground hover:bg-muted hover:text-foreground',
};

/** Bouton de base — toute nouvelle variante se déclare ici, pas dans les écrans. */
export function Button({ variant = 'primary', className, ...props }: ButtonProps) {
  return (
    <button
      className={clsx(
        'inline-flex min-h-11 items-center justify-center gap-2 rounded px-4 py-2 text-sm font-semibold transition-colors duration-150 ease-out disabled:cursor-not-allowed disabled:opacity-50',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary',
        VARIANT_CLASSES[variant],
        className,
      )}
      {...props}
    />
  );
}
