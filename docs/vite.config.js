import { defineConfig } from 'vite';

export default defineConfig({
  root: '.',
  // Base path for production - change this to match your deployment path
  base: '/opth/docs/',
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    rollupOptions: {
      input: {
        main: './index.html'
      }
    },
    // Optimize for production - using esbuild (built-in, faster than terser)
    minify: 'esbuild',
    // Remove console and debugger in production
    esbuild: {
      drop: ['console', 'debugger']
    }
  },
  server: {
    port: 3000,
    open: true,
    base: '/docs/' // For development
  }
});
