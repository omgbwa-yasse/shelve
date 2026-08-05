/**
 * Types de la feature Auth.
 */
export type SessionUser = {
  id: number;
  name: string;
  email: string;
  surname?: string | null;
  current_organisation_id?: number | null;
  organisations?: { id: number; name: string }[];
  roles?: { id: number; name: string }[];
  permissions?: string[];
};

export type AuthSession = {
  user: SessionUser | null;
};

export type LoginPayload = {
  email: string;
  password: string;
  device_name?: string;
};

export type LoginResult = { ok: true } | { ok: false; message: string };
