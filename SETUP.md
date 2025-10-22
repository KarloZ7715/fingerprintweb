# 🚀 Guía de Configuración Rápida - Proyecto fingerprintweb

## 📋 Stack Tecnológico

-   **Framework**: Laravel 12.35.0
-   **Panel Admin**: Filament 4.1.10
-   **PHP**: 8.2
-   **Base de Datos**: MySQL (Remota en AlwaysData)
-   **Containerización**: Docker + Docker Compose

---

## ⚡ Configuración Rápida (Para nuevos miembros del equipo)

### 1. Requisitos

Antes de empezar, asegúrate de tener instalado:

-   **Git**
-   **Docker Desktop** (para Windows/Mac) o **Docker + Docker Compose** (para Linux)

### 2. Configurar Variables de Entorno

```bash
# En Windows (PowerShell)
Copy-Item .env.example .env

# En Linux/Mac
cp .env.example .env
```

**Nota**: El archivo `.env` ya viene preconfigurado con las credenciales de la base de datos remota.

### 3. Iniciar con Docker

#### Windows:

```powershell
.\docker-setup.ps1
```

#### Linux/Mac:

```bash
chmod +x docker-setup.sh
./docker-setup.sh
```

### 4. Crear Usuario Administrador de Filament

```bash
docker-compose exec app php artisan make:filament-user
```

Sigue las instrucciones en pantalla para crear tu usuario administrador.

---

## Acceso a la Aplicación

Una vez iniciado el proyecto:

-   **Aplicación Web**: http://localhost:8000
-   **Panel de Administración Filament**: http://localhost:8000/admin

---

## Comandos Útiles

### Docker

```bash
# Iniciar contenedores
docker-compose up -d

# Detener contenedores
docker-compose down

# Ver logs
docker-compose logs -f app

# Acceder al contenedor
docker-compose exec app bash
```

### Artisan (dentro del contenedor)

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
```

### Composer (dentro del contenedor)

```bash
# Instalar dependencias
docker-compose exec app composer install

# Actualizar dependencias
docker-compose exec app composer update

# Agregar un paquete
docker-compose exec app composer require vendor/package
```

---

## 📁 Estructura del Proyecto

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
│   └── Providers/
│       └── Filament/       # Providers de Filament
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

### Crear un Recurso

```bash
docker-compose exec app php artisan make:filament-resource NombreTabla
```

Por ejemplo, para trabajar con la tabla `empleado`:

```bash
docker-compose exec app php artisan make:filament-resource Empleado
```

Esto creará:

-   `app/Filament/Resources/EmpleadoResource.php`
-   `app/Filament/Resources/EmpleadoResource/Pages/`

### Crear una Página Personalizada

```bash
docker-compose exec app php artisan make:filament-page NombrePagina
```

### Crear un Widget

```bash
docker-compose exec app php artisan make:filament-widget NombreWidget
```

---

## Solución de Problemas Comunes

### Error: "No se puede conectar a Docker"

-   Asegúrate de que Docker Desktop esté ejecutándose
-   Reinicia Docker Desktop

### Error: "Puerto 8000 ya en uso"

-   Cambia el puerto en `docker-compose.yml` (línea `ports: - "8000:80"`)
-   O detén el servicio que está usando el puerto 8000

### Error: "Permission denied" en Linux/Mac

```bash
sudo chown -R $USER:$USER .
chmod -R 755 storage bootstrap/cache
```

### Error de conexión a la base de datos

-   Verifica tu conexión a internet
-   Confirma que las credenciales en `.env` sean correctas
-   Prueba la conexión: `docker-compose exec app php artisan db:show`

---

## Flujo de Trabajo en Equipo

### 1. Antes de Empezar a Trabajar

```bash
# Obtener últimos cambios
git pull origin main

# Actualizar dependencias (si hubo cambios)
docker-compose exec app composer install
```

### 2. Trabajar en una Nueva Funcionalidad

```bash
# Crear una rama nueva
git checkout -b feature/nombre-funcionalidad

# Hacer tus cambios...

# Guardar cambios
git add .
git commit -m "Descripción clara de los cambios"
git push origin feature/nombre-funcionalidad
```

### 3. Fusionar Cambios

-   Crea un Pull Request en GitHub
-   Espera revisión del equipo
-   Fusiona a la rama `main`

---

## ⚠️ Importante

-   **NO COMMITEAR** el archivo `.env` (ya está en `.gitignore`)
-   **NO COMMITEAR** la carpeta `vendor/`
-   **USAR SIEMPRE DOCKER** para mantener consistencia en el equipo a no ser que seas Brayan
-   **COMENTAR EL CÓDIGO** para que todos entiendan
-   **HACER COMMITS DESCRIPTIVOS**

---
