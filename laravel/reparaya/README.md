# Producto 3 · ReparaYa en Laravel

Migración de la aplicación de gestión de reparaciones de **ReparaYa** del Producto 2 (PHP nativo) a **Laravel 13**. Los clientes particulares y las gestoras de fincas solicitan asistencias, y el administrador gestiona usuarios e incidencias.

- **Tecnología:** Laravel 13 (Eloquent, Blade, Form Requests, Policies), sin paquetes de autenticación externos.
- **Base de datos:** MySQL, compartida con WordPress y el Producto 2 (migraciones propias de Laravel).
- **URL local:** `http://localhost:<WEB_PORT>/producto3`.

> Para levantar el entorno Docker, las variables del `.env` raíz, las rutas de Apache, WordPress y el despliegue en AWS, consulta el [README principal](../../README.md). Este documento solo describe el Producto 3.

## Contenido

1. [Puesta en marcha](#puesta-en-marcha)
2. [Funcionalidades](#funcionalidades)
3. [Arquitectura](#arquitectura)
4. [Roles y permisos](#roles-y-permisos)
5. [Reglas de negocio](#reglas-de-negocio)
6. [Flujo de trabajo con Git](#flujo-de-trabajo-con-git)
---
## Puesta en marcha

Todos los comandos se ejecutan desde la raíz del repositorio (donde está `compose.yaml`), con los contenedores arrancados.

Laravel tiene su *propio* `.env`, independiente del de la raíz.

### 1. Configurar Laravel

```bash
cp laravel/reparaya/.env.example laravel/reparaya/.env
```

Edita `laravel/reparaya/.env` y asegúrate de que tenga estos valores:

```dotenv
APP_URL=http://localhost:<WEB_PORT>/producto3   # ajusta el puerto a WEB_PORT

DB_CONNECTION=mysql
DB_HOST=db                                # nombre del servicio de Docker, NO localhost
DB_PORT=3306
DB_DATABASE=...                           # el mismo que DB_DATABASE de la raíz
DB_USERNAME=...                           # el mismo que DB_USERNAME de la raíz
DB_PASSWORD=...                           # el mismo que DB_PASSWORD de la raíz
```

> Las variables `DB_*` y `APP_ENV` también las inyecta `compose.yaml` al contenedor `web`, y **tienen prioridad** sobre las del `.env` de Laravel. Mantenlas coherentes.

### 2. Instalar dependencias y preparar la aplicación

```bash
# Dependencias PHP
docker compose exec web bash -c "cd /var/www/laravel/reparaya && composer install"

# Clave de la aplicación
docker compose exec web bash -c "cd /var/www/laravel/reparaya && php artisan key:generate"

# Permisos de escritura
docker compose exec web bash -c "cd /var/www/laravel/reparaya && \
    mkdir -p storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R ug+rwX storage bootstrap/cache"

# Migraciones (crea las tablas, incluidas cache y sessions)
docker compose exec web bash -c "cd /var/www/laravel/reparaya && php artisan migrate"
```

### 3. Cargar datos iniciales

El catálogo (especialidades y zonas) es **imprescindible** para que la aplicación funcione:

```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && php artisan db:seed"
```

Para probar la aplicación, carga además los **datos de demostración**: usuarios de cada rol, gestorías, comunidades e incidencias en todos los estados. Se requiere haber cargado antes el catálogo.

```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && php artisan db:seed --class=DemoSeeder"
```

> Los *seeders* se pueden volver a ejecutar sin crear duplicados: buscan cada registro por su email o referencia y lo actualizan si ya existe.

### 4. Usuarios de demostración

Tras cargar `DemoSeeder`, puedes iniciar sesión en `/producto3/login` con cualquiera de estos usuarios:

| Rol | Email | Contraseña |
|----------------|---------------------------------------|---------------|
| Administrador | `admin@reparaya.test` | `password123` |
| Particular | `laura.garcia@reparaya.test` | `password123` |
| Técnico | `pedro.fontanero@reparaya.test` | `password123` |
| Gestora | `fincas.mediterraneo@reparaya.test` | `password123` |

Hay más usuarios de cada rol en `database/seeders/DemoUserSeeder.php` y `ManagementCompanySeeder.php`, todos con la misma contraseña.

> Estas credenciales son **solo para el entorno local**. No ejecutes `DemoSeeder` en AWS: crea usuarios con una contraseña conocida.

### 5. Ejecutar los tests

```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && php artisan test"
```

Los tests usan una base de datos SQLite en memoria, así que **no tocan** la base de datos compartida con WordPress y el Producto 2.

> `compose.yaml` inyecta `APP_ENV` y `DB_*` como variables de entorno del sistema, y Laravel las lee antes que las de `phpunit.xml`. Por eso `phpunit.xml` las declara con `<server ... force="true">` en lugar de `<env>`. Si se cambian a `<env>`, los tests se ejecutarían contra MySQL y fallarían con errores `419` (CSRF).

---

## Funcionalidades

**Incluidas:**

- Registro, inicio y cierre de sesión propios, con redirección al panel de cada rol (administrador, particular, gestora, técnico) y menú según el rol.
- Perfil propio: consulta, edición de datos y cambio de contraseña.
- Incidencias (particular y gestora): listado, alta, detalle y cancelación.
- Usuarios (administrador): alta y edición de administradores, y baja lógica y restauración de administradores y particulares (un administrador no puede darse de baja a sí mismo). Los particulares se dan de alta ellos mismos desde el registro.

**Pendientes:** CRUD de técnicos y especialidades, gestión de incidencias por el administrador, calendario, gestoras, zonas y liquidaciones. Los enlaces del menú aparecen automáticamente cuando existe la ruta (`Route::has()` en el layout).

---

## Arquitectura

Se sigue la estructura estándar de Laravel. Solo se listan las carpetas con código propio:

```
laravel/reparaya/
├── app/
│   ├── Enums/              # UserRole, IncidentStatus, Urgency (valores del dominio)
│   ├── Exceptions/Incident/# Excepciones de dominio de las incidencias
│   ├── Http/
│   │   ├── Controllers/    # Auth, Admin\UserController, IncidentController, ProfileController...
│   │   ├── Middleware/     # EnsureUserHasRole (alias "role")
│   │   └── Requests/       # Validación de cada formulario (Form Requests)
│   ├── Models/             # Modelos Eloquent y sus relaciones
│   ├── Policies/           # IncidentPolicy, UserPolicy (quién puede hacer qué)
│   └── Services/           # IncidentService, CommissionCalculator (reglas de negocio)
├── database/
│   ├── migrations/         # Esquema de la base de datos
│   └── seeders/            # Catálogo y datos de demostración
├── resources/views/        # Vistas Blade (layout, auth, incidents, admin, profile...)
├── routes/web.php          # Todas las rutas, agrupadas por rol
└── tests/Feature/          # Tests de usuarios y de cancelación de incidencias
```

Todas las peticiones pasan primero por los mismos filtros:

1. **Rutas** (`routes/web.php`): el middleware `auth` exige sesión y `role:...` comprueba el rol.
2. **Form Request**: valida los datos antes de llegar al controlador.

A partir de aquí, el código se organiza según la complejidad de cada parte:

- **Incidencias** (con reglas de negocio): `IncidentController` no contiene lógica, solo delega en `IncidentService`. El servicio aplica las reglas (antelación mínima, estados que permiten cancelar), comprueba la autorización con `IncidentPolicy` y guarda los cambios en una transacción. Si se incumple una regla, lanza una **excepción de dominio** (`app/Exceptions/Incident/`), que el controlador captura y muestra como mensaje de error.
- **Usuarios y perfil** (CRUD sin reglas de negocio): el controlador trabaja directamente con el modelo Eloquent, sin capa de servicio. En `Admin\UserController`, la autorización se comprueba en el propio controlador con `Gate::authorize()` y `UserPolicy` (por ejemplo, un administrador no puede darse de baja a sí mismo). `ProfileController` no necesita Policy: solo actúa sobre el usuario autenticado (`$request->user()`).

---

## Roles y permisos

Cada usuario tiene un único rol (`App\Enums\UserRole`). Al iniciar sesión se le redirige a su panel (`UserRole::homeRoute()`) y el menú muestra solo sus secciones (`UserRole::menu()`).

| Rol | Valor en BD | Panel | Puede hacer |
|---|---|---|---|
| Administrador | `admin` | `/admin` | Gestionar usuarios (alta y edición de administradores; baja y restauración de administradores y particulares) |
| Particular | `particular` | `/cliente` | Crear, consultar y cancelar sus propias incidencias |
| Gestora | `gestora` | `/gestora` | Crear, consultar y cancelar sus propias incidencias |
| Técnico | `tecnico` | `/tecnico` | Panel provisional (pendiente) |

Todos los roles pueden consultar y editar su perfil (`/perfil`). El registro público (`/registro`) crea siempre un **particular**: el rol no se acepta desde el formulario.

Los permisos se comprueban en tres niveles:

1. **Middleware `role`** (`EnsureUserHasRole`): limita cada grupo de rutas a uno o más roles, por ejemplo `role:particular,gestora`. Si el rol no coincide, devuelve `403`.
2. **Policies**: deciden sobre un registro concreto. `IncidentPolicy` solo permite ver o cancelar una incidencia a su propietario. `UserPolicy` limita qué usuarios puede editar, dar de baja o restaurar el administrador.
3. **Consultas filtradas**: los listados solo cargan los registros del usuario autenticado (por ejemplo, `where('client_id', Auth::id())` en las incidencias), así que nunca se muestran datos ajenos.

Los errores `403` se muestran con la vista `resources/views/errors/403.blade.php`, dentro del layout de la aplicación.

---

## Reglas de negocio

### Ciclo de vida de una incidencia

Estados (`App\Enums\IncidentStatus`): **Pendiente → Asignada → En curso → Finalizada**, o **Cancelada**. *Finalizada* y *Cancelada* son estados finales.

Cada cambio de estado se guarda en `incident_status_logs` (estado anterior, estado nuevo, usuario y nota), dentro de la misma transacción que el cambio.

### Alta de una incidencia

- Toda incidencia nueva empieza en *Pendiente* y recibe un localizador único con el formato `REP-AAAAMMDD-XXXXXX`.
- **Estándar**: el servicio se debe solicitar con al menos **48 horas** de antelación.
- **Urgente**: basta con que la fecha del servicio sea posterior al momento actual.
- Una **gestora** debe asociar la incidencia a una de **sus** comunidades. Un **particular** no puede asociar ninguna.
- La antelación se comprueba dos veces: en `StoreIncidentRequest`, para mostrar el error en el formulario, y en `IncidentService`, para que la regla se cumpla aunque el servicio se use desde otro punto.

### Cancelación por el cliente

- Solo el propietario de la incidencia puede cancelarla (`IncidentPolicy`).
- Solo se pueden cancelar las incidencias *Pendiente* o *Asignada*.
- Debe faltar **más de 48 horas** para la fecha del servicio.
- La incidencia se bloquea (`lockForUpdate`) durante la comprobación, para que dos peticiones simultáneas no la cancelen dos veces.

Las 48 horas están definidas una sola vez, en `IncidentService::MIN_HOURS_ADVANCE`. La validación y los mensajes de error también usan esta constante.

### Comisiones de las gestoras

`CommissionCalculator::amount()` es el único punto donde se calcula la comisión: precio base × porcentaje de la gestora. Trabaja en céntimos (enteros) para evitar errores de redondeo de coma flotante, y redondea el medio céntimo hacia arriba.

---

## Flujo de trabajo con Git

Las normas generales (qué no se sube, mensajes de commit, `.gitignore`) están en el [README principal](../../README.md#flujo-de-trabajo-con-git). En el Producto 3 se trabaja con una rama de integración:

```
feature/<rol>-<descripcion>  ──PR──▶  migration/laravel  ──PR (en cada entrega)──▶  main
```

- **`main`** solo recibe versiones entregables.
- **`migration/laravel`** es la rama de integración del Producto 3. Todas las ramas de trabajo salen de aquí y vuelven aquí con un *pull request*.
- **Ramas de trabajo**: `feature/` para funcionalidades nuevas y `fix/` para correcciones, seguidas de la letra del rol del integrante (`a`, `b`, `c`) y una descripción. Por ejemplo, `feature/a-crud-usuarios-admin`.
- Cada *pull request* se revisa antes de fusionarlo, y debe pasar los tests (`php artisan test`).
- `main` se actualiza con un *pull request* desde `migration/laravel` y un **merge commit** (no *squash*), para conservar la autoría de cada integrante.

Después de cambiar de rama o de hacer `git pull`, actualiza las dependencias y la base de datos:

```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && composer install && php artisan migrate"
```
