# Panel Administrativo ISP 2025

Sistema de gestión administrativa para proveedores de servicios de internet (ISP) desarrollado con Laravel 11.

## 📋 Características Principales

- 👥 **Gestión de Clientes** - CRUD completo con validación de DNI/RUC
- 🌐 **Gestión de Servicios** - Servicios PPPoE con integración RouterOS
- 💰 **Comprobantes** - Recibos, pagos, comprobantes y promesas de pago
- 🔐 **Control de Acceso** - Sistema de roles y permisos granular
- 📊 **Dashboard** - Estadísticas y métricas en tiempo real
- 🔍 **Auditoría** - Registro completo de actividades del sistema
- 📡 **Red** - Gestión de nodos, routers y conexiones PPPoE
- ⚙️ **Sistema** - Configuración de planes, ONUs, medios de pago

## 🚀 Requisitos

- PHP 8.2 o superior
- Composer 2.x
- Node.js 18+ y npm
- MySQL 8.0+ o MariaDB 10.3+
- Extensiones PHP requeridas:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

## 📦 Instalación

### 1. Clonar el repositorio

```bash
git clone <repository-url>
cd adminISP
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Instalar dependencias Node.js

```bash
npm install
```

### 4. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` con tus configuraciones:
- Base de datos
- APIs externas (DNI, RUC)
- RouterOS
- Email

### 5. Configurar base de datos

```bash
php artisan migrate --seed
```

Esto creará las tablas y datos iniciales (roles, permisos, usuario admin).

### 6. Compilar assets

**Desarrollo:**
```bash
npm run dev
```

**Producción:**
```bash
npm run build
```

### 7. Iniciar servidor de desarrollo

```bash
php artisan serve
```

El sistema estará disponible en `http://localhost:8000`

## 🔐 Credenciales por Defecto

Después de ejecutar los seeders, puedes iniciar sesión con:

- **Email:** `christiang.cm@wan.net.pe`
- **Rol:** Administrador (acceso completo)

**⚠️ IMPORTANTE:** Cambiar la contraseña después del primer inicio de sesión.

## 📁 Estructura del Proyecto

```
adminISP/
├── app/
│   ├── Core/              # Componentes centrales reutilizables
│   │   ├── Middleware/    # Middleware personalizado
│   │   ├── Services/      # Servicios base
│   │   ├── Traits/        # Traits reutilizables
│   │   └── Rules/         # Reglas de validación
│   ├── Modules/           # Módulos del sistema
│   │   ├── Clientes/      # Gestión de clientes
│   │   ├── Servicios/     # Gestión de servicios
│   │   ├── Comprobantes/  # Comprobantes, recibos y pagos
│   │   ├── Red/           # Gestión de red
│   │   ├── ControlAcceso/ # Roles y permisos
│   │   └── ...
│   └── Http/
│       └── Controllers/    # Controladores principales
├── database/
│   ├── migrations/        # Migraciones de BD
│   ├── seeders/           # Seeders de datos
│   └── factories/         # Factories para testing
├── resources/
│   ├── views/             # Vistas Blade
│   ├── js/                # JavaScript
│   └── css/               # Estilos CSS
├── routes/
│   ├── web.php            # Rutas web
│   └── api.php            # Rutas API
└── tests/                 # Tests automatizados
```

## 🏗️ Arquitectura

El proyecto sigue una **arquitectura modular** con:

- **Service Layer** - Lógica de negocio en servicios
- **Repository Pattern** - Acceso a datos abstraído
- **Form Requests** - Validación centralizada
- **Policies** - Autorización granular
- **DTOs** - Transferencia de datos tipada

### Principios SOLID

- ✅ **Single Responsibility** - Cada clase tiene una responsabilidad única
- ✅ **Open/Closed** - Abierto para extensión, cerrado para modificación
- ✅ **Liskov Substitution** - Interfaces bien definidas
- ✅ **Interface Segregation** - Interfaces específicas
- ✅ **Dependency Inversion** - Dependencias inyectadas

## 🔧 Configuración

### Variables de Entorno Importantes

Ver `.env.example` para todas las variables disponibles.

**Principales:**
- `APP_ENV` - Entorno (local, staging, production)
- `APP_DEBUG` - Modo debug (false en producción)
- `DB_*` - Configuración de base de datos
- `APISPERU_API_KEY` - API key para consultas DNI/RUC
- `ROUTEROS_*` - Configuración RouterOS

### Configuración de RouterOS

El sistema se integra con RouterOS para:
- Gestión de conexiones PPPoE
- Creación/eliminación de usuarios
- Reglas de firewall
- NAT para ONUs

Configurar en `.env`:
```env
ROUTEROS_DEFAULT_HOST=192.168.1.1
ROUTEROS_DEFAULT_USER=admin
ROUTEROS_DEFAULT_PASS=password
```

## 🧪 Testing

Ejecutar tests:

```bash
php artisan test
```

Ejecutar tests con cobertura:

```bash
php artisan test --coverage
```

## 📚 Documentación

La documentación técnica del proyecto se encuentra en la carpeta `docs/`:
- **Estándares de Comprobantes:** `docs/ESTANDARES_COMPROBANTES.md`
- **Guía de Componentes UI:** `docs/GUIA_COMPONENTES_UI.md`
- **Permisos de Comprobantes:** `docs/PERMISOS_COMPROBANTES.md`

## 🛠️ Comandos Útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar seeders específicos
php artisan db:seed --class=RolePermissionSeeder

# Ver rutas disponibles
php artisan route:list
```

## 🔒 Seguridad

- ✅ Autenticación con Laravel Breeze
- ✅ Autorización con Policies y Permisos
- ✅ Rate Limiting en login y APIs
- ✅ CSRF Protection
- ✅ XSS Protection
- ✅ SQL Injection Protection (Eloquent)
- ✅ Validación de entrada robusta
- ✅ Auditoría de actividades

## 📊 Módulos Principales

### Clientes
- CRUD completo de clientes
- Validación de DNI/RUC con APIs externas
- Gestión de ubicaciones
- Historial de servicios y pagos

### Servicios
- Creación y gestión de servicios PPPoE
- Integración con RouterOS
- Gestión de ONUs
- Planes de servicio

### Comprobantes
- Generación automática de recibos
- Registro de pagos
- Comprobantes (Boletas/Facturas)
- Promesas de pago
- Reportes (cuadre de caja, etc.)

### Red
- Gestión de nodos
- Configuración de routers
- Conexiones PPPoE activas
- Reglas de firewall
- NAT para ONUs

### Control de Acceso
- Sistema de roles (Admin, Supervisor, Operador)
- Permisos granulares por módulo
- Gestión de usuarios
- Auditoría de accesos

## 🚧 Mejoras Pendientes

**Prioridades:**
1. ⚠️ Aumentar cobertura de tests
2. ⚠️ Implementar API REST completa
3. ⚠️ Portal de cliente (self-service)
4. ⚠️ Sistema de notificaciones completo
5. ⚠️ Optimizaciones de rendimiento para grandes volúmenes

## 📝 Licencia

Este proyecto es privado y de uso interno.

## 👥 Contribución

Para contribuir al proyecto:
1. Crear una rama desde `main`
2. Realizar cambios
3. Ejecutar tests
4. Crear Pull Request

## 📞 Soporte

Para soporte técnico, contactar al equipo de desarrollo.

---

**Versión:** 1.0.0
**Última actualización:** 2025-01-XX





