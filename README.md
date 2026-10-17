# ReparaYa

Aplicación web de gestión de reparaciones desarrollada con **Laravel 13** (PHP) y desplegada en contenedores **Docker**. Proyecto de la asignatura *FP.064 - Desarrollo back-end con PHP, framework MVC y gestor de contenidos*.

## Contenidos
1. [Arquitectura](#arquitectura)
2. [Requisitos previos](#requisitos-previos)
3. [Puesta en marcha](#puesta-en-marcha)
4. [URLs de acceso](#urls-de-acceso)
5. [Comandos de acceso](#comandos-de-acceso)
6. [Flujo de trabajo](#flujo-de-trabajo)
7. [Resolución de problemas](#resolucion-de-problemas)

----

## Arquitectura

````
reparaya/
├── diagnostic/                # Página de pruebas (PHP, BD...)
├── docker/
│   └── web/
|       └── Dockerfile         # Imagen PHP + Apache
│       └── 000-default.conf   # Configuración del VirtualHost de Apache
├── laravel/
│   └── reparaya/              # Aplicación Laravel 13 (todo el código)
├── www/
│   └── index.php              # Web principal (/)
├── .env                       # Variables de Docker Compose (NO se sube a Git)
├── .env.example               # Plantilla de variables
├── compose.yaml
````

### Servicios (`compose.yaml`)

| Servicio       | Contenedor      | Función                              |
|----------------|-----------------|--------------------------------------|
| `web`          | `reparaya_web`  | PHP + Apache (sirve web y Laravel)   |
| `db`           | `reparaya_db`   | Base de datos MySQL                  |
| `phpmyadmin`   | `reparaya_pma`  | Gestión visual de la base de datos   |

Los tres comparten la red 'reparaya_net'. La base de datos guarda los datos en el volumen 'db_data', de manera que **sobreviven** a un `docker compose down`.

### Mapa de rutas Apache

| URL           | Carpeta dentro del contenedor           | Contenido              |
|---------------|-----------------------------------------|------------------------|
| `/`           | `/var/www/html`                         | Web principal          |
| `/producto3`  | `/var/www/laravel/reparaya/public`      | Aplicación Laravel     |
| `/diagnostic` | `/var/www/diagnostic`                   | Página de pruebas      |

> Solo se expone `public/` de Laravel. El resto (`app`, `.env`, `vendor`...) queda fuera del alcance del navegador.

----

## Requisitos previos

- [Docker](https://docs.docker.com/get-docker/) y Docker Compose v2 (`docker compose`)
- [Git](https://git-scm.com/)

No hace falta instalar PHP, Composer ni MySQL en tu máquina: todo se ejecuta dentro de los contenedores.

----

## Puesta en marcha

### 1. Clonar el repositorio
```bash
git clone <URL_DEL_REPOSITORIO>
cd reparaya
```

### 2. Configruar las variables de Docker Compose
```bash
cp .env.example .env
``` 

Edita `.env` y revisa, como mínimo:

| Variable           | Descripción                                          |
|--------------------|------------------------------------------------------|
| `PHP_VERSION`      | Versión de PHP (**8.2 o superior** para Laravel 12)  |
| `WEB_PORT`         | Puerto local de la web (ej. `8080`)                  |
| `PMA_PORT`         | Puerto local de phpMyAdmin (ej. `8081`)              |
| `DB_IMAGE`         | Imagen de la BDBD (ej. `mysql:8.0`)                  |
| `DB_DATABASE`      | Nombre de la base de datos                           |
| `DB_USERNAME`      | Usuario de la BBDD                                   |
| `DB_PASSWORD`      | Contraseña del usuario                               |
| `DB_ROOT_PASSWORD` | Contraseña de `root`                                 |
| `GROUP_CODE`, `STUDENT_NAME`, `APP_ENV` | Datos del grupo y entorno       |

### 3. Arrancar los contenedores

```bash
docker compose up -d --build
```

Comprueba que los tres servicios están en marcha (`db` ha de estar *healthy*):

```bash
docker compose ps
```

### 4. Configurar Laravel

Laravel tiene su *propio* `.env`, independiente del de la raíz:

```bash
cp laravel/reparaya/.env.example laravel/reparaya/.env
```

Edita `laravel/reparaya/.env` y assegurate que tenga estos valores:

```dotenv
APP_URL=http://localhost:8080/producto3   # ajusta el puerto a WEB_PORT
 
DB_CONNECTION=mysql
DB_HOST=db                                # nombre del servicio de Docker, NO localhost
DB_PORT=3306
DB_DATABASE=reparaya                      # el mismo que DB_DATABASE de la raíz
DB_USERNAME=...                           # el mismo que DB_USERNAME de la raíz
DB_PASSWORD=...                           # el mismo que DB_PASSWORD de la raíz
```

> Las variables `DB_*` y `APP_ENV` también las inyecta `compose.yaml` al contenedor `web`, y **tienen prioridad** sobre las del `.env` de Laravel. Mantenlas coherentes.

### 5. Instalar dependencias y preparar la aplicación

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

# Migraciones (crea las tablas, incluídas cache y sessions)
docker compose exec web bash -c "cd /var/www/laravel/reparaya && php artisan migrate"
```

Si quieres datos de prueba (cuando haya *seeders*):
```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && php artisan db:seed"
```

### 6. Compruébalo

Abre `http://localhost:<WEB_PORT>/producto3` en el navegador.

----

## URLs de acceso

Sustituye `<WEB_PORT>` y `<PMA_PORT>` por los valores de tu `.env`.

| Servicio                | URL                                        |
|-------------------------|--------------------------------------------|
| Web principal           | `http://localhost:<WEB_PORT>/`             |
| Aplicación Laravel      | `http://localhost:<WEB_PORT>/producto3`    |
| Página de diagnóstico   | `http://localhost:<WEB_PORT>/diagnostic`   |
| phpMyAdmin              | `http://localhost:<PMA_PORT>`              |

Acceso a phpMyAdmin: usuario y contraseñas de la BBDD (`DB_USERNAME` / `DB_PASSWORD`), o `root` con `DB_ROOT_PASSWORD`.

----

## Comandos de acceso

Para no repetir el prefijo, puedes abrir una terminal dentro del contenedor:

```bash
docker compose exec web bash
cd /var/www/laravel/reparaya
```

Y, desde allí, trabajar con normalidad (`php artisan ...`, `composer ...`).

| Acción                              | Comando                                                        |
|-------------------------------------|----------------------------------------------------------------|
| Arrancar contenedores               | `docker compose up -d`                                         |
| Arrancar reconstruyendo la imagen   | `docker compose up -d --build`                                 |
| Parar contenedores                  | `docker compose stop`                                          |
| Parar y eliminar contenedores       | `docker compose down`                                          |
| Parar y **borrar la BBDD**          | `docker compose down -v` ⚠️                                    |
| Ver logs de Apache/PHP              | `docker compose logs -f web`                                   |
| Ver logs de Laravel                 | `docker compose exec web tail -f /var/www/laravel/reparaya/storage/logs/laravel.log` |
| Crear un modelo con migración       | `php artisan make:model Client -m`                           |
| Crear un controlador de recurso     | `php artisan make:controller ClientController --resource`    |
| Ver las rutas                       | `php artisan route:list`                                     |
| Ejecutar migraciones                | `php artisan migrate`                                        |
| Rehacer la BBDD desde zero          | `php artisan migrate:fresh --seed` ⚠️                        |
| Limpiar caches                      | `php artisan optimize:clear`                                 |

(Los comandos `php artisan` se deben ejecutar dentro del contenedor, en `/var/www/laravel/reparaya`).

----

## Flujo de trabajo con Git

- **Nunca** se sube `.env`, `laravel/reparaya/.env` ni `laravel/reparaya/vendor`. Verifica que `.gitignore` los excluye.
- Solo hay **un** repositorio Git, en la raíz. No debe haber ninguna carpeta `.git` dentr de `laravel/reparaya`.
- Después de cada `git pull`, ejecuta:

```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && composer install && php artisan migrate"
```

De este modo tendrás las dependencias y las tablas nuevas que haya añadido alguien del equipo.

- Si añades una variable nueva en `.env`, añádela también en `.env.example` (sin el valor secreto).
- Mensajes de commit claros y en una línea, ej. `Añadir CRUD de clientes`.

---- 

## Resolución de problemas

### `404` en todas las rutas menos en la página principal

Laravel cuelga del Alias `/producto3`. Comprueba que en `laravel/reparaya/public/.htaccess` haya, justo después de `RewriteEngine On`:

```apache
RewriteBase /producto3
```

Y que el módulo `mod_rewrite` esté activado en el Dockerfile (`RUN a2enmod rewrite`).

### `404` también en `/producto3`

Mira que la ruta dentro del contenedor exista:

```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya/public"
```

Deben salir `index.php` y `.htaccess`. Si no, revisa los volúmenes de `compose.yaml` (tiene que montar `./laravel` a `/var/www/laravel`).

### `tempnam(): file created in the system's temporary directory`

Faltan carpetas o permisos en `storage/` y `bootstrap/cache/`. Ejecuta el paso de permisos de la sección [Puesta en marcha](#5-instalar-dependencias-y-preparar-la-aplicacion). Si el proyecto es en Windows/Mac y el `chown` no tiene efecto:

```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && chmod -R 777 storage bootstrap/cache"
```

(Solo para desarrollo local)

### `Base table or view not found: 1146 Table '...cache' doesn't exist'`

Laravel 12 usa por defecto `CACHE_STORE=database` y `SESSION_DRIVER=database`. Ejecuta `php artisan migrate`.

### `SQLSTATE[HY000] [2002] Connection refused` o `getaddrinfo failed`

- `DB_HOST` tiene que ser `db` (nombre del servicio), **no** `localhost`.
- Comprueba que `db` está **healthy**: `docker compose ps`.
- Después de cambiar el `.env`: `docker compose exec web php artisan config:clear`.

### He cambiado la contraseña de la BBDD en el `.env` y no se aplica

MySQL solo lee las credenciales la **primera vez** que crea el volúmen. Para recrearlo (⚠️ se borran los datos):

```bash
docker compose down -v
docker compose up -d --build
```

### El puerto ya está en uso

Cambia `WEB_PORT` o `PMA_PORT` en el `.env` de la raíz y vuelve a ejecutar `docker compose up -d`. Recuerda actualizar también `APP_URL` en el `.env` de Laravel.

### Apache avisa de 'Could not reliably determine the server's fully qualified domain name'

Es solo un aviso. Para eliminarlo, añade `ServerName localhost` al VirtualHost.

----

## Equipo

| Nombre                   |
|--------------------------|
| Sandra Ridao Palahí      |
| Marc de la Riva Perez    |
| Andrea Rs Rivas          |

## Entregas del curso
 
| Producto | Descripción                                                | Fecha límite  |
|----------|------------------------------------------------------------|---------------|
| 1        | Desarrollo del prototipo                                   | 18/10/2026    |
| 2        | Aplicación de gestión de reparaciones                      | 08/11/2026    |
| 3        | Aplicación de gestión de reparaciones con Laravel          | 06/12/2026    |
| 4        | Web institucional con los servicios realizados (WordPress) | 23/12/2026    |
