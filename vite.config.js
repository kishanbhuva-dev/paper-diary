import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          // Vite will transform asset URLs in Vue templates
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    tailwindcss(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
      '@components': fileURLToPath(new URL('./resources/js/components', import.meta.url)),
      '@pages': fileURLToPath(new URL('./resources/js/pages', import.meta.url)),
      '@layouts': fileURLToPath(new URL('./resources/js/layouts', import.meta.url)),
      '@composables': fileURLToPath(new URL('./resources/js/composables', import.meta.url)),
      '@services': fileURLToPath(new URL('./resources/js/services', import.meta.url)),
      '@utils': fileURLToPath(new URL('./resources/js/utils', import.meta.url)),
    },
  },
  build: {
    // Generate sourcemaps for debugging
    sourcemap: true,
    // Rollup options
    rollupOptions: {
      output: {
        // Manual chunk splitting for better caching
        manualChunks: {
          'vue-vendor': ['vue', 'vue-router'],
          'ui-vendor': ['@iconify/vue', 'sweetalert2', 'vue-sonner'],
        },
      },
    },
    // Chunk size warning limit (in kB)
    chunkSizeWarningLimit: 1000,
  },
  // Development server options
  server: {
    hmr: {
      host: 'localhost',
    },
  },
});
