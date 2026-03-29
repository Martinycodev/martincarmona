import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  // Evitar conflicto entre publicDir y outDir
  publicDir: false,

  // Entry points
  build: {
    outDir: 'public/dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        app: resolve(__dirname, 'resources/js/app.js'),
        style: resolve(__dirname, 'resources/css/app.css'),
      },
    },
  },
  // Dev server config
  server: {
    port: 5173,
    strictPort: true,
    // Allow cross-origin requests from PHP dev server
    cors: true,
    hmr: {
      host: 'localhost',
    },
  },
  // Resolve aliases
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources'),
      '@js': resolve(__dirname, 'resources/js'),
      '@css': resolve(__dirname, 'resources/css'),
    },
  },
});
