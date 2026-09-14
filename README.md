# Tutorial Laravel - ISAM 2024

## Descripción
El siguiente proyecto fue creado con fines educativos para ser utilizado por los alumnos de la materia Programación Orientada a Objetos del ISAM.

## Instalación del proyecto 💻

Para instalar el proyecto en un servidor local se deberán seguir los siguientes pasos

### Requisitos previos

* Tener un servidor web instalado en la máquina destino. El servidor utilizado durante el desarrollo fue **Apache**.
* Tener un servidor de base de datos instalado en la máquina destino. El servidor utilizado durante el desarrollo fue **MySQL**.
* Tener las credenciales de los distintos servidores a utilizar.
* Tener instalado **Laravel** (versión 11).
* Tener instaladas las siguientes herramientas de desarrollo:
    * Composer
    * NPM

### Pasos a seguir

1. Clonar el repositorio:

```sh
git clone https://github.com/profesorOrtiz/gestion-freelance.git
```

2. Instalar las dependencias de PHP necesarias:

```sh
composer install
```

Y las de NPM:

```sh
npm install
```

3. Crear una copia del archivo `.env.example`, renombrarlo como `.env` y modificar las credenciales necesarias:

```php
APP_NAME='Tutorial Laravel'
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tutorial_laravel
DB_USERNAME=root
DB_PASSWORD=
```

4. Generar la clave de Larvel para la aplicación

```sh
php artisan key:generate
```

5. Crear una base de datos llamada "gestion_freelance" en el servidor de base de datos MySQL de tu computadora.

6. Ejecutar las migraciones para crear las tablas en la base de datos

```sh
php artisan migrate
```

7. En el caso de utilizar Vite para compilar los archivos CSS y Javascript, ejecutar el siguiente comando en una nueva pestaña de la terminal para iniciar Vite.

```sh
npm run dev
```

8. Finalmente, ejecutar el servidor

```sh
php artisan serve
```
