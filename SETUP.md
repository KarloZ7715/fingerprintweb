# Guía de Configuración - Proyecto fingerprintweb

## Stack Tecnológico

-   **Framework**: Laravel 12.35.0
-   **Panel Admin**: Filament 4.1.10
-   **PHP**: 8.2
-   **Base de Datos**: MySQL (Remota)
-   **Containerización**: Docker + Docker Compose

---

## Requisitos del Sistema

Antes de empezar, asegúrate de tener instalado:

-   **Git** - Control de versiones
-   **Docker Desktop** - Para Windows/Mac, o Docker + Docker Compose para Linux
-   **Editor de código** - VS Code, PHPStorm, u otro de tu preferencia

---

## Configuración Inicial

### 1. Clonar el Repositorio

```bash
git clone https://github.com/KarloZ7715/fingerprintweb.git
cd fingerprintweb
```

### 2. Cambiar a la Rama Develop

```bash
git checkout develop
```

### 3. Configurar Variables de Entorno

```bash
# Windows (PowerShell)
Copy-Item .env.example .env

# Linux/Mac
cp .env.example .env
```

**IMPORTANTE:** Configura las credenciales de la base de datos en tu archivo `.env`. Las credenciales se comparten por WhatsApp o Discord del equipo.

```env
DB_CONNECTION=mysql
DB_HOST=tu-host-de-base-de-datos
DB_PORT=3306
DB_DATABASE=nombre-de-la-base-de-datos
DB_USERNAME=tu-usuario
DB_PASSWORD=tu-contraseña
```

### 4. Construir e Iniciar Docker

```bash
docker-compose build
docker-compose up -d
```

### 5. Instalar Dependencias

```bash
docker-compose exec app composer install
```

### 6. Generar Application Key

```bash
docker-compose exec app php artisan key:generate
```

### 7. Crear Enlace de Storage

```bash
docker-compose exec app php artisan storage:link
```

### 8. Ejecutar Migraciones (Opcional)

```bash
docker-compose exec app php artisan migrate
```

### 9. Crear Usuario Administrador

```bash
docker-compose exec app php artisan make:filament-user
```

---

## Acceso a la Aplicación

Una vez completada la instalación:

-   **Aplicación Web**: http://localhost:8000
-   **Panel de Administración**: http://localhost:8000/admin

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

# Acceder al contenedor
docker-compose exec app bash
```

### Comandos de Laravel/Artisan

```bash
# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Crear un modelo
docker-compose exec app php artisan make:model NombreModelo

# Crear un recurso de Filament
docker-compose exec app php artisan make:filament-resource NombreRecurso

# Limpiar caché
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Ver información del sistema
docker-compose exec app php artisan about

# Ver información de la base de datos
docker-compose exec app php artisan db:show
```

### Comandos de Composer

```bash
# Instalar dependencias
docker-compose exec app composer install

# Actualizar dependencias
docker-compose exec app composer update

# Agregar un paquete
docker-compose exec app composer require vendor/package
```

---

## Estructura del Proyecto

```
fingerprintweb/
├── app/
│   ├── Filament/           # Recursos, páginas y widgets de Filament
│   │   ├── Resources/      # Recursos CRUD de Filament
│   │   ├── Pages/          # Páginas personalizadas
│   │   └── Widgets/        # Widgets del dashboard
│   ├── Http/
│   │   └── Controllers/    # Controladores
│   ├── Models/             # Modelos Eloquent
│   └── Providers/          # Service providers
├── config/                 # Archivos de configuración
├── database/
│   ├── migrations/         # Migraciones de base de datos
│   └── seeders/           # Seeders
├── docker/                 # Configuraciones de Docker
│   ├── nginx/             # Configuración de Nginx
│   └── php/               # Configuración de PHP
├── public/                # Archivos públicos (CSS, JS, imágenes)
├── resources/
│   └── views/             # Vistas Blade
├── routes/                # Rutas de la aplicación
├── storage/               # Archivos de almacenamiento
├── tests/                 # Pruebas
├── docker-compose.yml     # Configuración de Docker Compose
├── Dockerfile             # Definición de la imagen Docker
└── .env                   # Variables de entorno (NO COMMITEAR)
```

---

## Trabajar con Filament

### Crear un Recurso CRUD

```bash
docker-compose exec app php artisan make:filament-resource NombreModelo
```

Esto creará:

-   `app/Filament/Resources/NombreModeloResource.php`
-   `app/Filament/Resources/NombreModeloResource/Pages/`

### Crear una Página Personalizada

```bash
docker-compose exec app php artisan make:filament-page NombrePagina
```

### Crear un Widget

```bash
docker-compose exec app php artisan make:filament-widget NombreWidget
```

---

## Flujo de Trabajo con Git

### Actualizar Código Antes de Trabajar

```bash
# Actualizar rama develop
git checkout develop
git pull origin develop

# Actualizar dependencias si hubo cambios
docker-compose exec app composer install
```

### Crear una Nueva Funcionalidad

```bash
# Crear rama de funcionalidad desde develop
git checkout -b feature/nombre-funcionalidad

# Realizar cambios y commitear
git add .
git commit -m "Descripción clara de los cambios"

# Subir la rama al repositorio
git push origin feature/nombre-funcionalidad
```

### Integrar Cambios

1. Crear Pull Request en GitHub desde `feature/nombre-funcionalidad` hacia `develop`
2. Esperar revisión del equipo
3. Fusionar a `develop` una vez aprobado
4. Eliminar la rama de feature

```bash
# Después de fusionar
git checkout develop
git pull origin develop
git branch -d feature/nombre-funcionalidad
```

---

## Solución de Problemas

### Error de conexión a Docker

-   Verifica que Docker Desktop esté ejecutándose
-   Reinicia Docker Desktop

### Puerto 8000 ya en uso

Cambia el puerto en `docker-compose.yml`:

```yaml
ports:
    - "8080:80" # Cambia 8000 por otro puerto disponible
```

### Error de permisos (Linux/Mac)

```bash
sudo chown -R $USER:$USER .
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Error de conexión a la base de datos

1. Verifica tu conexión a internet (la BD es remota)
2. Confirma que las credenciales en `.env` sean correctas
3. Prueba: `docker-compose exec app php artisan db:show`

### Error "Class not found"

```bash
docker-compose exec app composer dump-autoload
docker-compose exec app php artisan optimize:clear
```

---

## Buenas Prácticas

-   **NO commitear** el archivo `.env` (contiene credenciales sensibles)
-   **NO commitear** las carpetas `vendor/` y `node_modules/`
-   **Usar Docker** para mantener consistencia en el entorno de desarrollo
-   **Comentar el código** para facilitar el entendimiento
-   **Hacer commits descriptivos** en español
-   **Probar los cambios** antes de hacer push
-   **Documentar** funcionalidades nuevas o cambios importantes

---

## Documentación Adicional

-   **README.md** - Documentación principal del proyecto
-   **DOCKER.md** - Guía específica sobre Docker
-   **COMANDOS.md** - Referencia rápida de comandos
