# FingerPrintWeb

Sistema de registro de asistencias con sensor de huellas dactilares.

## 🚀 Inicio Rápido

### Prerrequisitos
- Docker Desktop
- Git

### Instalación
```bash
git clone https://github.com/KarloZ7715/fingerprintweb.git
cd fingerprintweb/infra
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

## 🛠️ Comandos Útiles

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

## 🏗️ Stack Tecnológico
- **Frontend:** React 19 + TypeScript + Vite + TailwindCSS
- **Backend:** Node.js 22 + TypeScript + Fastify
- **Base de datos:** MariaDB 11.4
- **Containerización:** Docker + Docker Compose

## 👥 Colaboración en Equipo

### Para trabajar en el proyecto:

1. **Clonar el repositorio:**
```bash
git clone https://github.com/KarloZ7715/fingerprintweb.git
cd fingerprintweb
```

2. **Crear una rama para tu feature:**
```bash
git checkout -b feature/nombre-de-tu-feature
```

3. **Desarrollar con Docker:**
```bash
cd infra
docker compose up --build
```

4. **Hacer commits pequeños y descriptivos:**
```bash
git add .
git commit -m "feat: descripción clara del cambio"
```

5. **Subir tu rama:**
```bash
git push origin feature/nombre-de-tu-feature
```

6. **Crear Pull Request en GitHub**

### 📋 Reglas de Colaboración
- Siempre trabajar en ramas separadas
- Hacer commits descriptivos
- Probar localmente antes de push
- Revisar Pull Requests de compañeros
- Mantener main branch estable
