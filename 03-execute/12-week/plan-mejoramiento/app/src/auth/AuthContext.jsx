import { createContext, useCallback, useContext, useEffect, useLayoutEffect, useMemo, useRef, useState } from 'react';
import { configureClient } from '../api/client';
import { authApi } from '../api/sportsGearApi';
import { clearSession, readSession, saveSession } from './tokenStorage';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [session, setSession] = useState(() => readSession());
  const [notice, setNotice] = useState('');
  const sessionRef = useRef(session);
  sessionRef.current = session;

  const logout = useCallback((message = '') => {
    clearSession();
    setSession(null);
    setNotice(message);
  }, []);

  // El cliente HTTP obtiene el token desde aquí y cierra la sesión ante un 401.
  // useLayoutEffect se ejecuta antes que los useEffect de las pantallas hijas,
  // así las primeras peticiones después de recargar ya llevan el token.
  useLayoutEffect(() => {
    configureClient({
      tokenProvider: () => sessionRef.current?.token ?? null,
      onUnauthorized: () => logout('Tu sesión expiró. Inicia sesión nuevamente.'),
    });
  }, [logout]);

  // Cierre automático cuando se cumplen las 8 horas de vigencia del token.
  useEffect(() => {
    if (!session) return undefined;
    const remaining = session.expiresAt - Date.now();
    const timer = setTimeout(() => logout('Tu sesión expiró. Inicia sesión nuevamente.'), Math.max(remaining, 0));
    return () => clearTimeout(timer);
  }, [session, logout]);

  const login = useCallback(async (username, password) => {
    const result = await authApi.login(username.trim(), password);
    const newSession = saveSession(result);
    sessionRef.current = newSession;
    setSession(newSession);
    setNotice('');
    return newSession.user;
  }, []);

  const value = useMemo(
    () => ({ user: session?.user ?? null, login, logout, notice }),
    [session, login, logout, notice],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) throw new Error('useAuth debe usarse dentro de AuthProvider');
  return context;
}
