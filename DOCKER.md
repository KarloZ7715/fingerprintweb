# Guía de Docker

## Requisitos Previos

-   Docker Desktop instalado en tu sistema
-   Git instalado

## Configuración Inicial

### Primera vez

1. **Copia el archivo de ejemplo de entorno:**

```bash
# Windows (PowerShell)
Copy-Item .env.example .env

# Linux/Mac
cp .env.example .env
```

2. **Construye e inicia los contenedores:**

```bash
docker-compose build
docker-compose up -d
```

3. **Instala las dependencias de Composer:**

```bash
docker-compose exec app composer install
```

4. **Genera la clave de aplicación:**

```bash
docker-compose exec app php artisan key:generate
```

5. **Crea el enlace simbólico de storage:**

```bash
docker-compose exec app php artisan storage:link
```

6. **Ejecuta las migraciones (opcional):**

```bash
docker-compose exec app php artisan migrate
```

7. **Crea un usuario administrador de Filament:**

```bash
docker-compose exec app php artisan make:filament-user
```

```bash
docker-compose exec app php artisan make:filament-user
```

¡Listo! Tu aplicación estará disponible en http://localhost:8000

## Comandos Útiles

### Iniciar el proyecto

```bash
docker-compose up -d
```

### Detener el proyecto

```bash
docker-compose down
```

### Ver logs

```bash
# Todos los servicios
docker-compose logs -f

# Solo la aplicación
docker-compose logs -f app

# Solo nginx
docker-compose logs -f nginx
```

### Acceder al contenedor de la aplicación

```bash
docker-compose exec app bash
```

### Ejecutar comandos de Artisan

```bash
docker-compose exec app php artisan <comando>
```

Ejemplos:

```bash
# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Crear un controlador
docker-compose exec app php artisan make:controller NombreController

# Limpiar caché
docker-compose exec app php artisan cache:clear
```

### Ejecutar comandos de Composer

```bash
docker-compose exec app composer <comando>
```

Ejemplos:

```bash
# Instalar dependencias
docker-compose exec app composer install

# Actualizar dependencias
docker-compose exec app composer update

# Instalar un paquete
docker-compose exec app composer require nombre/paquete
```

### Crear un usuario administrador de Filament

```bash
docker-compose exec app php artisan make:filament-user
```

### Reinstalar desde cero

```bash
# Detener y eliminar contenedores
docker-compose down

# Eliminar volúmenes (opcional, esto borrará datos)
docker-compose down -v

# Reconstruir contenedores
docker-compose build --no-cache

# Iniciar nuevamente
docker-compose up -d
```

## Acceso a la Aplicación

-   **Aplicación principal**: http://localhost:8000
-   **Panel de Filament**: http://localhost:8000/admin

## Solución de Problemas

### Error de permisos en storage/logs

```bash
docker-compose exec app chmod -R 777 storage bootstrap/cache
```

### La aplicación no carga

1. Verifica que los contenedores estén corriendo:

```bash
docker-compose ps
```

2. Revisa los logs:

```bash
docker-compose logs -f
```

3. Reinicia los contenedores:

```bash
docker-compose restart
```

### Error de conexión a la base de datos

Verifica que las credenciales en el archivo `.env` sean correctas y que tengas acceso a internet para conectar con la base de datos remota.

## Estructura de Docker

-   `Dockerfile`: Define la imagen de la aplicación con PHP 8.2-FPM
-   `docker-compose.yml`: Orquesta los servicios (app + nginx)
-   `docker/nginx/default.conf`: Configuración de Nginx
-   `docker/php/php.ini`: Configuración personalizada de PHP

## Notas Importantes

-   **No commitear el archivo `.env`**: Contiene credenciales sensibles
-   **Puerto 8000**: Asegúrate de que este puerto esté disponible
