import { defineConfig } from 'vite';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
  root: __dirname,
  base: '/build/',
  publicDir: false,
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        app: 'resources/js/app.js',
        admin: 'resources/js/admin.js',
        'forms': 'resources/js/forms.js',
        'auth': 'resources/js/auth.js',
        'consultant': 'resources/js/consultant.js',
        'calculator': 'resources/js/calculator.js',
        'login': 'resources/js/login.js',
      },
    },
    minify: 'esbuild',
    cssCodeSplit: true,
  },
  server: {
    port: 5173,
    proxy: {
      '/api': 'http://localhost',
      '/uploads': 'http://localhost',
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
      '@scss': path.resolve(__dirname, 'resources/scss'),
    },
  },
  css: {
    postcss: './postcss.config.js',
  },
});