# Fingerprintweb - Gestión Automática de Asistencias y Detección de Movimiento con Alarma.

Proyecto universitario desarrollado con Laravel 12 y Filament 4 para la gestión de asistencia mediante huellas dactilares y detección de movimiento.

## Información del Proyecto

**Tecnologías utilizadas:**

-   Laravel Framework 12.35.0
-   Filament Admin Panel 4.1.10
-   PHP 8.2
-   MySQL (Base de datos remota)
-   Docker & Docker Compose

---

## Requisitos del Sistema

Antes de comenzar, asegúrate de tener instalado:

1. **Git** - Control de versiones
2. **Docker Desktop** - Para Windows/Mac, o Docker Engine + Docker Compose para Linux
3. **Editor de código** - VS Code, PHPStorm, u otro de tu preferencia

---

## Instalación y Configuración

### 1. Clonar el Repositorio

```bash
git clone https://github.com/KarloZ7715/fingerprintweb.git
cd fingerprintweb
git checkout development
```

### 2. Configurar Variables de Entorno

Copia el archivo de ejemplo:

```bash
# Windows (PowerShell)
Copy-Item .env.example .env

# Linux/Mac
cp .env.example .env
```

**Configurar credenciales de la base de datos:**

El archivo `.env.example` no contiene las credenciales reales por seguridad. Debes configurar manualmente las siguientes variables en tu archivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=tu-host-de-base-de-datos
DB_PORT=3306
DB_DATABASE=nombre-de-la-base-de-datos
DB_USERNAME=tu-usuario
DB_PASSWORD=tu-contraseña
```

**Obtener credenciales:**

-   Las credenciales de la base de datos se comparten por los canales privados del equipo (WhatsApp o Discord)
-   **NUNCA** compartas las credenciales públicamente o las subas al repositorio

### 3. Iniciar Docker

Construye e inicia los contenedores de Docker:

```bash
docker-compose build
docker-compose up -d
docker-compose exec redis redis-cli ping
```

### 4. Instalar Dependencias

Instala las dependencias de PHP con Composer:

```bash
docker-compose exec app composer install
```

### 5. Crear Enlace de Storage

Crea el enlace simbólico para el almacenamiento público:

```bash
docker-compose exec app php artisan storage:link
```

### 6. Ejecutar Migraciones (Opcional)

Si necesitas ejecutar migraciones en la base de datos:

```bash
docker-compose exec app php artisan migrate
```

### 7. Crear Usuario Administrador

Crea tu usuario para acceder al panel de administración:

```bash
docker-compose exec app php artisan make:filament-user
```

Proporciona la información solicitada:

-   Nombre
-   Email
-   Contraseña (mínimo 8 caracteres)

---

## Acceso a la Aplicación

Una vez completada la instalación, la aplicación estará disponible en:

-   **Aplicación principal:** http://localhost:8000
-   **Panel de administración:** http://localhost:8000/admin
-   **Login administrativo:** http://localhost:8000/admin/login

---

## Comandos Útiles

### Gestión de Docker

```bash
# Iniciar contenedores
docker-compose up -d

# Detener contenedores
docker-compose down

# Ver logs en tiempo real
docker-compose logs -f app

# Reiniciar contenedores
docker-compose restart

# Acceder al contenedor de la aplicación
docker-compose exec app bash
```

### Comandos de Laravel/Artisan

```bash
# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Limpiar caché
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Ver información del sistema
docker-compose exec app php artisan about

# Ver rutas de la aplicación
docker-compose exec app php artisan route:list

# Ver información de la base de datos
docker-compose exec app php artisan db:show
```

### Comandos de Composer

```bash
# Instalar dependencias
docker-compose exec app composer install

# Actualizar dependencias
docker-compose exec app composer update

# Instalar un paquete específico
docker-compose exec app composer require vendor/package
```

### Comandos de Filament

```bash
# Crear un recurso CRUD
docker-compose exec app php artisan make:filament-resource NombreModelo

# Crear una página personalizada
docker-compose exec app php artisan make:filament-page NombrePagina

# Crear un widget
docker-compose exec app php artisan make:filament-widget NombreWidget

# Crear usuario administrador
docker-compose exec app php artisan make:filament-user
```

---

## Estructura del Proyecto

```
fingerprintweb/
├── app/
│   ├── Filament/           # Recursos, páginas y widgets de Filament
│   ├── Http/               # Controladores y middleware
│   ├── Models/             # Modelos de la base de datos
│   └── Providers/          # Service providers
├── config/                 # Archivos de configuración
├── database/
│   ├── migrations/         # Migraciones de base de datos
│   └── seeders/           # Seeders de datos
├── docker/                 # Configuración de Docker
│   ├── nginx/             # Configuración de Nginx
│   └── php/               # Configuración de PHP
├── public/                 # Archivos públicos accesibles
├── resources/
│   └── views/             # Plantillas Blade
├── routes/                 # Definición de rutas
├── storage/               # Archivos generados y logs
├── tests/                 # Tests automatizados
├── .env                   # Variables de entorno (NO COMMITEAR)
├── docker-compose.yml     # Configuración de servicios Docker
└── Dockerfile             # Imagen Docker del proyecto
```

---

## Trabajo en Equipo

### Estructura de Branches

El proyecto utiliza la siguiente estructura de ramas:

-   **`main`** - Rama principal con código estable y en producción
-   **`development`** - Rama de desarrollo donde se integran las nuevas funcionalidades
-   **`development/feature/{nombre-funcionalidad}`** - Ramas para desarrollar funcionalidades específicas desde development

### Obtener Cambios del Repositorio

Antes de comenzar a trabajar, actualiza tu copia local:

```bash
# Actualizar rama development
git checkout development
git pull origin development

# Actualizar dependencias si hubo cambios
docker-compose exec app composer install
```

### Crear una Nueva Funcionalidad

#### Opción A: Si NO tienes `development` en tu repositorio local

Sigue estos pasos para traer `development` y crear tu rama de feature:

```bash
# 1. Ver todas las ramas remotas disponibles
git branch -r

# 2. Crear y cambiar a la rama development desde origin/development
git checkout -b development origin/development

# 3. Verificar que estás en development
git branch

# 4. Crear la rama de feature desde development
git checkout -b development/feature/nombre-funcionalidad

# 5. Realizar tus cambios y commitear frecuentemente
git add archivo.php
git commit -m "feat: descripción clara de los cambios realizados"

# 6. Subir la rama de feature al repositorio
git push origin development/feature/nombre-funcionalidad

# 7. Crear Pull Request en GitHub desde development/feature/nombre-funcionalidad hacia development
# (Desde la interfaz de GitHub)
```

#### Opción B: Si YA tienes `development` en tu repositorio local

Sigue estos pasos para actualizar y crear tu rama de feature:

```bash
# 1. Asegúrate de estar en development y actualízalo
git checkout development
git pull origin development

# 2. Crear la rama de feature desde development
git checkout -b development/feature/nombre-funcionalidad

# 3. Realizar tus cambios y commitear frecuentemente
git add archivo.php
git commit -m "feat: descripción clara de los cambios realizados"

# 4. Subir la rama de feature al repositorio
git push origin development/feature/nombre-funcionalidad

# 5. Crear Pull Request en GitHub desde development/feature/nombre-funcionalidad hacia development
# (Desde la interfaz de GitHub)
```

### Integrar Cambios a Development

Una vez que tu funcionalidad esté completa:

1. Crea un **Pull Request** en GitHub desde `development/feature/nombre-funcionalidad` hacia `development`
2. Espera la revisión de código del equipo
3. Realiza los cambios solicitados si los hay
4. Una vez aprobado, se fusionará a `development`
5. Elimina tu rama de feature después de fusionar

```bash
# Después de fusionar, actualiza tu development local
git checkout development
git pull origin development

# Elimina la rama local de feature
git branch -d development/feature/nombre-funcionalidad

# Elimina la rama remota de feature (opcional)
git push origin --delete development/feature/nombre-funcionalidad
```

### Pasar Cambios a Producción (development → main)

Cuando development tenga funcionalidades estables listas para producción:

1. Crea un Pull Request desde `development` hacia `main`
2. Revisión final del equipo
3. Una vez aprobado, se fusiona a `main`
4. Se crea un tag de versión (opcional pero recomendado)

### Buenas Prácticas

-   **NO commitear** el archivo `.env` (contiene credenciales sensibles)
-   **NO commitear** las carpetas `vendor/` y `node_modules/`
-   Usar **mensajes de commit descriptivos** en un solo idioma (español)
-   Usar el formato **Conventional Commits** (ver sección "Commit Workflow Guidelines")
-   **Probar los cambios** antes de hacer push
-   **Documentar** funcionalidades nuevas o cambios importantes
-   **Usar Docker** para mantener consistencia en el entorno de desarrollo

### Commit Workflow Guidelines

Sigue este formato para mantener un historial de commits limpio y comprensible:

**Estructura básica:** `<type>(optional-scope): <descripción>`

**Tipos de commits comunes:**
- `feat` - Nueva funcionalidad
- `fix` - Corrección de errores
- `docs` - Cambios en documentación
- `chore` - Tareas de mantenimiento
- `refactor` - Refactorización de código
- `style` - Cambios de formato/estilo
- `test` - Agregar o modificar tests
- `build` - Cambios en la build o dependencias
- `ci` - Cambios en CI/CD

**Ejemplos de commits:**

```bash
# Nuevo feature
git commit -m "feat: agregar panel de asistencia"

# Corrección de error
git commit -m "fix: resolver problema de conexión a base de datos"

# Con descripción detallada
git commit -m "feat: agregar validación de huella dactilar" \
           -m "- Implementa verificación biométrica" \
           -m "- Agrega manejo de errores" \
           -m "- Actualiza migraciones"

# Documentación
git commit -m "docs: actualizar guía de instalación"
```

**Recomendaciones:**
- Mantén la primera línea ≤ 72 caracteres
- Usa imperativo: "agregar" no "agregado"
- Realiza commits pequeños y frecuentes
- Un commit = un cambio lógico

---

## Solución de Problemas

### Error de permisos en storage

```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Error de conexión a la base de datos

Verifica que:

1. Tengas conexión a internet (la BD es remota)
2. Las credenciales en `.env` sean correctas
3. Ejecuta: `docker-compose exec app php artisan db:show`

### Los contenedores no inician

```bash
# Verificar estado
docker-compose ps

# Ver logs de errores
docker-compose logs

# Reconstruir contenedores
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Error "Class not found"

```bash
docker-compose exec app composer dump-autoload
docker-compose exec app php artisan optimize:clear
```

---

## Documentación Adicional

Para más información detallada, consulta los siguientes archivos:

-   **SETUP.md** - Guía completa de configuración del proyecto
-   **DOCKER.md** - Documentación específica sobre Docker
-   **COMANDOS.md** - Referencia rápida de comandos útiles

---
