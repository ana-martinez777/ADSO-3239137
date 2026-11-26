import { describe, expect, it } from 'vitest';
import { clearSession, readSession, saveSession } from './tokenStorage';

const loginData = {
  access_token: 'jwt-token',
  token_type: 'Bearer',
  expires_in: 28800,
  user: { id: 'u-1', username: 'entrenador01', role: 'coach' },
};

describe('session storage', () => {
  it('stores the token with its expiration and only public user data', () => {
    const now = 1_000_000;
    const session = saveSession(loginData, now);

    expect(session.expiresAt).toBe(now + 28800 * 1000);
    expect(readSession(now + 1000)).toEqual(session);
    expect(sessionStorage.getItem('sports_gear_session')).not.toContain('password');
  });

  it('discards the session when the token has expired', () => {
    const now = 1_000_000;
    saveSession(loginData, now);

    expect(readSession(now + 28800 * 1000)).toBeNull();
    expect(sessionStorage.getItem('sports_gear_session')).toBeNull();
  });

  it('discards corrupted content and allows logging out', () => {
    sessionStorage.setItem('sports_gear_session', '{no-json');
    expect(readSession()).toBeNull();

    saveSession(loginData);
    clearSession();
    expect(readSession()).toBeNull();
  });
});
