import { forwardRef } from 'react';
import { cn } from '@/utils/cn';

export type InputProps = React.InputHTMLAttributes<HTMLInputElement>;

/**
 * Champ de saisie atomique (structure de référence `components/ui/input.tsx`).
 */
export const Input = forwardRef<HTMLInputElement, InputProps>(
  ({ className, type, ...props }, ref) => {
    return (
      <input
        ref={ref}
        type={type}
        className={cn(
          'w-full min-h-11 rounded border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20',
          className,
        )}
        {...props}
      />
    );
  },
);

Input.displayName = 'Input';
