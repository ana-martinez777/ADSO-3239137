// Persistencia de la sesión en sessionStorage.
// - Se borra al cerrar la pestaña o el navegador.
// - Se guarda la fecha de expiración calculada con expires_in (8 horas)
//   para descartar tokens vencidos sin esperar un 401.
// - Nunca se guarda la contraseña.

const STORAGE_KEY = 'sports_gear_session';

export function saveSession({ access_token, expires_in, user }, now = Date.now()) {
  const session = {
    token: access_token,
    expiresAt: now + Number(expires_in) * 1000,
    user: { id: user.id, username: user.username, role: user.role },
  };
  sessionStorage.setItem(STORAGE_KEY, JSON.stringify(session));
  return session;
}

export function readSession(now = Date.now()) {
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY);
    if (!raw) return null;
    const session = JSON.parse(raw);
    if (!session?.token || !session?.expiresAt || session.expiresAt <= now) {
      clearSession();
      return null;
    }
    return session;
  } catch {
    clearSession();
    return null;
  }
}

export function clearSession() {
  try {
    sessionStorage.removeItem(STORAGE_KEY);
  } catch {
    // El almacenamiento puede no estar disponible; no hay nada que limpiar.
  }
}
