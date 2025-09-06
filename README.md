# FingerPrintWeb

Sistema de registro de asistencias con sensor de huellas dactilares.

## Inicio Rápido

### Prerrequisitos
- Docker Desktop
- Git

### Instalación
```bash
git clone <repo-url>
cd FingerPrintWeb/infra
docker compose up --build
```

### URLs de Desarrollo
- **Frontend:** http://localhost:5173
- **Backend API:** http://localhost:4000/health
- **MariaDB:** localhost:3306

### Credenciales DB
- Usuario: `fpw`
- Contraseña: `fpwpass`
- Base de datos: `fingerprintweb`

## Comandos Útiles

```bash
# Iniciar servicios
docker compose up -d

# Ver logs
docker compose logs -f

# Parar servicios
docker compose down

# Rebuild tras cambios en package.json
docker compose up --build
```

## Stack Tecnológico
- **Frontend:** React 19 + TypeScript + Vite + TailwindCSS
- **Backend:** Node.js 22 + TypeScript + Fastify
- **Base de datos:** MariaDB 11.4
- **Containerización:** Docker + Docker Compose
