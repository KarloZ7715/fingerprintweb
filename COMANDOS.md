# Comandos Útiles - fingerprintweb

## Docker

### Iniciar el proyecto

```powershell
docker-compose up -d
```

### Detener el proyecto

```powershell
docker-compose down
```

### Ver logs

```powershell
docker-compose logs -f
```

### Reiniciar contenedores

```powershell
docker-compose restart
```

### Reconstruir imágenes

```powershell
docker-compose build --no-cache
```

### Acceder al contenedor

```powershell
docker-compose exec app bash
```

---

## Laravel (dentro de Docker)

### Instalación inicial

```powershell
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan storage:link
```

### Migraciones

```powershell
# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Migración desde cero (BORRA DATOS)
docker-compose exec app php artisan migrate:fresh

# Ejecutar seeders
docker-compose exec app php artisan db:seed

# Reinstalar BD completa (BORRA DATOS)
docker-compose exec app php artisan migrate:fresh --seed
```

### Caché

```powershell
# Limpiar todas las cachés
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Optimizar aplicación (cachear)
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
docker-compose exec app php artisan optimize
```

### Información del sistema

```powershell
# Ver información de Laravel
docker-compose exec app php artisan about

# Ver información de la BD
docker-compose exec app php artisan db:show

# Ver rutas
docker-compose exec app php artisan route:list

# Ver rutas de Filament
docker-compose exec app php artisan route:list --path=admin
```

---

## Filament

### Crear usuario administrador

```powershell
docker-compose exec app php artisan make:filament-user
```

### Crear recursos

```powershell
# Crear recurso CRUD completo
docker-compose exec app php artisan make:filament-resource NombreModelo

# Ejemplo: Recurso para Empleado
docker-compose exec app php artisan make:filament-resource Empleado
```

### Crear componentes

```powershell
# Crear página personalizada
docker-compose exec app php artisan make:filament-page NombrePagina

# Crear widget
docker-compose exec app php artisan make:filament-widget NombreWidget

# Crear widget de estadísticas
docker-compose exec app php artisan make:filament-widget StatsOverview --stats-overview
```

---

## Composer

### Instalar/Actualizar paquetes

```powershell
# Instalar dependencias
docker-compose exec app composer install

# Actualizar dependencias
docker-compose exec app composer update

# Instalar paquete específico
docker-compose exec app composer require vendor/package

# Eliminar paquete
docker-compose exec app composer remove vendor/package

# Ver paquetes desactualizados
docker-compose exec app composer outdated
```

---

## Artisan Make (Crear archivos)

```powershell
# Crear modelo
docker-compose exec app php artisan make:model NombreModelo

# Crear modelo con migración
docker-compose exec app php artisan make:model NombreModelo -m

# Crear modelo con todo (migración, factory, seeder, controller)
docker-compose exec app php artisan make:model NombreModelo -mfsc

# Crear controlador
docker-compose exec app php artisan make:controller NombreController

# Crear migración
docker-compose exec app php artisan make:migration nombre_migracion

# Crear seeder
docker-compose exec app php artisan make:seeder NombreSeeder

# Crear middleware
docker-compose exec app php artisan make:middleware NombreMiddleware

# Crear request (validación)
docker-compose exec app php artisan make:request NombreRequest
```

---

## Base de Datos

### Consultas rápidas

```powershell
# Abrir Tinker (consola interactiva)
docker-compose exec app php artisan tinker

# Ver tablas de la BD
docker-compose exec app php artisan db:table

# Ver información detallada de una tabla
docker-compose exec app php artisan db:table nombre_tabla
```

### Ejemplos en Tinker

```php
// Ver todos los registros de una tabla
User::all();

// Crear un registro
User::create(['name' => 'Carlos', 'email' => 'carlos@example.com', 'password' => Hash::make('password')]);

// Buscar por ID
User::find(1);

// Contar registros
User::count();
```

---

## Testing

```powershell
# Ejecutar todas las pruebas
docker-compose exec app php artisan test

# Ejecutar pruebas con coverage
docker-compose exec app php artisan test --coverage

# Ejecutar una prueba específica
docker-compose exec app php artisan test --filter=NombrePrueba
```

---

## Git

```powershell
# Ver estado
git status

# Descargar cambios
git pull origin main

# Crear rama nueva
git checkout -b feature/nombre-funcionalidad

# Ver ramas
git branch

# Cambiar de rama
git checkout nombre-rama

# Agregar cambios
git add .

# Hacer commit
git commit -m "Descripción del cambio"

# Subir cambios
git push origin nombre-rama
```

---

## Debug y Logs

```powershell
# Ver logs de Laravel
docker-compose exec app tail -f storage/logs/laravel.log

# Ver logs de Docker
docker-compose logs -f app

# Ver logs de Nginx
docker-compose logs -f nginx

# Limpiar logs
docker-compose exec app truncate -s 0 storage/logs/laravel.log
```

---

## Atajos Combinados

### Resetear aplicación completa

```powershell
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
docker-compose exec app composer dump-autoload
docker-compose exec app php artisan optimize
```

### Preparar para producción

```powershell
docker-compose exec app composer install --optimize-autoloader --no-dev
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
docker-compose exec app php artisan optimize
```

---

## Solución de Problemas

### Permisos en Linux/Mac

```bash
sudo chown -R $USER:$USER .
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Error "Class not found"

```powershell
docker-compose exec app composer dump-autoload
docker-compose exec app php artisan optimize:clear
```

### Error de configuración

```powershell
docker-compose exec app php artisan config:clear
docker-compose restart
```

### Reinstalar desde cero

```powershell
# Detener contenedores
docker-compose down

# Limpiar volúmenes (BORRA TODO)
docker-compose down -v

# Reconstruir
docker-compose build --no-cache

# Iniciar y configurar
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan storage:link
docker-compose exec app php artisan migrate
```

---

## Comandos de Un Solo Uso (Primera Configuración)

Estos comandos solo se ejecutan UNA VEZ al configurar el proyecto:

```powershell
# 1. Copiar archivo de entorno
Copy-Item .env.example .env

# 2. Ejecutar script de configuración
.\docker-setup.ps1

# 3. Crear usuario admin de Filament
docker-compose exec app php artisan make:filament-user
```

---

## Flujo de Trabajo Diario

```powershell
# 1. Iniciar el día - Actualizar ramas y proyecto
git checkout develop
git pull origin develop
docker-compose up -d
docker-compose exec app composer install

# 2. Crear o actualizar la rama de feature
# Para crear desde develop:
git checkout -b feature/nombre-del-feature develop
# Si ya existe:
# git checkout feature/nombre-del-feature
# git pull origin feature/nombre-del-feature
# Mantenerla sincronizada con develop:
git fetch origin
git merge origin/develop

# 3. Trabajar en el código...

# 4. Probar cambios
docker-compose exec app php artisan test

# 5. Commit y push de la feature
git add .
git commit -m "Descripción de cambios"
git push origin feature/nombre-del-feature

# 6. Abrir PR y merge a develop
# (Crear PR en la plataforma, revisar, aprobar y mergear a develop)

# 7. Actualizar develop y limpiar ramas
git checkout develop
git pull origin develop
# Eliminar rama local y remota tras merge (opcional)
git branch -d feature/nombre-del-feature
git push origin --delete feature/nombre-del-feature

# 8. Detener proyecto (opcional)
docker-compose down
```

```

```
