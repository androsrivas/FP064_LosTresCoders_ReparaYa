# ReparaYa

Plataforma de gestión de reparaciones domésticas desarrollada en **PHP** y ejecutada en contenedores **Docker**. Proyecto de la asignatura *FP.064 - Desarrollo back-end con PHP, framework MVC y gestor de contenidos*.

El proyecto evoluciona a lo largo de cuatro productos, todos dentro del mismo repositorio:

| Producto | Contenido                                              | Carpeta                   | URL local      |
|----------|--------------------------------------------------------|---------------------------|----------------|
| 1        | Entorno Docker + AWS, Laravel y WordPress instalados   | raíz, `docker/`, `diagnostic/` | `/diagnostic` |
| 2        | Aplicación en PHP nativo con arquitectura MVC          | `producto2/`              | `/producto2`   |
| 3        | Aplicación en Laravel 13                               | `laravel/reparaya/`       | `/producto3`   |
| 4        | Web institucional en WordPress                         | `www/`                    | `/`            |

## Contenidos
1. [Arquitectura](#arquitectura)
2. [Requisitos previos](#requisitos-previos)
3. [Puesta en marcha](#puesta-en-marcha)
4. [Preparar cada aplicación](#preparar-cada-aplicacion)
5. [URLs de acceso](#urls-de-acceso)
6. [Comandos de acceso](#comandos-de-acceso)
7. [Despliegue en AWS](#despliegue-en-aws)
8. [Flujo de trabajo con Git](#flujo-de-trabajo-con-git)
9. [Resolución de problemas](#resolucion-de-problemas)

----

## Arquitectura

````
reparaya/
├── diagnostic/                # Página de pruebas (PHP, BD...)
├── docker/
│   └── web/
│       ├── Dockerfile         # Imagen PHP + Apache
│       └── 000-default.conf   # VirtualHost y Alias de Apache
├── producto2/                 # Producto 2: PHP nativo (MVC, sin framework)
│   ├── public/                # Único directorio accesible desde el navegador
│   ├── app/                   # Controllers, Models, Views, Services, Core
│   ├── config/                # Configuración (config.example.php)
│   ├── database/              # Scripts SQL
│   └── docs/                  # Documentación del producto
├── laravel/
│   └── reparaya/              # Producto 3: aplicación Laravel 13
├── www/                       # Web principal (/) y WordPress
├── .env                       # Variables de Docker Compose (NO se sube a Git)
├── .env.example               # Plantilla de variables
├── .gitignore
└── compose.yaml
````

### Servicios (`compose.yaml`)

| Servicio       | Contenedor      | Función                                        |
|----------------|-----------------|------------------------------------------------|
| `web`          | `reparaya_web`  | PHP + Apache (sirve la web y las aplicaciones) |
| `db`           | `reparaya_db`   | Base de datos MySQL                            |
| `phpmyadmin`   | `reparaya_pma`  | Gestión visual de la base de datos             |

Los tres comparten la red `reparaya_net`. La base de datos guarda los datos en el volumen `db_data`, de manera que **sobreviven** a un `docker compose down`.

Volúmenes del servicio `web`:

```yaml
volumes:
  - ./www:/var/www/html
  - ./producto2:/var/www/producto2
  - ./laravel/reparaya:/var/www/laravel/reparaya
  - ./diagnostic:/var/www/diagnostic
```

### Mapa de rutas Apache

| URL           | Carpeta dentro del contenedor           | Contenido                  |
|---------------|-----------------------------------------|----------------------------|
| `/`           | `/var/www/html`                         | Web principal / WordPress  |
| `/producto2`  | `/var/www/producto2/public`             | Aplicación PHP nativo      |
| `/producto3`  | `/var/www/laravel/reparaya/public`      | Aplicación Laravel         |
| `/diagnostic` | `/var/www/diagnostic`                   | Página de pruebas          |

Cada aplicación tiene un `Alias` en `docker/web/000-default.conf`, por ejemplo:

```apache
Alias /producto2 /var/www/producto2/public
<Directory /var/www/producto2/public>
    AllowOverride All
    Require all granted
</Directory>
```

> Solo se expone `public/`. El resto (`app`, `config`, `.env`, `vendor`...) queda fuera del alcance del navegador.

----

## Requisitos previos

- [Docker](https://docs.docker.com/get-docker/) y Docker Compose v2 (`docker compose`)
- [Git](https://git-scm.com/)

No hace falta instalar PHP, Composer ni MySQL en tu máquina: todo se ejecuta dentro de los contenedores. Los comandos `php` y `composer` se ejecutan **siempre dentro del contenedor** `web`, porque la versión de PHP local podría ser distinta a la de AWS.

----

## Puesta en marcha

### 1. Clonar el repositorio
```bash
git clone <URL_DEL_REPOSITORIO>
cd reparaya
```

### 2. Configurar las variables de Docker Compose
```bash
cp .env.example .env
```

Edita `.env` y revisa, como mínimo:

| Variable           | Descripción                                                            |
|--------------------|------------------------------------------------------------------------|
| `PHP_VERSION`      | Versión de PHP. **Debe coincidir con la de AWS** y cumplir los requisitos de la versión de Laravel elegida |
| `WEB_PORT`         | Puerto local de la web (ej. `8080`)                                    |
| `PMA_PORT`         | Puerto local de phpMyAdmin (ej. `8081`)                                |
| `DB_IMAGE`         | Imagen de la BBDD (ej. `mysql:8.0`; debe ser compatible con AWS)       |
| `DB_DATABASE`      | Nombre de la base de datos                                             |
| `DB_USERNAME`      | Usuario de la BBDD                                                     |
| `DB_PASSWORD`      | Contraseña del usuario                                                 |
| `DB_ROOT_PASSWORD` | Contraseña de `root`                                                   |
| `GROUP_CODE`, `STUDENT_NAME`, `APP_ENV` | Datos del grupo y entorno                         |

### 3. Arrancar los contenedores

```bash
docker compose up -d --build
```

Comprueba que los tres servicios están en marcha (`db` ha de estar *healthy*):

```bash
docker compose ps
```

----

## Preparar cada aplicación

### Producto 2 (`producto2/`)

Aplicación en PHP nativo. No usa Composer ni frameworks.

- Importa los scripts de `producto2/database/` (phpMyAdmin o línea de comandos).
- Dentro de Docker, la conexión a la BBDD se toma de las variables de entorno de `compose.yaml`.
- En AWS se crea `producto2/config/config.php` a partir de `config.example.php`. Este archivo **no se sube a Git**.

> Si Laravel comparte la base de datos, **no uses `php artisan migrate:fresh`**: borraría también las tablas del Producto 2.

### Producto 3 (`laravel/reparaya/`)

Laravel tiene su *propio* `.env`, independiente del de la raíz.

#### 1. Configurar Laravel

```bash
cp laravel/reparaya/.env.example laravel/reparaya/.env
```

Edita `laravel/reparaya/.env` y asegúrate de que tenga estos valores:

```dotenv
APP_URL=http://localhost:<WEB_PORT>/producto3   # ajusta el puerto a WEB_PORT

DB_CONNECTION=mysql
DB_HOST=db                                # nombre del servicio de Docker, NO localhost
DB_PORT=3306
DB_DATABASE=reparaya                      # el mismo que DB_DATABASE de la raíz
DB_USERNAME=...                           # el mismo que DB_USERNAME de la raíz
DB_PASSWORD=...                           # el mismo que DB_PASSWORD de la raíz
```

> Las variables `DB_*` y `APP_ENV` también las inyecta `compose.yaml` al contenedor `web`, y **tienen prioridad** sobre las del `.env` de Laravel. Mantenlas coherentes.

#### 2. Instalar dependencias y preparar la aplicación

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

Si quieres datos de prueba (cuando haya *seeders*):
```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && php artisan db:seed"
```

### WordPress (`www/`)

Se instala en la raíz web. Su `wp-config.php` contiene credenciales y **no se sube a Git**.

----

## URLs de acceso

Sustituye `<WEB_PORT>` y `<PMA_PORT>` por los valores de tu `.env`.

| Servicio                | URL                                        |
|-------------------------|--------------------------------------------|
| Web principal           | `http://localhost:<WEB_PORT>/`             |
| Producto 2 (PHP nativo) | `http://localhost:<WEB_PORT>/producto2`    |
| Producto 3 (Laravel)    | `http://localhost:<WEB_PORT>/producto3`    |
| Página de diagnóstico   | `http://localhost:<WEB_PORT>/diagnostic`   |
| phpMyAdmin              | `http://localhost:<PMA_PORT>`              |

Acceso a phpMyAdmin: usuario y contraseña de la BBDD (`DB_USERNAME` / `DB_PASSWORD`), o `root` con `DB_ROOT_PASSWORD`.

> La base de datos **no publica puertos** hacia tu equipo: solo es accesible desde los contenedores y a través de phpMyAdmin.

----

## Comandos de acceso

Para no repetir el prefijo, puedes abrir una terminal dentro del contenedor:

```bash
docker compose exec web bash
cd /var/www/laravel/reparaya      # para Laravel
cd /var/www/producto2             # para el Producto 2
```

| Acción                              | Comando                                                        |
|-------------------------------------|----------------------------------------------------------------|
| Arrancar contenedores               | `docker compose up -d`                                         |
| Arrancar reconstruyendo la imagen   | `docker compose up -d --build`                                 |
| Parar contenedores                  | `docker compose stop`                                          |
| Parar y eliminar contenedores       | `docker compose down`                                          |
| Parar y **borrar la BBDD**          | `docker compose down -v` ⚠️                                    |
| Ver logs de Apache/PHP              | `docker compose logs -f web`                                   |
| Ver logs de Laravel                 | `docker compose exec web tail -f /var/www/laravel/reparaya/storage/logs/laravel.log` |
| Crear un modelo con migración       | `php artisan make:model Client -m`                             |
| Crear un controlador de recurso     | `php artisan make:controller ClientController --resource`      |
| Ver las rutas                       | `php artisan route:list`                                       |
| Ejecutar migraciones                | `php artisan migrate`                                          |
| Rehacer la BBDD desde cero          | `php artisan migrate:fresh --seed` ⚠️                          |
| Limpiar cachés                      | `php artisan optimize:clear`                                   |

(Los comandos `php artisan` se ejecutan dentro del contenedor, en `/var/www/laravel/reparaya`.)

----

## Despliegue en AWS

El servidor AWS es el entorno de **preproducción**: tiene PHP, Apache y MySQL, pero **sin Composer** y sin permisos de administración. Todo se prepara en local y se transfiere por **SFTP**; el acceso al servidor es por **SSH**.

- No usar `sudo`, no instalar paquetes del sistema ni usar permisos `777`.
- No subir nunca `.env`, `config.php` local, `.git`, `node_modules`, logs ni cachés.
- La configuración del entorno AWS (credenciales reales) se crea directamente en el servidor.
- La versión de PHP **de Apache** puede ser distinta a la de **SSH**: comprobar ambas.

Para Laravel, las dependencias de producción se preparan en local:

```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && composer install --no-dev --optimize-autoloader"
```

Se transfiere el código con `vendor/`, `composer.lock` y `public/`. **No se usa `composer update`** en el despliegue. Después, para volver al entorno de desarrollo, ejecuta de nuevo `composer install`.

----

## Flujo de trabajo con Git

- **Nunca** se sube `.env`, `producto2/config/config.php`, `laravel/reparaya/.env` ni `laravel/reparaya/vendor`. Verifica que `.gitignore` los excluye.
- Solo hay **un** repositorio Git, en la raíz. No debe haber ninguna carpeta `.git` dentro de `laravel/reparaya` ni de `producto2`.
- Una rama por funcionalidad y *merge* a `main` cuando esté probada.
- Commits pequeños y descriptivos en una línea, ej. `Añadir CRUD de clientes`. Cada integrante debe tener configurado su `user.name` y `user.email`.
- Si añades una variable nueva en `.env`, añádela también en `.env.example` (sin el valor secreto).
- Después de cada `git pull`, ejecuta:

```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && composer install && php artisan migrate"
```

Entradas mínimas de `.gitignore`:

```gitignore
.env
producto2/config/config.php
laravel/reparaya/.env
laravel/reparaya/vendor/
laravel/reparaya/node_modules/
laravel/reparaya/storage/logs/*
www/wp-config.php
*.log
```

----

## Resolución de problemas

### `404` en todas las rutas menos en la página principal

Las aplicaciones cuelgan de un Alias de Apache (`/producto2`, `/producto3`). Comprueba:

1. Que el `.htaccess` de `public/` tenga, justo después de `RewriteEngine On`, el `RewriteBase` correspondiente:
   ```apache
   RewriteBase /producto3     # o /producto2
   ```
2. Que el `<Directory>` del Alias tenga `AllowOverride All` en `000-default.conf`.
3. Que `mod_rewrite` esté activado en el Dockerfile (`RUN a2enmod rewrite`).

### `404` también en `/producto2` o `/producto3`

Comprueba que la ruta existe dentro del contenedor:

```bash
docker compose exec web ls -la /var/www/producto2/public
docker compose exec web ls -la /var/www/laravel/reparaya/public
```

Deben salir `index.php` y `.htaccess`. Si no, revisa los volúmenes de `compose.yaml`.

### `tempnam(): file created in the system's temporary directory`

Faltan carpetas o permisos en `storage/` y `bootstrap/cache/`. Ejecuta el paso de permisos de la sección de Laravel. Si trabajas en Windows/Mac y el `chown` no tiene efecto:

```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && chmod -R 777 storage bootstrap/cache"
```

(Solo para desarrollo local. **Nunca en AWS.**)

### `Base table or view not found: 1146 Table '...cache' doesn't exist'`

Laravel usa por defecto `CACHE_STORE=database` y `SESSION_DRIVER=database`. Ejecuta `php artisan migrate`.

### `SQLSTATE[HY000] [2002] Connection refused` o `getaddrinfo failed`

- `DB_HOST` tiene que ser `db` (nombre del servicio), **no** `localhost`.
- Comprueba que `db` está **healthy**: `docker compose ps`.
- Después de cambiar el `.env` de Laravel: `docker compose exec web php artisan config:clear`.

### He cambiado la contraseña de la BBDD en el `.env` y no se aplica

MySQL solo lee las credenciales la **primera vez** que crea el volumen. Para recrearlo (⚠️ se borran todos los datos):

```bash
docker compose down -v
docker compose up -d --build
```

### El puerto ya está en uso

Cambia `WEB_PORT` o `PMA_PORT` en el `.env` de la raíz y vuelve a ejecutar `docker compose up -d`. Recuerda actualizar también `APP_URL` en el `.env` de Laravel.

### Apache avisa de «Could not reliably determine the server's fully qualified domain name»

Es solo un aviso. Para eliminarlo, añade `ServerName localhost` al VirtualHost.

----

## Equipo

| Nombre                   |
|--------------------------|
| Sandra Ridao Palahí      |
| Marc de la Riva Perez    |
| Andrea Ros Rivas          |

## Entregas del curso

| Producto | Descripción                                                | Fecha límite  |
|----------|------------------------------------------------------------|---------------|
| 1        | Desarrollo del prototipo                                   | 18/10/2026    |
| 2        | Aplicación de gestión de reparaciones                      | 08/11/2026    |
| 3        | Aplicación de gestión de reparaciones con Laravel          | 06/12/2026    |
| 4        | Web institucional con los servicios realizados (WordPress) | 23/12/2026    |