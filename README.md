# fingerprintweb — Desarrollo local (rápido)

Este repositorio usa Laravel + Filament. Para desarrollo rápido y reproducible usamos Docker y SQLite por defecto. Estas instrucciones te ayudarán a levantar el entorno, ejecutar migraciones y cambiar a MySQL si lo necesitas.

## Requisitos
- Docker y docker-compose instalados
- Git

## Levantar el entorno (rápido)

1. Levanta los contenedores:

```bash
docker-compose up -d
```

2. Ejecuta las migraciones (la configuración por defecto usa SQLite dentro del contenedor):

```bash
docker-compose exec app php artisan migrate --force
```

3. Limpiar caches / regenerar autoload si haces cambios en clases:

```bash
docker-compose exec app composer dump-autoload
docker-compose exec app php artisan optimize:clear
```

4. Ver rutas para comprobar que la app arranca:

```bash
docker-compose exec app php artisan route:list
```

## Comandos útiles dentro del contenedor `app`

- Abrir una shell dentro del contenedor:

```bash
docker-compose exec app bash
```

- Ejecutar artisan:

```bash
docker-compose exec app php artisan <comando>
```

- Ejecutar composer/npm (si necesitas):

```bash
docker-compose exec app composer install
docker-compose exec app npm install
docker-compose exec app npm run dev
```

## Crear usuario administrador (rápido)

El proyecto incluye un seeder para crear un usuario administrador por defecto. Para ejecutarlo:

```bash
docker-compose exec app php artisan db:seed
```

Esto creará un administrador con las siguientes credenciales:
- Email: admin@fingerprint.local
- Usuario: admin
- Contraseña: password

También puedes crear administradores adicionales manualmente con Tinker:

```bash
docker-compose exec app php artisan tinker
>>> \App\Models\Administrador::create([
    'name' => 'Tu Nombre',
    'username' => 'tu-usuario',
    'email' => 'tu@email.com',
    'password' => bcrypt('tu-password')
]);
```

## Si quieres usar MySQL en Docker (opcional)

Actualmente el proyecto está configurado por defecto para SQLite (más simple para dev). Si prefieres usar MySQL en Docker:

1. Edita `.env` y ajusta:

```dotenv
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=supertiendasis_control
DB_USERNAME=root
DB_PASSWORD=secret
```

2. Asegúrate de que `docker-compose.yml` contenga el servicio `mysql` (ya viene incluido en el repo). Luego recrea contenedores:

```bash
docker-compose up -d --build
```

3. (Opcional) Si el contenedor MySQL tarda en arrancar añade un `depends_on`/`healthcheck` para que `app` espere antes de ejecutar migraciones.

Nota: para evitar problemas de puertos en el host, el servicio MySQL en `docker-compose.yml` no expone 3306 por defecto; la comunicación se realiza por la red de Docker entre contenedores.

## Qué cambié durante el setup local

- Se añadieron y ajustaron varias `Filament Resources` y `Pages` para compatibilidad con Filament v4 (tipos `navigationIcon`, `navigationGroup`, `view`, y correcciones PSR-4 en páginas). Si ves errores de autoload ejecuta:

```bash
docker-compose exec app composer dump-autoload
docker-compose exec app php artisan package:discover
```

## Troubleshooting rápido
- Si Composer se queja de extensiones faltantes (p. ej. `ext-intl`), instala la extensión dentro de la imagen PHP o añade la extensión a la imagen Docker que uses.
- Si `php artisan migrate` falla por conexión MySQL, asegúrate de que `.env` esté apuntando a SQLite o que el servicio MySQL esté operativo (ver `docker-compose logs mysql`).

## Próximos pasos sugeridos
- Crear un `seeder` para el usuario admin y documentarlo.
- Añadir healthcheck para MySQL en `docker-compose.yml` si decides usar MySQL permanentemente.

---

Si quieres, puedo ahora:
- Añadir un seeder que cree un admin por defecto.
- Hacer un escaneo automático de `app/Filament` para normalizar propiedades restantes.

Archivo creado: `README.md` (raíz del repo)
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

## Configuración Recomendada para Windows

### Pasos de Configuración (Windows)

#### Paso 1: Instalar WSL 2 con Ubuntu

Abre **PowerShell como administrador** y ejecuta:

```powershell
wsl --install -d Ubuntu
```

#### Paso 2: Configurar Ubuntu (primera vez)

Después, abre **Ubuntu** desde el menú Inicio. Te pedirá crear usuario y contraseña:

(Si te pidió ingresar usuario y contraseña al instalar Ubuntu, omite este paso)

```bash
# Ingresa un nombre de usuario (ej: tu nombre)
username: carloscc
# Ingresa una contraseña (la que quieras)
password: ••••••••
```

#### Paso 3: Actualizar Ubuntu

Dentro de Ubuntu, ejecuta:

```bash
sudo apt update && sudo apt upgrade -y
```

#### Paso 4: Instalar Docker en Ubuntu

Ahora instala Docker con este método simplificado:

```bash
# Instalar dependencias
sudo apt install -y ca-certificates curl gnupg lsb-release

# Agregar clave de Docker
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

# Crear el archivo de repositorio directamente (evita problemas con /dev/null)
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list

# Verificar que el archivo se creó correctamente
ls -la /etc/apt/sources.list.d/

# Actualizar repositorios
sudo apt update

# Instalar Docker
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Agregar tu usuario a docker
sudo usermod -aG docker $USER
newgrp docker
```

**Importante:** Si tienes problemas con el archivo "docker.list " primero limpia cualquier archivo corrupto previo (si **NO** has tenido problemas con la instalacion de docker en Ubuntu omite este paso):

**NO** hagas este paso si no tienes problemas con docker en Ubuntu

```bash
# Eliminar archivos docker problemáticos (incluso con espacios en el nombre)
sudo rm -f /etc/apt/sources.list.d/docker.*
sudo rm -f "/etc/apt/sources.list.d/docker.list "

# Limpiar caché de apt
sudo apt clean
```

#### Paso 5: Habilitar WSL Integration en Docker Desktop

1. Abre **Docker Desktop** en Windows
2. Ve a **Settings** → **Resources** → **WSL Integration**
3. Activa el toggle de **Ubuntu**
4. Haz clic en **Apply & Restart**

#### Paso 6: Obtener el Proyecto en Ubuntu

Tienes **dos opciones** según tu situación:

**Opción A: Si tienes cambios sin commitear (Migración segura)**

Si ya tenías el proyecto en Windows con cambios locales, usa esta opción para no perderlos:

```bash
# 1. Abre Ubuntu
# 2. Crea la carpeta de destino
mkdir -p ~/proyectos

# 3. Copia todo el proyecto desde Windows a Ubuntu
# Reemplaza 'tu-usuario' con tu usuario de Windows
cp -r /mnt/c/Users/tu-usuario/ruta/a/fingerprintweb ~/proyectos/

# 4. Entra a la carpeta
cd ~/proyectos/fingerprintweb

# 5. Verifica que tus cambios locales estén ahí
git status
```

**Opción B: Si NO TIENES cambios sin commitear**

Abre Ubuntu y ejecuta:

```bash
# Crear carpeta de proyectos
mkdir -p ~/proyectos
cd ~/proyectos

# Clonar el repositorio
git clone https://github.com/KarloZ7715/fingerprintweb.git
cd fingerprintweb
git checkout development
```

### Acceder al Proyecto desde VS Code (Windows)

1. Abre **VS Code**
2. Instala la extensión **"Remote Development"** (Microsoft)
3. En la esquina inferior izquierda, haz clic en **"><"** (botón remoto)
4. Selecciona **"Connect to WSL"**
5. Abre la carpeta: `/home/tu-usuario/proyectos/fingerprintweb`

Ahora tendrás acceso completo con buen rendimiento desde VS Code Windows.

---

## Instalación y Configuración

> **Nota:** Si seguiste la sección "[Configuración Recomendada para Windows](#configuración-recomendada-para-windows)", ya habrás completado los Pasos 1-2. Continúa desde el **Paso 3**.

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

# Linux/Mac/WSL
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
docker compose build
docker compose up -d
```

> ℹ️ **Nota:** `docker compose` es la versión moderna de `docker-compose`. Si tu sistema solo reconoce `docker-compose`, es también válido.

### 4. Instalar Dependencias

Instala las dependencias de PHP con Composer:

```bash
docker compose exec app composer install
```

### 5. Crear Enlace de Storage

Crea el enlace simbólico para el almacenamiento público:

```bash
docker compose exec app php artisan storage:link
```

### 6. Ejecutar Migraciones (Opcional)

Si necesitas ejecutar migraciones en la base de datos:

```bash
docker compose exec app php artisan migrate
```

### 7. Crear Usuario Administrador

Crea tu usuario para acceder al panel de administración:

```bash
docker compose exec app php artisan make:filament-user
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
docker compose up -d

# Detener contenedores
docker compose down

# Ver logs en tiempo real
docker compose logs -f app

# Reiniciar contenedores
docker compose restart

# Acceder al contenedor de la aplicación
docker compose exec app bash
```

### Comandos de Laravel/Artisan

```bash
# Ejecutar migraciones
docker compose exec app php artisan migrate

# Limpiar caché
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Ver información del sistema
docker compose exec app php artisan about

# Ver rutas de la aplicación
docker compose exec app php artisan route:list

# Ver información de la base de datos
docker compose exec app php artisan db:show
```

### Comandos de Composer

```bash
# Instalar dependencias
docker compose exec app composer install

# Actualizar dependencias
docker compose exec app composer update

# Instalar un paquete específico
docker compose exec app composer require vendor/package
```

### Comandos de Filament

```bash
# Crear un recurso CRUD
docker compose exec app php artisan make:filament-resource NombreModelo

# Crear una página personalizada
docker compose exec app php artisan make:filament-page NombrePagina

# Crear un widget
docker compose exec app php artisan make:filament-widget NombreWidget

# Crear usuario administrador
docker compose exec app php artisan make:filament-user
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
docker compose exec app composer install
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
git checkout -b feature/nombre-funcionalidad

# 5. Realizar tus cambios y commitear frecuentemente
git add archivo.php
git commit -m "feat: descripción clara de los cambios realizados"

# 6. Subir la rama de feature al repositorio
git push origin feature/nombre-funcionalidad

# 7. Crear Pull Request en GitHub desde feature/nombre-funcionalidad hacia development
# (Desde la interfaz de GitHub)
```

#### Opción B: Si YA tienes `development` en tu repositorio local

Sigue estos pasos para actualizar y crear tu rama de feature:

```bash
# 1. Asegúrate de estar en development y actualízalo
git checkout development
git pull origin development

# 2. Crear la rama de feature desde development
git checkout -b feature/nombre-funcionalidad

# 3. Realizar tus cambios y commitear frecuentemente
git add archivo.php
git commit -m "feat: descripción clara de los cambios realizados"

# 4. Subir la rama de feature al repositorio
git push origin feature/nombre-funcionalidad

# 5. Crear Pull Request en GitHub desde feature/nombre-funcionalidad hacia development
# (Desde la interfaz de GitHub)
```

### Integrar Cambios a Development

Una vez que tu funcionalidad esté completa:

1. Crea un **Pull Request** en GitHub desde `feature/nombre-funcionalidad` hacia `development`
2. Espera la revisión de código del equipo
3. Realiza los cambios solicitados si los hay
4. Una vez aprobado, se fusionará a `development`
5. Elimina tu rama de feature después de fusionar

```bash
# Después de fusionar, actualiza tu development local
git checkout development
git pull origin development

# Elimina la rama local de feature (opcional)
git branch -d feature/nombre-funcionalidad

# Elimina la rama remota de feature (opcional)
git push origin --delete feature/nombre-funcionalidad
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

-   `feat` - Nueva funcionalidad
-   `fix` - Corrección de errores
-   `docs` - Cambios en documentación
-   `chore` - Tareas de mantenimiento
-   `refactor` - Refactorización de código
-   `style` - Cambios de formato/estilo
-   `test` - Agregar o modificar tests
-   `build` - Cambios en la build o dependencias
-   `ci` - Cambios en CI/CD

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

-   Mantén la primera línea ≤ 72 caracteres
-   Usa imperativo: "agregar" no "agregado"
-   Realiza commits pequeños y frecuentes
-   Un commit = un cambio lógico

---

## Solución de Problemas

### Error: "docker-compose: command not found"

Si recibas este error, usa la versión moderna:

```bash
# En lugar de:
docker-compose build

# Usa:
docker compose build
```

La mayoría de sistemas modernos incluyen `docker compose` (sin guión). Si tampoco funciona, significa que Docker no está correctamente instalado.

**Para Windows con WSL:** Asegúrate de seguir la sección "[Configuración Recomendada para Windows](#configuración-recomendada-para-windows)".

### Error de permisos en storage

```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Error de conexión a la base de datos

Verifica que:

1. Tengas conexión a internet (la BD es remota)
2. Las credenciales en `.env` sean correctas
3. Ejecuta: `docker compose exec app php artisan db:show`

### Los contenedores no inician

```bash
# Verificar estado
docker compose ps

# Ver logs de errores
docker compose logs

# Reconstruir contenedores
docker compose down
docker compose build --no-cache
docker compose up -d
```

### Error "Class not found"

```bash
docker compose exec app composer dump-autoload
docker compose exec app php artisan optimize:clear
```

### Latencia muy alta en Windows

Si experimentas tiempos de carga >5 segundos, **no estás usando WSL 2**. Sigue la sección "[Configuración Recomendada para Windows](#configuración-recomendada-para-windows)" para obtener rendimiento óptimo (90-150ms).

---

## Documentación Adicional

Para más información detallada, consulta los siguientes archivos:

-   **SETUP.md** - Guía completa de configuración del proyecto
-   **DOCKER.md** - Documentación específica sobre Docker
-   **COMANDOS.md** - Referencia rápida de comandos útiles

---
