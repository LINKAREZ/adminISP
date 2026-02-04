import { copyFileSync, mkdirSync, existsSync, readdirSync, statSync } from 'fs';
import { join } from 'path';

const fontAwesomePath = join(process.cwd(), 'node_modules/@fortawesome/fontawesome-free/webfonts');
const publicWebfontsPath = join(process.cwd(), 'public/webfonts');

if (!existsSync(fontAwesomePath)) {
  console.warn('Aviso: No se encontró FontAwesome en node_modules. ¿Ejecutaste npm install?');
  process.exit(0);
}

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
  console.log('FontAwesome webfonts copiados a public/webfonts');
} catch (error) {
  console.warn('Aviso: No se pudieron copiar los webfonts de FontAwesome:', error.message);
}
