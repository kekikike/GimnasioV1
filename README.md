<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>


# GimnasioV1 - Guía de Configuración y Arranque

Esta guía contiene los pasos necesarios para instalar las dependencias, establecer el entorno y ejecutar el proyecto de forma local.

## Prerrequisitos
Antes de comenzar, asegúrate de tener instalado en tu equipo:
* PHP (Versión 8.2 o superior recomendada)
* Composer
* Node.js & NPM
* Servidor de Base de Datos (MySQL)

---

## Pasos para Configurar el Proyecto

Si acabas de descargar o clonar el proyecto en una nueva computadora, ejecuta los siguientes comandos en la raíz de la carpeta:

### 1. Instalar las dependencias de PHP (Laravel)
Descarga todos los paquetes del núcleo del framework:
```bash
composer install
composer require barryvdh/laravel-dompdf
```

### 2. Instalar las dependencias de JavaScript (Vue 3 y Vite)
Descarga los módulos necesarios para la interfaz visual:
```bash
npm install
```

### 3. Configurar el archivo de entorno `.env`
1. Copia el archivo de ejemplo externo ejecutando este comando en la terminal (o duplícalo manualmente):
   ```bash
   cp .env-example .env
   ```
2. Abre el nuevo archivo `.env` que se creó y edita las credenciales de tu base de datos local:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=GimnasioV1
   DB_USERNAME=usuario
   DB_PASSWORD=contraseña
   ```

### 4. Generar la clave de seguridad de la aplicación
Crea la llave de cifrado obligatoria de Laravel:
```bash
php artisan key:generate
```

---

## Creación y Migración de la Base de Datos

Una vez que tengas tu servidor MySQL activo (por ejemplo, en XAMPP) y hayas creado la base de datos vacía con el nombre que pusiste en el `.env`, ejecuta el siguiente comando para estructurar las tablas y sus relaciones automáticas, como sus registros:

```bash
php artisan migrate:fresh --seed
```

---

## Cómo Inicializar el Proyecto

Para hacer funcionar el sistema en tu navegador, necesitas abrir **dos terminales diferentes** en la raíz del proyecto y dejar ambos comandos corriendo al mismo tiempo:

* **Terminal 1: Servidor Local de PHP**
  ```bash
  php artisan serve
  ```
  *Esto levantará el sistema de rutas. Podrás entrar a la aplicación abriendo tu navegador en la dirección: http://127.0.0.1:8000*

* **Terminal 2: Compilador de Assets (Vite + Vue.js)**
  ```bash
  npm run dev
  ```
  *Este comando se encarga de procesar los componentes de Vue y actualizar la pantalla al instante cada vez que guardes un cambio.*


### 5. Usuarios

admin: favio@gmail.com
socio: eddy@gmail.com
recepcionista: kike@gmail.com
entrenador: max@gmail.com

contraseñas: 123456

### 6. Ejecutar

Localhost:8000
