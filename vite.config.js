import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { copyFileSync, mkdirSync, existsSync, readdirSync, statSync } from 'fs';
import { join } from 'path';

// Plugin para copiar webfonts de FontAwesome
function copyFontAwesomeWebfonts() {
  return {
    name: 'copy-fontawesome-webfonts',
    buildStart() {
      const fontAwesomePath = join(process.cwd(), 'node_modules/@fortawesome/fontawesome-free/webfonts');
      const publicWebfontsPath = join(process.cwd(), 'public/webfonts');
      
      if (existsSync(fontAwesomePath)) {
        if (!existsSync(publicWebfontsPath)) {
          mkdirSync(publicWebfontsPath, { recursive: true });
        }
        
        try {
          const files = readdirSync(fontAwesomePath);
          files.forEach(file => {
            const srcPath = join(fontAwesomePath, file);
            const destPath = join(publicWebfontsPath, file);
            if (statSync(srcPath).isFile()) {
              copyFileSync(srcPath, destPath);
            }
          });
          console.log('✅ FontAwesome webfonts copiados a public/webfonts');
        } catch (error) {
          console.warn('⚠️ No se pudieron copiar los webfonts de FontAwesome:', error.message);
        }
      }
    }
  };
}

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/adminlte.css',
        'resources/css/superadmin.css',
        'resources/css/portal.css',
        'resources/css/mapa-infraestructura.css',
        'resources/js/app.js',
        'resources/js/adminlte.js',
        'resources/js/theme-toggle.js',
        'resources/js/logger.js',
      ],
      refresh: true,
    }),
    copyFontAwesomeWebfonts(),
  ],
  server: {
    hmr: {
      host: 'localhost',
    },
  },
  build: {
    // Optimizaciones de build
    cssCodeSplit: true,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['axios'],
          adminlte: ['admin-lte', 'bootstrap', 'jquery'],
          datatables: ['datatables.net', 'datatables.net-bs4'],
        },
      },
    },
    // Minificar en producción, mantener sin minificar en desarrollo para mejor debugging
    minify: process.env.NODE_ENV === 'production',
    // Chunk size warning limit
    chunkSizeWarningLimit: 1000,
  },
  // Optimizaciones de desarrollo
  optimizeDeps: {
    include: ['axios', 'chart.js/auto', 'datatables.net', 'datatables.net-bs4'],
  },
});
