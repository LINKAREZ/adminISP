# Integración de Cursor-Skills en Admin ISP

Este proyecto aplica guías y buenas prácticas del repositorio **cursor-skills** para desarrollo con Cursor IDE.

## Qué es Cursor-Skills

- **Repositorio**: [github.com/grapelike-class151/cursor-skills](https://github.com/grapelike-class151/cursor-skills)
- **Descripción**: Hub comunitario de mejores prácticas y guías para Cursor IDE en distintos entornos (PHP, Node, Python, web design, DevOps, testing, etc.).
- **Contenido**: Guías maestras, configuraciones recomendadas, plantillas, ejemplos y documentación por lenguaje/stack.

## Cómo está implementado aquí

1. **Regla de Cursor**
   En `.cursor/rules/cursor-skills-php-laravel.mdc` hay una regla que aplica las prácticas de cursor-skills para **PHP y Laravel** cuando trabajas con archivos `*.php` y `*.blade.php`. El asistente de Cursor usará esas guías al generar o modificar código en el proyecto.

2. **Reglas globales del proyecto**
   El archivo `.cursorrules` en la raíz define el contexto del proyecto (Laravel 11, PHP 8.2+, mobile-first, etc.) y el comportamiento del asistente. Las prácticas de cursor-skills se suman a esas reglas.

## Uso recomendado

- **En Cursor**: Abre archivos PHP o Blade y pide cambios o código nuevo; la regla de cursor-skills se aplicará automáticamente según los globs definidos.
- **Como referencia**: Puedes consultar el repositorio cursor-skills para:
  - **PHP/Laravel**: carpeta `php/` (README, patrones, configuración).
  - **Guía general**: `CURSOR_SKILLS_MASTER_GUIDE.md`.
  - **Otros entornos**: `node/`, `python/`, `webdesign/`, `configs/`, `devops/`, etc.

## Opcional: clonar cursor-skills en el proyecto

Si quieres tener el contenido del repositorio dentro del proyecto (solo lectura/referencia):

```bash
# Desde la raíz del proyecto (adminISP)
git submodule add https://github.com/grapelike-class151/cursor-skills.git docs/cursor-skills
```

Después de esto, la documentación quedará en `docs/cursor-skills/`. Para actualizar el submodule más adelante:

```bash
git submodule update --remote docs/cursor-skills
```

Si no usas submodule, la regla en `.cursor/rules/` y los enlaces a GitHub son suficientes para aplicar las prácticas.

## Resumen

| Dónde                                                                             | Qué hace                                               |
| --------------------------------------------------------------------------------- | ------------------------------------------------------ |
| `.cursor/rules/cursor-skills-php-laravel.mdc`                                     | Aplica prácticas cursor-skills a PHP y Blade en Cursor |
| `docs/CURSOR_SKILLS.md` (este archivo)                                            | Documenta la integración y cómo usar cursor-skills     |
| Repo externo [cursor-skills](https://github.com/grapelike-class151/cursor-skills) | Fuente de las guías y mejores prácticas                |
