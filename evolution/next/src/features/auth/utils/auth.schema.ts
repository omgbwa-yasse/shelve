import { z } from 'zod';

/**
 * Validation Zod de la connexion — miroir de `LoginRequest` côté Laravel.
 */
export const loginSchema = z.object({
  email: z.string().email('Adresse email invalide.'),
  password: z.string().min(1, 'Le mot de passe est obligatoire.'),
  device_name: z.string().default('next-web'),
});

export type LoginSchema = z.infer<typeof loginSchema>;
