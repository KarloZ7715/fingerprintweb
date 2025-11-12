# 📘 Guía de Uso - Gestión de Horarios

Esta guía te ayudará a utilizar correctamente la nueva funcionalidad de Gestión de Horarios.

---

## 🎯 Acceso al Módulo

1. Inicia sesión en el panel de administración Filament
2. En el menú lateral, navega a: **Gestión de Personal → Horarios**
3. Verás el listado de todos los horarios existentes

---

## ➕ Crear un Nuevo Horario

### Ejemplo 1: Turno de Mañana (Lunes a Viernes)

```
Paso 1: Click en "Nuevo Horario"

Paso 2: Completar formulario
┌─────────────────────────────────────────┐
│ INFORMACIÓN DEL HORARIO                 │
├─────────────────────────────────────────┤
│ Nombre: Turno Mañana                    │
│ Descripción: Personal operativo de      │
│              7am a 3pm                   │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ HORARIO DE TRABAJO                      │
├─────────────────────────────────────────┤
│ Hora de Entrada: 07:00 AM               │
│ Hora de Salida:  03:00 PM               │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ TOLERANCIAS                             │
├─────────────────────────────────────────┤
│ Tol. Entrada: 15 minutos                │
│ Tol. Salida:  15 minutos                │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ DÍAS LABORABLES                         │
├─────────────────────────────────────────┤
│ ☑ Lunes    ☑ Martes   ☑ Miércoles      │
│ ☑ Jueves   ☑ Viernes  ☐ Sábado         │
│ ☐ Domingo                               │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ CONFIGURACIÓN ADICIONAL                 │
├─────────────────────────────────────────┤
│ ☑ Requiere Registro de Entrada         │
│ ☑ Requiere Registro de Salida          │
│ ☑ Horario Activo                        │
│ Sucursal: (Dejar vacío para todas)     │
└─────────────────────────────────────────┘

Paso 3: Click en "Crear"
```

**Resultado:** El empleado puede llegar entre las 6:45 AM y las 7:15 AM sin ser marcado como retardado.

---

### Ejemplo 2: Turno de Tarde (Incluye Sábados)

```
┌─────────────────────────────────────────┐
│ Nombre: Turno Tarde                     │
│ Descripción: Personal de comercio       │
│              3pm a 11pm                  │
├─────────────────────────────────────────┤
│ Hora de Entrada: 03:00 PM               │
│ Hora de Salida:  11:00 PM               │
├─────────────────────────────────────────┤
│ Tol. Entrada: 10 minutos                │
│ Tol. Salida:  10 minutos                │
├─────────────────────────────────────────┤
│ ☑ Lun ☑ Mar ☑ Mié ☑ Jue ☑ Vie ☑ Sáb   │
└─────────────────────────────────────────┘
```

---

### Ejemplo 3: Horario Administrativo (Mayor Flexibilidad)

```
┌─────────────────────────────────────────┐
│ Nombre: Administrativo                  │
│ Descripción: Personal administrativo    │
├─────────────────────────────────────────┤
│ Hora de Entrada: 08:00 AM               │
│ Hora de Salida:  05:00 PM               │
├─────────────────────────────────────────┤
│ Tol. Entrada: 15 minutos                │
│ Tol. Salida:  30 minutos (más flexible) │
├─────────────────────────────────────────┤
│ ☑ Lun ☑ Mar ☑ Mié ☑ Jue ☑ Vie          │
└─────────────────────────────────────────┘
```

---

### Ejemplo 4: Horario para Sucursal Específica

```
┌─────────────────────────────────────────┐
│ Nombre: Turno Mall                      │
│ Descripción: Turno centro comercial     │
├─────────────────────────────────────────┤
│ Hora de Entrada: 10:00 AM               │
│ Hora de Salida:  10:00 PM               │
├─────────────────────────────────────────┤
│ Tol. Entrada: 5 minutos (estricto)     │
│ Tol. Salida:  5 minutos                 │
├─────────────────────────────────────────┤
│ ☑ Lun ☑ Mar ☑ Mié ☑ Jue ☑ Vie          │
│ ☑ Sáb ☑ Dom (7 días)                   │
├─────────────────────────────────────────┤
│ Sucursal: Centro Comercial XYZ ✓       │
└─────────────────────────────────────────┘
```

---

## 👥 Asignar Horario a Empleados

### Opción A: Al crear un empleado

```
1. Ir a: Gestión de Personal → Empleados
2. Click en "Nuevo Empleado"
3. Completar información personal
4. En "Información Laboral":
   ├── Sucursal: Seleccionar sucursal ✓
   └── Horario: Seleccionar horario (opcional)
5. Guardar
```

### Opción B: Editar empleado existente

```
1. Ir a: Gestión de Personal → Empleados
2. Click en "Editar" en el empleado deseado
3. Ir a sección "Información Laboral"
4. Cambiar el campo "Horario"
5. Guardar
```

---

## 🔍 Consultar Empleados de un Horario

### Método 1: Desde el listado de horarios

```
1. Ir a: Gestión de Personal → Horarios
2. Buscar el horario deseado
3. Click en icono 👥 "Ver Empleados"
4. Se abrirá lista de empleados filtrada
```

### Método 2: Desde empleados

```
1. Ir a: Gestión de Personal → Empleados
2. Usar el filtro "Horario"
3. Seleccionar el horario deseado
4. Ver empleados filtrados
```

---

## ✏️ Editar un Horario Existente

```
1. Ir a: Gestión de Personal → Horarios
2. Click en "Editar" (lápiz) en el horario
3. Modificar los campos necesarios
4. Click en "Guardar"
```

**⚠️ Nota:** Los cambios aplican inmediatamente a todos los empleados asignados.

---

## 🔄 Activar/Desactivar Horarios

### Método 1: Desde el listado (Toggle)

```
1. Ir a: Gestión de Personal → Horarios
2. Usar el toggle en la columna "Activo"
3. Click para cambiar estado
```

### Método 2: Desde acciones

```
1. Click en "..." del horario
2. Seleccionar "Activar" o "Desactivar"
3. Confirmar acción
```

### Método 3: Múltiples horarios

```
1. Seleccionar varios horarios (checkbox)
2. En acciones masivas elegir:
   - "Activar Seleccionados" o
   - "Desactivar Seleccionados"
3. Confirmar
```

**¿Qué significa desactivar un horario?**
- ❌ No aparecerá en la lista de horarios disponibles al crear/editar empleados
- ✅ Los empleados que ya lo tenían asignado lo mantienen
- ✅ Se puede reactivar cuando sea necesario

---

## 🗑️ Eliminar un Horario

```
1. Ir a: Gestión de Personal → Horarios
2. Click en "Editar" en el horario
3. Click en "Eliminar" (arriba a la derecha)
4. Confirmar eliminación
```

**⚠️ Restricciones:**
- ❌ **No se puede eliminar** si tiene empleados asignados
- ✅ Primero debes reasignar los empleados a otro horario
- ✅ O desasignarlos (dejar horario vacío)

**Si intentas eliminar un horario con empleados:**
```
┌─────────────────────────────────────────┐
│ ⚠️  NO SE PUEDE ELIMINAR                │
├─────────────────────────────────────────┤
│ Este horario tiene empleados asignados. │
│ Debes reasignarlos antes de eliminarlo. │
└─────────────────────────────────────────┘
```

---

## 🔎 Filtrar y Buscar Horarios

### Buscar por nombre
```
Usar el campo de búsqueda en la parte superior
Ejemplo: "Turno", "Mañana", "Administrativo"
```

### Filtrar por estado
```
Filtro: Estado
├── Activo
└── Inactivo
```

### Filtrar por sucursal
```
Filtro: Sucursal
├── Todas las sucursales
├── Sucursal A
├── Sucursal B
└── ...
```

### Filtrar por requisitos
```
Filtros adicionales:
├── Requiere Entrada (toggle)
└── Requiere Salida (toggle)
```

---

## 📊 Interpretar la Tabla de Horarios

```
┌────────────────┬──────────┬──────────┬────────┬────────┬──────────────┬──────────┬──────────┬────────┐
│ Nombre         │ Entrada  │ Salida   │ Tol.E  │ Tol.S  │ Días         │ Empleados│ Sucursal │ Activo │
├────────────────┼──────────┼──────────┼────────┼────────┼──────────────┼──────────┼──────────┼────────┤
│ Turno Mañana   │ 07:00 AM │ 03:00 PM │ 15 min │ 15 min │ Lun-Vie      │    12    │ Todas    │   ✓    │
│ Turno Tarde    │ 03:00 PM │ 11:00 PM │ 10 min │ 10 min │ Lun-Sáb      │     8    │ Todas    │   ✓    │
│ Administrativo │ 08:00 AM │ 05:00 PM │ 15 min │ 30 min │ Lun-Vie      │     5    │ Todas    │   ✓    │
│ Turno Mall     │ 10:00 AM │ 10:00 PM │  5 min │  5 min │ Lun-Dom      │     3    │ Mall XYZ │   ✓    │
└────────────────┴──────────┴──────────┴────────┴────────┴──────────────┴──────────┴──────────┴────────┘
```

**Leyenda:**
- 🟢 **Entrada** (verde): Hora esperada de inicio
- 🔴 **Salida** (rojo): Hora esperada de término
- **Tol.E/S**: Tolerancia en minutos
- **Días**: Abreviatura de días laborables
- **Empleados**: Cantidad de empleados asignados (click para ver)
- **Activo**: Toggle para activar/desactivar

---

## ⚙️ Configuraciones Especiales

### Horario sin registro de salida
```
Usar cuando:
- Horarios variables de salida
- Personal que no tiene hora fija de salida
- Solo se necesita controlar entrada

Configuración:
├── ☑ Requiere Registro de Entrada
└── ☐ Requiere Registro de Salida
```

### Horario solo para control de salida
```
Usar cuando:
- Solo importa la hora de salida
- Entrada es libre

Configuración:
├── ☐ Requiere Registro de Entrada
└── ☑ Requiere Registro de Salida
```

### Horario con días no consecutivos
```
Ejemplo: Lunes, Miércoles, Viernes
├── ☑ Lunes
├── ☐ Martes
├── ☑ Miércoles
├── ☐ Jueves
├── ☑ Viernes
├── ☐ Sábado
└── ☐ Domingo
```

---

## ❓ Preguntas Frecuentes

### ¿Puedo tener un empleado sin horario asignado?
✅ Sí, el campo horario es opcional. Útil para personal eventual o contratistas.

### ¿Qué sucede si cambio el horario de un empleado?
✅ El cambio aplica inmediatamente. Las asistencias futuras usarán el nuevo horario.

### ¿Puedo crear horarios con jornadas nocturnas?
✅ Sí. Ejemplo: Entrada 11:00 PM, Salida 07:00 AM (día siguiente).

### ¿Cuántas tolerancias puedo configurar?
✅ Entre 0 y 120 minutos para entrada y salida (independientes).

### ¿Puedo duplicar un horario?
⚠️ Actualmente no hay función de duplicar, pero puedes crear uno nuevo copiando los valores.

### ¿Se pueden crear horarios rotativos?
⚠️ Actualmente no. Un empleado tiene un horario fijo. En futuras versiones se podría implementar.

---

## 🎯 Casos de Uso Reales

### Caso 1: Empresa con 2 turnos
```
Configuración:
├── Turno 1: 06:00 - 14:00 (Lun-Vie)
└── Turno 2: 14:00 - 22:00 (Lun-Sáb)

Empleados:
├── Grupo A → Turno 1 (20 empleados)
└── Grupo B → Turno 2 (15 empleados)
```

### Caso 2: Tienda con horario comercial
```
Configuración:
└── Turno Comercio: 09:00 - 21:00 (Lun-Dom)
    ├── Tolerancia: 5 min (horario comercial estricto)
    └── Todos los días (incluido domingos)
```

### Caso 3: Oficina administrativa
```
Configuración:
└── Administrativo: 08:00 - 17:00 (Lun-Vie)
    ├── Tolerancia entrada: 15 min
    ├── Tolerancia salida: 30 min (más flexible)
    └── Solo días hábiles
```

### Caso 4: Múltiples sucursales
```
Configuración:
├── Horario General (sin sucursal)
│   └── Aplica a todas las sucursales
├── Horario Mall (Sucursal: Centro Comercial)
│   └── Solo para empleados de esa sucursal
└── Horario Aeropuerto (Sucursal: Terminal)
    └── Solo para empleados de esa sucursal
```

---

## 🚀 Tips y Mejores Prácticas

### ✅ Recomendaciones

1. **Nombres descriptivos**: Usa nombres claros como "Turno Mañana", no solo "Turno 1"
2. **Tolerancias razonables**: 10-15 minutos es estándar en la mayoría de empresas
3. **Horarios genéricos**: Crea horarios sin sucursal para usar en todas las sedes
4. **Desactivar en vez de eliminar**: Si un horario ya no se usa, desactívalo en lugar de eliminarlo
5. **Revisar empleados antes de editar**: Verifica cuántos empleados tienen el horario antes de cambios importantes

### ❌ Evitar

1. No crear horarios muy similares (duplicados)
2. No eliminar horarios con empleados asignados
3. No usar tolerancias muy altas (más de 30 min generalmente no es recomendable)
4. No dejar horarios inactivos innecesariamente

---

## 📞 Soporte

Si tienes dudas o encuentras problemas:
1. Revisa esta guía
2. Consulta con tu administrador del sistema
3. Verifica los logs del sistema en caso de errores

---

**✨ ¡Listo! Ya puedes gestionar horarios como un experto.**
