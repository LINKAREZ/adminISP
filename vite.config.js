import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { copyFileSync, mkdirSync, existsSync, readdirSync, statSync } from 'fs';
import { join } from 'path';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

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
          files.forEach((file) => {
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
    },
  };
}

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/adminlte.css',
        'resources/css/portal.css',
        'resources/css/mapa-infraestructura.css',
        'resources/js/app.js',
        'resources/js/adminlte.js',
        'resources/js/color-theme.js',
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
  resolve: {
    alias: {
      // popper.js: package.json apunta a dist/umd/ que no existe; usar dist/ directamente
      'popper.js': path.resolve(__dirname, 'node_modules/popper.js/dist/popper.js'),
    },
  },
  build: {
    cssCodeSplit: true,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['axios'],
          adminlte: ['admin-lte', 'jquery'],
          datatables: ['datatables.net', 'datatables.net-bs4'],
        },
      },
    },
    minify: process.env.NODE_ENV === 'production',
    chunkSizeWarningLimit: 1000,
  },
  optimizeDeps: {
    include: ['axios', 'chart.js/auto', 'datatables.net', 'datatables.net-bs4', 'popper.js'],
  },
});
