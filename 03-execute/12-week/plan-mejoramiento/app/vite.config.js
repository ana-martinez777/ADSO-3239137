import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

// In development, /api requests are proxied to the Laravel backend.
// The browser works with a single origin, so no local CORS setup is needed.
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const apiTarget = env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8000';

  return {
    plugins: [react()],
    server: {
      host: true,
      port: 5173,
      proxy: {
        '/api': { target: apiTarget, changeOrigin: true },
      },
    },
    test: {
      environment: 'jsdom',
      globals: true,
      setupFiles: './src/test/setup.js',
      css: false,
    },
  };
});
