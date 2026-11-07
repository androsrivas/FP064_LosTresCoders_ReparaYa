# Producto 2 · Aplicación de gestión de reparaciones

Aplicación web de **ReparaYa** para gestionar averías domésticas (fontanería, electricidad, etc.). Los clientes solicitan asistencias y el administrador gestiona las incidencias, asigna técnicos y consulta el calendario de trabajo.

- **Tecnología:** PHP nativo con arquitectura **MVC**. **Sin Laravel ni ningún otro framework** (restricción del enunciado).
- **Base de datos:** MySQL con PDO y sentencias preparadas.
- **URL local:** `http://localhost:<WEB_PORT>/producto2`

> Para levantar el entorno Docker, las variables `.env`, las rutas de Apache, WordPress y el despliegue en AWS, consulta el [README principal](../README.md). Este documento solo describe el Producto 2.

## Contenido

1. [Arquitectura](#arquitectura)
2. [Puesta en marcha](#puesta-en-marcha)
3. [Base de datos](#base-de-datos)
4. [Roles y permisos](#roles-y-permisos)
5. [Ciclo de vida de una incidencia](#ciclo-de-vida-de-una-incidencia)
6. [Reglas de negocio](#reglas-de-negocio)
7. [Seguridad](#seguridad)
8. [Librerías externas](#librerías-externas)
9. [Usuarios de prueba](#usuarios-de-prueba)
10. [Reparto del equipo](#reparto-del-equipo)
11. [Flujo de trabajo con Git](#flujo-de-trabajo-con-git)
12. [Documentación y entregables](#documentación-y-entregables)

---

## Arquitectura

```
producto2/
├── app/
│   ├── controllers/     # AuthController · PerfilController · IncidenciaController
│   │                    # AdminController · TecnicoController · AgendaController
│   │                    # CalendarioController · TipoServicioController
│   ├── models/          # Usuario · Cliente · Tecnico · Incidencia
│   │                    # HistorialEstado · TipoServicio
│   ├── services/        # IncidenciaService.php (transiciones + regla 48 h)
│   ├── views/
│   │   ├── layouts/     # header · footer · menu
│   │   ├── home/ · auth/ · perfil/ · errors/
│   │   ├── incidencias/ # detalle.php (compartida, con historial)
│   │   └── cliente/ · admin/ · tecnico/
│   └── core/            # Controller · Model · Database · Router
│                        # Auth · Session · Validator · Helpers
├── config/              # config.example.php · config.php (en .gitignore)
├── public/              # index.php · assets/css|js|img
├── database/            # reparaya.sql (original) · ampliacion.sql
├── docs/                # Word, capturas de Git, mapa conceptual
└── README.md
```

| Carpeta | Responsabilidad |
|---|---|
| `public/` | Único directorio expuesto al navegador. Contiene el punto de entrada `index.php` y los recursos estáticos. El resto del código queda fuera de la raíz web. |
| `config/` | Configuración centralizada (conexión a la BD, URL base). Las credenciales reales **no se suben al repositorio**. |
| `app/controllers/` | Reciben la petición HTTP, comprueban el rol del usuario y deciden qué acción ejecutar. |
| `app/models/` | Acceso a datos con **sentencias preparadas (PDO)**. |
| `app/services/` | Reglas de negocio, siempre validadas en el servidor, independientemente de la interfaz. |
| `app/views/` | Plantillas (solo visualización) con menús dinámicos según el rol. |
| `app/core/` | Clases base: router, controlador y modelo base, conexión, sesión, autenticación, validación. |
| `database/` | Scripts SQL: la base original y las ampliaciones documentadas aparte. |
| `docs/` | Documentación que acompaña al código. |

**Flujo de una petición:** `public/index.php` → `Router` → `Controller` (comprueba rol con `Auth`) → `Service` (reglas de negocio) → `Model` (PDO) → `View`.

---

## Puesta en marcha

Con el entorno Docker del [README principal](../README.md) en marcha:

1. Importa los scripts de `database/` en la base de datos `DB_DATABASE`, en este orden:
   1. `reparaya.sql` (base proporcionada por el consultor)
   2. `ampliacion.sql` (ampliaciones del equipo)
2. Configuración de la conexión:
   - **Docker (local):** la conexión usa las variables de entorno definidas en `compose.yaml`.
   - **AWS:** crea `config/config.php` a partir de `config/config.example.php` con las credenciales del servidor. Este archivo **no se sube a Git**.
3. Abre `http://localhost:<WEB_PORT>/producto2`.

> Esta aplicación comparte la base de datos con WordPress (`wp_*`) y Laravel. **No elimines ni modifiques tablas que no sean de este producto**, y no uses `CREATE DATABASE` en ningún script.

---

## Base de datos

Se conserva la estructura inicial proporcionada y se amplía desde `database/ampliacion.sql`, sin eliminar ni alterar de forma incompatible lo original.

### Ampliaciones respecto a `reparaya.sql`

| Cambio | Tabla / campo | Motivo |
|---|---|---|
| Estado actual de la incidencia | `<tabla>.<campo estado>` | Pendiente, Asignada, En curso, Finalizada, Cancelada |
| Historial de cambios | `historial_estados` | Incidencia, estado anterior, estado nuevo, usuario responsable, fecha y hora, observación |
| Precio base por servicio | `<tabla>.precio_base` | Se reutilizará en el Producto 3 para calcular comisiones |
| Especialidades / categorías | `<tabla>` | `<justificación>` |

> Completa los datos entre `<>` cuando `ampliacion.sql` esté cerrado.

### Diagrama entidad-relación

`<insertar imagen en docs/>`

---

## Roles y permisos

El menú y las acciones cambian según el rol. Los permisos se comprueban **en el servidor** con `Auth`, no solo ocultando botones.

| Función | Administrador | Técnico | Cliente |
|---|:-:|:-:|:-:|
| Registro, login y perfil (nombre, email, contraseña) | ✅ | ✅ | ✅ |
| Crear solicitud de asistencia | ✅ (alta manual) | ❌ | ✅ |
| Ver «Mis avisos» y su historial | ❌ | ❌ | ✅ (propios) |
| Modificar o cancelar incidencias | ✅ (Pendiente/Asignada) | ❌ | Solo cancelar (> 48 h) |
| Asignar técnico | ✅ | ❌ | ❌ |
| Calendario general y filtro por estado | ✅ | ❌ | ❌ |
| Agenda propia | ❌ | ✅ | ❌ |
| Asignada → En curso → Finalizada | ❌ | ✅ (solo las suyas) | ❌ |
| Gestionar técnicos y tipos de servicio | ✅ | ❌ | ❌ |

---

## Ciclo de vida de una incidencia

```
              asignar técnico            iniciar               finalizar
 Pendiente ───────────────────▶ Asignada ───────▶ En curso ───────▶ Finalizada
     │                             │
     └──────────── cancelar ───────┴────▶ Cancelada
```

| Transición | Quién | Condición |
|---|---|---|
| (alta) → Pendiente | Cliente o Administrador | Estado inicial |
| Pendiente → Asignada | Administrador | Se produce **automáticamente** al asignar técnico |
| Asignada → En curso | Técnico asignado | |
| En curso → Finalizada | Técnico asignado | |
| Pendiente / Asignada → Cancelada | Cliente | Solo si faltan **más de 48 h** para la cita |
| Pendiente / Asignada → Cancelada | Administrador | |

- **Finalizada** y **Cancelada** son estados finales: no pueden volver a modificarse.
- Cada cambio queda registrado en `historial_estados` y se muestra ordenado cronológicamente en el detalle.
- `IncidenciaService` rechaza cualquier transición no prevista, aunque se intente saltando la interfaz.

---

## Reglas de negocio

- **Tipo de servicio de la solicitud:** urgente o estándar. Las estándar se piden con **48 h de antelación mínima**. Las urgentes pueden pedirse con menos y se identifican como tales en el calendario.
- **Datos obligatorios:** fecha solicitada, franja horaria, descripción de la avería, dirección, teléfono de contacto y tipo de servicio.
- **Código único** de incidencia, generado automáticamente.
- **Asignación obligatoria** de técnico para toda incidencia, la haya creado un cliente o el administrador.
- **Calendario:** vistas mensual, semanal y diaria; colores distintos para urgente y estándar; el estado se muestra también con texto, distintivo o icono (no solo con color); al pulsar un evento se ve el detalle completo.
- **Precio base** por tipo de servicio, almacenado en la BD.

---

## Seguridad

- Sentencias preparadas (PDO) en todos los modelos.
- Validación de datos en el servidor (`Validator`).
- Contraseñas con `password_hash()` y `password_verify()`.
- Control de acceso por rol mediante sesión (`Auth`, `Session`).
- Salida escapada en las vistas (protección frente a XSS).
- Sin credenciales reales en el repositorio ni en la documentación (`config/config.php` en `.gitignore`).

---

## Librerías externas

Se permiten librerías JavaScript o CSS para la interfaz, siempre que se documenten.

| Librería | Versión | Uso | Cómo se incluye |
|---|---|---|---|
| `<calendario, p. ej. FullCalendar>` | `<x.y.z>` | Calendario mensual/semanal/diario (`public/assets/js/calendario.js`) | `<CDN o local>` |
| `<CSS, p. ej. Bootstrap>` | `<x.y.z>` | `<uso>` | `<CDN o local>` |

---

## Usuarios de prueba

Solo datos **ficticios** para evaluar la aplicación. Las credenciales reales no se incluyen en el repositorio.

| Rol | Email | Contraseña |
|---|---|---|
| Administrador | `<admin@ejemplo.test>` | `<contraseña de prueba>` |
| Técnico | `<tecnico@ejemplo.test>` | `<contraseña de prueba>` |
| Cliente | `<cliente@ejemplo.test>` | `<contraseña de prueba>` |

---

## Reparto del equipo

| Rol | Responsable | Carpetas y CRUD |
|---|---|---|
| **A** | `<nombre>` | Infraestructura (`core/`, Docker, repo). **CRUD A** Usuarios (registro, login, perfil) y **CRUD D** Técnicos/Servicios |
| **B** | `<nombre>` | Base de datos (`database/`). **CRUD B** Incidencias (cliente), historial, máquina de estados (`IncidenciaService`) y front-end estático |
| **C** | `<nombre>` | **CRUD C** Gestión Admin, calendario, panel técnico y despliegue en AWS |

### Estado de los CRUD

| CRUD | Descripción | Responsable | Estado |
|---|---|---|---|
| A | Usuarios: registro, login y perfiles | A | ⬜ |
| B | Incidencias del cliente: solicitud, regla de 48 h, «Mis avisos», historial | B | ⬜ |
| C | Gestión Admin: panel, asignación, calendario, filtro, acciones del técnico | C | ⬜ |
| D | Maestro de técnicos y tipos de servicio | A | ⬜ |

---

## Flujo de trabajo con Git

- Un solo repositorio (raíz del proyecto). Rama por funcionalidad: `feature/<rol>-<tarea>`, p. ej. `feature/b-incidencia-service`.
- Cambios a `main` mediante **Pull Request** revisada por otro integrante.
- Commits pequeños y descriptivos, en una línea.
- Planificación en **Issues** y **Milestones** del repositorio:
  - Fase 1 · Fundamentos (25/10)
  - Fase 2 · CRUDs y funcionalidades (01/11)
  - Fase 3 · Integración y entrega (06/11)
- Punto de restauración del producto anterior: release `v1.0-producto1`.
- Nunca se suben `.env`, `producto2/config/config.php` ni credenciales.

---

## Documentación y entregables

| Entregable | Ubicación |
|---|---|
| Word técnico (arquitectura, BD ampliada, librerías, uso de IA) | `docs/` |
| Evidencia de Git (`git log --graph --oneline` o *Insights → Contributors*) | `docs/` |
| Mapa conceptual hecho a mano (MVC + ciclo de estados con roles) | `docs/` |
| Vídeo funcional en AWS (ciclo completo + transición rechazada) | Google Sites |
| Defensas individuales (≤ 2 min por CRUD) | Google Sites |
| ZIP con código y BD | Aula virtual |

Nombre del archivo de entrega: `Producto2(FP.064)_LosTresCoders`