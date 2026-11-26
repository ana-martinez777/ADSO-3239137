// Session persistence in sessionStorage.
// - It is cleared when the tab or browser is closed.
// - The expiration time computed from expires_in (8 hours) is stored
//   to discard expired tokens without waiting for a 401.
// - The password is never stored.

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
    // Storage may be unavailable; there is nothing to clear.
  }
}
