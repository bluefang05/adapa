# ADAPA Setup

## Requisitos

- PHP 8.2+
- MariaDB/MySQL
- Apache con `mod_rewrite`
- XAMPP o equivalente

## Base de datos

El esquema oficial actual del proyecto es:

- `adapa_db.sql`

Para importar el dump en XAMPP:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS adapa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
C:\xampp\mysql\bin\mysql.exe -u root adapa < adapa_db.sql
```

## Configuracion

Revisa:

- `config/database.php`
- `config.php`
- `.htaccess`

Si el proyecto se sirve desde `http://localhost/adapa`, la configuracion actual ya esta alineada.

## Acceso demo

Los accesos visibles en la pantalla de login usan estas cuentas:

- `estudiante1@adapa.edu` / `estudiante123`
- `profesor@adapa.edu` / `profesor123`
- `admin@adapa.edu` / `admin123`

## Notas

- No existe instalador web; la referencia canonica es `adapa_db.sql`.
- Las rutas mutadoras ya usan `POST`.
- El proyecto debe ejecutarse a traves de Apache para que el rewrite funcione correctamente.
