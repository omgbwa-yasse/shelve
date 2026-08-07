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
          'w-full rounded border border-border bg-surface px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary',
          className,
        )}
        {...props}
      />
    );
  },
);

Input.displayName = 'Input';
