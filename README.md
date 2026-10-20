# ReparaYa

Plataforma de gestión de reparaciones domésticas desarrollada en **PHP** y ejecutada en contenedores **Docker**. Proyecto de la asignatura *FP.064 - Desarrollo back-end con PHP, framework MVC y gestor de contenidos*.

El proyecto evoluciona a lo largo de cuatro productos, todos dentro del mismo repositorio:

| Producto | Contenido                                              | Carpeta                        | URL local      |
|----------|--------------------------------------------------------|--------------------------------|----------------|
| 1        | Entorno Docker + AWS, Laravel y WordPress instalados   | raíz, `docker/`, `diagnostic/` | `/diagnostic`  |
| 2        | Aplicación en PHP nativo con arquitectura MVC          | `producto2/`                   | `/producto2`   |
| 3        | Aplicación en Laravel 13                               | `laravel/reparaya/`            | `/producto3`   |
| 4        | Web institucional en WordPress                         | `www/`                         | `/`            |

## Contenidos
1. [Arquitectura](#arquitectura)
2. [Requisitos previos](#requisitos-previos)
3. [Puesta en marcha](#puesta-en-marcha)
4. [Preparar cada aplicación](#preparar-cada-aplicación)
5. [URLs de acceso](#urls-de-acceso)
6. [Comandos de acceso](#comandos-de-acceso)
7. [Despliegue en AWS](#despliegue-en-aws)
8. [Flujo de trabajo con Git](#flujo-de-trabajo-con-git)
9. [Resolución de problemas](#resolución-de-problemas)

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
├── www/                       # Raíz web (/): WordPress (no se versiona, ver más abajo)
├── .env                       # Variables de Docker Compose (NO se sube a Git)
├── .env.example               # Plantilla de variables
├── .gitignore
└── compose.yaml
````

### Servicios (`compose.yaml`)

| Servicio       | Contenedor      | Función                                        |
|----------------|-----------------|------------------------------------------------|
| `web`          | `reparaya_web`  | PHP + Apache (sirve la web y las aplicaciones, **incluido WordPress**) |
| `db`           | `reparaya_db`   | Base de datos MySQL                            |
| `phpmyadmin`   | `reparaya_pma`  | Gestión visual de la base de datos             |

Los tres comparten la red `reparaya_net`. La base de datos guarda los datos en el volumen `db_data`, de manera que **sobreviven** a un `docker compose down`.

> **WordPress no tiene contenedor propio.** Se ejecuta dentro del servicio `web`, con la misma versión de PHP y las mismas extensiones que el servidor de AWS. No se debe añadir un servicio `wordpress` con la imagen oficial: usaría un PHP distinto, generaría su propio `wp-config.php` con otras credenciales y ocuparía un puerto adicional.

Volúmenes del servicio `web`:

```yaml
volumes:
  - ./www:/var/www/html
  - ./producto2:/var/www/producto2
  - ./laravel/reparaya:/var/www/laravel/reparaya
  - ./diagnostic:/var/www/diagnostic
```

### Base de datos única y compartida

El servidor de AWS ofrece **una sola base de datos**, y el usuario asignado puede crear tablas pero **no crear bases de datos**. Para que el entorno local sea equivalente, el proyecto trabaja con **una única base de datos (`DB_DATABASE`) compartida** por las tres aplicaciones. La convivencia se resuelve con los nombres de las tablas:

| Aplicación  | Tablas                                                                 | Cómo se crean                          |
|-------------|------------------------------------------------------------------------|----------------------------------------|
| WordPress   | Todas con el prefijo **`wp_`**                                         | Asistente de instalación de WordPress  |
| Laravel     | Las que trae por defecto (`users`, `migrations`, `sessions`, `cache`, `jobs`...) y las nuevas, con las convenciones de Laravel | `php artisan migrate` |
| Producto 2  | Las definidas en `producto2/database/`                                 | Scripts SQL                            |

Consecuencias prácticas:

- WordPress se instala **siempre con el prefijo `wp_`** (ver [WordPress](#wordpress-www)).
- **No se usa `php artisan migrate:fresh`** (ni `db:wipe`): borran *todas* las tablas de la base de datos, incluidas las de WordPress y las del Producto 2.
- Si el Producto 2 y Laravel definen tablas con el mismo nombre, hay que acordarlo en equipo antes de ejecutar las migraciones.
- La base de datos se crea sola al iniciar el volumen `db_data`, con el usuario `DB_USERNAME`, que ya tiene todos los privilegios sobre ella. **No hace falta crear ninguna otra base de datos.**

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

Los `Alias` se resuelven **antes** que el `.htaccess` de WordPress en `/`, así que WordPress no interfiere con `/producto2`, `/producto3` ni `/diagnostic`. A cambio, **no se pueden crear en WordPress páginas con los slugs** `producto2`, `producto3` o `diagnostic`: Apache las taparía.

----

## Requisitos previos

- [Docker](https://docs.docker.com/get-docker/) y Docker Compose v2 (`docker compose`)
- [Git](https://git-scm.com/)

No hace falta instalar PHP, Composer ni MySQL en tu máquina: todo se ejecuta dentro de los contenedores. Los comandos `php` y `composer` se ejecutan **siempre dentro del contenedor** `web`, porque la versión de PHP local podría ser distinta a la de AWS.

> **Windows con Git Bash:** convierte automáticamente las rutas que empiezan por `/` (por ejemplo `/var/www/html`) en rutas de Windows, lo que rompe los comandos con `docker compose exec`. Usa `sh -c "..."` / `bash -c "..."` con la ruta entre comillas, antepón `MSYS_NO_PATHCONV=1` o ejecuta `export MSYS_NO_PATHCONV=1` en la sesión. En PowerShell no ocurre.

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
| `PHP_VERSION`      | Versión de PHP. **Debe coincidir con la de AWS** (Apache: 8.3.6) y cumplir los requisitos de Laravel y WordPress. Ej.: `8.3` |
| `WEB_PORT`         | Puerto local de la web (ej. `8080`)                                    |
| `PMA_PORT`         | Puerto local de phpMyAdmin (ej. `8081`). Debe ser distinto de `WEB_PORT` |
| `DB_IMAGE`         | Imagen de la BBDD (ej. `mysql:8.0`; debe ser compatible con AWS)       |
| `DB_DATABASE`      | Nombre de la **única** base de datos, compartida por WordPress, Producto 2 y Laravel |
| `DB_USERNAME`      | Usuario de la BBDD                                                     |
| `DB_PASSWORD`      | Contraseña del usuario                                                 |
| `DB_ROOT_PASSWORD` | Contraseña de `root`. **Defínela antes del primer arranque**: se aplica solo al crear el volumen |
| `GROUP_CODE`, `STUDENT_NAME`, `APP_ENV` | Datos del grupo y entorno                         |

### 3. Arrancar los contenedores

```bash
docker compose up -d --build
```

Comprueba que los tres servicios están en marcha (`db` ha de estar *healthy*):

```bash
docker compose ps
```

Deben aparecer exactamente `reparaya_web`, `reparaya_db` y `reparaya_pma`.

----

## Preparar cada aplicación

### Producto 2 (`producto2/`)

Aplicación en PHP nativo. No usa Composer ni frameworks.

- Importa los scripts de `producto2/database/` en la base de datos `DB_DATABASE` (phpMyAdmin o línea de comandos).
- Dentro de Docker, la conexión a la BBDD se toma de las variables de entorno de `compose.yaml`.
- En AWS se crea `producto2/config/config.php` a partir de `config.example.php`. Este archivo **no se sube a Git**.

> Si Laravel comparte la base de datos, **no uses `php artisan migrate:fresh`**: borraría también las tablas del Producto 2 y de WordPress.

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
DB_DATABASE=...                           # el mismo que DB_DATABASE de la raíz
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

WordPress se instala en la raíz web (`www/` → `/var/www/html`) y se ejecuta en el contenedor `web`. **La carpeta `www/` no se versiona en Git**: cada integrante descarga WordPress en local.

| Dato                    | Valor                                                                      |
|-------------------------|----------------------------------------------------------------------------|
| Versión de WordPress    | `<indicar la versión instalada>` (se ve en `www/wp-includes/version.php`)   |
| PHP / extensiones       | Las de la imagen `docker/web/Dockerfile` (`mysqli`, `gd`, `zip`, `mbstring`, `intl`, `exif`...) |
| Base de datos           | `DB_DATABASE` (la **misma** que usan Producto 2 y Laravel)                 |
| Servidor de BBDD        | `db` (nombre del servicio, **no** `localhost`)                             |
| Prefijo de tablas       | `wp_`                                                                      |

La versión elegida debe ser compatible con PHP 8.3 y con la versión de MySQL/MariaDB de AWS. Consulta [wordpress.org/download/releases](https://wordpress.org/download/releases/) y descarga siempre una versión **concreta** (no `latest.zip`), para que local y AWS lleven la misma.

#### 1. Descargar WordPress en `www/`

Desde la raíz del proyecto (ajusta la versión):

```bash
cd www
curl -L -O https://wordpress.org/wordpress-<VERSION>.zip
unzip wordpress-<VERSION>.zip
mv wordpress/* .
rmdir wordpress
rm wordpress-<VERSION>.zip
cd ..
```

Si `unzip` no está disponible, descomprime el zip con el explorador de archivos y mueve el contenido de la carpeta `wordpress` a `www/`. En Windows (PowerShell) se puede usar `tar -xf` en lugar de `unzip`.

Comprueba que el contenedor ve los archivos:

```bash
docker compose exec web sh -c "ls /var/www/html"
```

#### 2. Comprobar que la base de datos está lista

No hay que crear ninguna base de datos: `DB_DATABASE` ya existe y `DB_USERNAME` tiene privilegios sobre ella. Comprueba únicamente que **no quedan tablas `wp_*` de una instalación anterior** (phpMyAdmin → `DB_DATABASE`). Si las hay y quieres empezar de cero, elimina **solo las que empiezan por `wp_`**, sin tocar las de Laravel ni las del Producto 2.

Si el usuario de la BBDD no tuviera privilegios (por ejemplo, porque el volumen `db_data` se creó con otros valores), entra en phpMyAdmin como `root` / `DB_ROOT_PASSWORD`, pestaña **SQL**:

```sql
GRANT ALL PRIVILEGES ON `<DB_DATABASE>`.* TO '<DB_USERNAME>'@'%';
FLUSH PRIVILEGES;
```

Para reproducir en local las restricciones de AWS (crear tablas sí, crear bases de datos no), se puede sustituir `ALL PRIVILEGES` por:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX, REFERENCES
ON `<DB_DATABASE>`.* TO '<DB_USERNAME>'@'%';
FLUSH PRIVILEGES;
```

#### 3. Ejecutar el asistente de instalación

Abre `http://localhost:<WEB_PORT>/` (como todavía no existe `wp-config.php`, aparece el asistente) e introduce:

| Campo                        | Valor                              |
|------------------------------|------------------------------------|
| Nombre de la base de datos   | el valor de `DB_DATABASE`          |
| Usuario / Contraseña         | `DB_USERNAME` / `DB_PASSWORD`      |
| Servidor de la base de datos | `db`                               |
| Prefijo de tablas            | `wp_`                              |

Después, indica título del sitio, usuario administrador, contraseña y correo. **Guarda la contraseña del administrador en un gestor de contraseñas**: el correo de recuperación no funciona en Docker (no hay servidor de correo).

El asistente genera `www/wp-config.php` con las credenciales. Este archivo **no se sube a Git** ni se incluye en ninguna entrega.

Si WordPress ya estuvo instalado con otra URL (por ejemplo, otro puerto) y las tablas `wp_*` se conservaron, la web redirigirá a la URL antigua. Fuerza la correcta en `www/wp-config.php`, antes de la línea «That's all, stop editing!»:

```php
define('WP_HOME',    'http://localhost:<WEB_PORT>');
define('WP_SITEURL', 'http://localhost:<WEB_PORT>');
```

#### 4. Comprobación de la instalación (Producto 1, punto 13)

1. Acceder a `http://localhost:<WEB_PORT>/` y a `http://localhost:<WEB_PORT>/wp-admin`.
2. Crear la página **«Entorno técnico de ReparaYa»** con el nombre y el código del grupo.
3. Crear una entrada de prueba.
4. Reiniciar los contenedores y comprobar que la página y la entrada siguen existiendo:

```bash
docker compose down        # SIN -v: -v borraría el volumen db_data
docker compose up -d
```

La persistencia depende de dos cosas: el volumen `db_data` (contenido) y el *bind mount* `./www` (archivos).

#### Recuperar la contraseña del administrador

Si pierdes la contraseña, restablécela en phpMyAdmin (base `DB_DATABASE`, pestaña **SQL**):

```sql
UPDATE wp_users SET user_pass = MD5('NuevaContraseña') WHERE ID = 1;
```

WordPress acepta MD5 y la vuelve a guardar con su hash seguro en el primer inicio de sesión. Fíjate en el prefijo: la tabla de WordPress es `wp_users`, distinta de la tabla `users` de Laravel.

----

## URLs de acceso

Sustituye `<WEB_PORT>` y `<PMA_PORT>` por los valores de tu `.env`.

| Servicio                | URL                                        |
|-------------------------|--------------------------------------------|
| Web principal (WordPress) | `http://localhost:<WEB_PORT>/`           |
| Administración de WordPress | `http://localhost:<WEB_PORT>/wp-admin` |
| Producto 2 (PHP nativo) | `http://localhost:<WEB_PORT>/producto2`    |
| Producto 3 (Laravel)    | `http://localhost:<WEB_PORT>/producto3`    |
| Página de diagnóstico   | `http://localhost:<WEB_PORT>/diagnostic`   |
| phpMyAdmin              | `http://localhost:<PMA_PORT>`              |

Acceso a phpMyAdmin: usuario y contraseña de la BBDD (`DB_USERNAME` / `DB_PASSWORD`). Solo se necesita `root` con `DB_ROOT_PASSWORD` para modificar privilegios.

> La base de datos **no publica puertos** hacia tu equipo: solo es accesible desde los contenedores y a través de phpMyAdmin.

----

## Comandos de acceso

Para no repetir el prefijo, puedes abrir una terminal dentro del contenedor:

```bash
docker compose exec web bash
cd /var/www/laravel/reparaya      # para Laravel
cd /var/www/producto2             # para el Producto 2
cd /var/www/html                  # para WordPress
```

| Acción                              | Comando                                                        |
|-------------------------------------|----------------------------------------------------------------|
| Arrancar contenedores               | `docker compose up -d`                                         |
| Arrancar reconstruyendo la imagen   | `docker compose up -d --build`                                 |
| Parar contenedores                  | `docker compose stop`                                          |
| Parar y eliminar contenedores       | `docker compose down`                                          |
| Reiniciar eliminando contenedores huérfanos | `docker compose down --remove-orphans`                 |
| Parar y **borrar la BBDD**          | `docker compose down -v` ⚠️                                    |
| Ver contenedores y puertos          | `docker compose ps`                                            |
| Ver logs de Apache/PHP              | `docker compose logs -f web`                                   |
| Ver logs de Laravel                 | `docker compose exec web tail -f /var/www/laravel/reparaya/storage/logs/laravel.log` |
| Entrar al cliente MySQL como root   | `docker compose exec db mysql -uroot -p`                       |
| Ver la versión de PHP del contenedor| `docker compose exec web php -v`                               |
| Ver las extensiones de PHP          | `docker compose exec web php -m`                               |
| Crear un modelo con migración       | `php artisan make:model Client -m`                             |
| Crear un controlador de recurso     | `php artisan make:controller ClientController --resource`      |
| Ver las rutas                       | `php artisan route:list`                                       |
| Ejecutar migraciones                | `php artisan migrate`                                          |
| Deshacer la última migración        | `php artisan migrate:rollback`                                 |
| Rehacer la BBDD desde cero          | **No usar** `migrate:fresh` ⛔: borra también las tablas `wp_*` y las del Producto 2 |
| Limpiar cachés                      | `php artisan optimize:clear`                                   |

(Los comandos `php artisan` se ejecutan dentro del contenedor, en `/var/www/laravel/reparaya`.)

----

## Despliegue en AWS

El servidor AWS es el entorno de **preproducción**: tiene PHP, Apache y MySQL, pero **sin Composer** y sin permisos de administración. Todo se prepara en local y se transfiere por **SFTP**; el acceso al servidor es por **SSH**.

- No usar `sudo`, no instalar paquetes del sistema ni usar permisos `777`.
- No subir nunca `.env`, `config.php` local, `wp-config.php` local, `.git`, `node_modules`, logs ni cachés.
- La configuración del entorno AWS (credenciales reales) se crea directamente en el servidor.
- La versión de PHP **de Apache** puede ser distinta a la de **SSH**: comprobar ambas.
- **Base de datos:** en AWS hay una única base de datos y el usuario no puede crear otras. Las tres aplicaciones comparten esa base de datos con la misma separación por tablas que en local (prefijo `wp_` para WordPress). Al importar datos desde local, importa **solo tablas** (sin `CREATE DATABASE` ni `USE` de otra base) y nunca ejecutes `migrate:fresh` ni elimines tablas que no sean tuyas.

### Laravel

Las dependencias de producción se preparan en local:

```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && composer install --no-dev --optimize-autoloader"
```

Se transfiere el código con `vendor/`, `composer.lock` y `public/`. **No se usa `composer update`** en el despliegue. Después, para volver al entorno de desarrollo, ejecuta de nuevo `composer install`.

En el servidor, el `.env` de Laravel se crea a mano con `APP_ENV=production`, `APP_DEBUG=false` y las credenciales de la base de datos de AWS. Las migraciones se ejecutan por SSH con la versión de PHP compatible: `php artisan migrate --force`.

### WordPress

WordPress queda instalado en la raíz del dominio (o en la ubicación que indique el consultor). Puntos a tener en cuenta en la migración:

- Transferir los archivos de WordPress por SFTP (la misma versión que en local).
- Crear el `wp-config.php` directamente en el servidor, con las credenciales de la base de datos de AWS y `$table_prefix = 'wp_';`. No se copia el de local.
- Importar a la base de datos de AWS **solo las tablas `wp_*`** y cambiar las URL (`home` y `siteurl`) por las del dominio. **No hacer un reemplazo de texto sobre el SQL**: WordPress guarda datos serializados y se corrompen. Usar un plugin de migración o una herramienta que los gestione.
- Guardar de nuevo los enlaces permanentes (*Ajustes → Enlaces permanentes*) para regenerar el `.htaccess`.
- Comprobar que los permisos de `wp-content/` permiten subir archivos, sin recurrir a `777`.

----

## Flujo de trabajo con Git

- **Nunca** se sube `.env`, `producto2/config/config.php`, `laravel/reparaya/.env`, `laravel/reparaya/vendor` ni `www/wp-config.php`. Verifica que `.gitignore` los excluye.
- **WordPress no se versiona.** El núcleo, los temas y plugins descargados y las subidas se bajan en local (ver [WordPress](#wordpress-www)). Solo se versionarán los temas o plugins propios que se creen en el Producto 4.
- Solo hay **un** repositorio Git, en la raíz. No debe haber ninguna carpeta `.git` dentro de `laravel/reparaya` ni de `producto2`.
- Una rama por funcionalidad y *merge* a `main` cuando esté probada.
- Commits pequeños y descriptivos en una línea, ej. `Añadir CRUD de clientes`. Cada integrante debe tener configurado su `user.name` y `user.email`.
- Si añades una variable nueva en `.env`, añádela también en `.env.example` (sin el valor secreto).
- Antes de cada commit, comprueba qué se añadiría con `git add -n .`.
- Después de cada `git pull`, ejecuta:

```bash
docker compose exec web bash -c "cd /var/www/laravel/reparaya && composer install && php artisan migrate"
```

Entradas mínimas de `.gitignore`:

```gitignore
# Secretos y configuración local
.env
producto2/config/config.php
laravel/reparaya/.env
diagnostic/diagnostico.config.aws.php

# Laravel
laravel/reparaya/vendor/
laravel/reparaya/node_modules/
laravel/reparaya/storage/logs/*
*.log

# WordPress: no se versiona (se descarga en local)
www/*
```

Cuando se cree un tema propio en el Producto 4, se puede versionar solo ese tema añadiendo excepciones:

```gitignore
www/*
!www/wp-content/
www/wp-content/*
!www/wp-content/themes/
www/wp-content/themes/*
!www/wp-content/themes/<tema-reparaya>/
```

### Si ya has añadido WordPress a Git

Si has hecho `git add` de archivos de `www/` pero todavía **no has hecho commit**:

```bash
git restore --staged www/
```

Si un archivo ya estaba versionado antes, `.gitignore` no lo afecta: hay que dejar de seguirlo (sin borrarlo del disco):

```bash
git rm --cached www/index.php
```

Si ya has hecho commit (y push), haz lo mismo con `git rm -r --cached ...` en un commit nuevo. **Siempre con `--cached`**: sin él se borrarían los archivos de WordPress del disco. Si se subió `wp-config.php` o un `.env`, cambia esas contraseñas.

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

Deben salir `index.php` y `.htaccess`. Si el contenedor ve la carpeta vacía, revisa qué carpeta del PC está montando realmente:

```bash
docker inspect reparaya_web --format '{{range .Mounts}}{{.Source}} -> {{.Destination}}{{println}}{{end}}'
```

El origen de cada línea debe ser la carpeta correcta (por ejemplo, `.../laravel/reparaya -> /var/www/laravel/reparaya`). Si no lo es, corrige los volúmenes de `compose.yaml` y recrea el contenedor con `docker compose up -d --force-recreate web`: un simple `restart` no aplica los cambios de volúmenes.

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

MySQL solo lee las credenciales la **primera vez** que crea el volumen. Para recrearlo (⚠️ se borran todos los datos, incluidas las tablas de WordPress, que habrá que reinstalar):

```bash
docker compose down -v
docker compose up -d --build
```

### El puerto ya está en uso

`Bind for 0.0.0.0:XXXX failed: port is already allocated`. Comprueba que `WEB_PORT` y `PMA_PORT` son distintos entre sí y que ningún otro programa o contenedor usa esos puertos (`docker ps`). Cambia `WEB_PORT` o `PMA_PORT` en el `.env` de la raíz y vuelve a ejecutar `docker compose up -d`. Recuerda actualizar también `APP_URL` en el `.env` de Laravel.

Si el error menciona un contenedor `reparaya_wp`, es un servicio `wordpress` sobrante en el `compose.yaml`. WordPress no lleva contenedor propio: elimina ese bloque y ejecuta:

```bash
docker compose down --remove-orphans
docker rm -f reparaya_wp
docker compose up -d
```

(Sin `-v`, para conservar la base de datos.) Si ese contenedor llegó a arrancar y generó su propia instalación, borra también `www/wp-config.php`, `www/wp-config-docker.php` y `www/.htaccess` si se crearon con credenciales incorrectas.

### Apache avisa de «Could not reliably determine the server's fully qualified domain name»

Es solo un aviso. Para eliminarlo, añade `ServerName localhost` al VirtualHost.

### WordPress: `Error establishing a database connection`

Si el mensaje incluye `mysqli_real_connect(): (HY000/2002): No such file or directory`, WordPress está intentando conectar con `localhost` por socket: el host de la base de datos debe ser `db`.

Comprueba, por este orden:

1. En `www/wp-config.php`: `DB_HOST` debe ser `db`, `DB_NAME` debe ser el valor de `DB_DATABASE`, y `DB_USER` / `DB_PASSWORD` deben coincidir con `DB_USERNAME` / `DB_PASSWORD` del `.env`.
2. Que el usuario tiene privilegios sobre la base de datos (`SHOW GRANTS FOR '<usuario>'@'%';` como `root`).
3. Prueba de conexión desde el contenedor `web`:
   ```bash
   docker compose exec web php -r "var_dump(new mysqli('db','<usuario>','<contraseña>','<DB_DATABASE>'));"
   ```
4. Si `wp-config.php` tiene credenciales incorrectas, bórralo (`rm www/wp-config.php`) y vuelve a ejecutar el asistente.

### WordPress redirige a otro puerto o URL

Las tablas `wp_*` de una instalación anterior guardan la URL antigua. Fuerza la correcta con `WP_HOME` y `WP_SITEURL` en `wp-config.php` (ver [WordPress](#wordpress-www)).

### phpMyAdmin: «No tienes los privilegios» al crear una base de datos

Es el comportamiento esperado: el usuario de la aplicación no puede crear bases de datos, igual que en AWS. El proyecto utiliza una única base de datos compartida (`DB_DATABASE`), así que no hace falta crear ninguna. Para modificar privilegios, entra con `root` y `DB_ROOT_PASSWORD`.

### No tengo contraseña de `root` en el `.env`

`MYSQL_ROOT_PASSWORD` solo se aplica al crear el volumen `db_data`. Añade `DB_ROOT_PASSWORD` al `.env` y recrea el volumen (⚠️ borra los datos):

```bash
docker compose down -v
docker compose up -d --build
```

Si el volumen ya tiene datos que quieres conservar, consulta antes con el equipo.

### WordPress: `404 Not Found` en `/` o en las páginas

- Comprueba que `www/` contiene los archivos de WordPress (`index.php`, `wp-admin/`, `wp-content/`...) y que el contenedor los ve (`docker compose exec web sh -c "ls /var/www/html"`).
- Que el puerto que abres es `WEB_PORT` y lo sirve `reparaya_web` (`docker compose ps`).
- Si falla solo con los enlaces permanentes, mueve temporalmente `www/.htaccess` y guarda de nuevo los enlaces permanentes desde *Ajustes*.
- Que no hay páginas de WordPress con los slugs `producto2`, `producto3` o `diagnostic`.

### Git Bash: `ls: cannot access 'C:/Program Files/Git/var/www/html'`

Git Bash convierte las rutas que empiezan por `/`. Ejecuta `export MSYS_NO_PATHCONV=1`, usa `sh -c "..."` o cambia a PowerShell.

----

## Equipo

| Nombre                   |
|--------------------------|
| Sandra Ridao Palahí      |
| Marc de la Riva Perez    |
| Andrea Ros Rivas         |

## Entregas del curso

| Producto | Descripción                                                | Fecha límite  |
|----------|------------------------------------------------------------|---------------|
| 1        | Desarrollo del prototipo                                   | 18/10/2026    |
| 2        | Aplicación de gestión de reparaciones                      | 08/11/2026    |
| 3        | Aplicación de gestión de reparaciones con Laravel          | 06/12/2026    |
| 4        | Web institucional con los servicios realizados (WordPress) | 23/12/2026    |