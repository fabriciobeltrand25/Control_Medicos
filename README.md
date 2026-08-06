# Backend PHP - Control de Pacientes (XAMPP)

## Instalacion
1. Copia toda esta carpeta "control_pacientes_backend" dentro de:
   `C:\xampp\htdocs\control_pacientes_backend`
2. Abre XAMPP y arranca **Apache** y **MySQL**.
3. Abre phpMyAdmin (http://localhost/phpmyadmin).
4. Ve a "Importar" y sube el archivo `base_de_datos.sql` (esto crea la base de
   datos `control_pacientes` con sus tablas y un usuario de prueba).
5. Usuario de prueba para el login de la app: **admin / 1234**

## IP para la app Android
En Windows, abre CMD y escribe `ipconfig`, busca "IPv4 Address" (algo como
192.168.x.x). Esa es la IP que va en `ConfigServidor.java` del proyecto Android:

```
http://TU_IP/control_pacientes_backend/
```

El celular y la laptop deben estar conectados a la MISMA red WiFi.

## Prueba rapida sin la app
Puedes probar los endpoints desde el navegador de la laptop:
- http://localhost/control_pacientes_backend/pacientes.php?accion=listar
- http://localhost/control_pacientes_backend/medicos.php?accion=listar
- http://localhost/control_pacientes_backend/reportes.php?tipo=medicos_especialidad

## Archivos
- `base_de_datos.sql` -> script para crear la BD en phpMyAdmin
- `conexion.php`      -> conexion mysqli (root sin password, como XAMPP trae por defecto)
- `login.php`         -> valida usuario/password
- `pacientes.php`     -> registrar / listar pacientes
- `medicos.php`       -> registrar / listar medicos
- `consultas.php`     -> asignar consulta, buscar consulta activa, dar de baja
- `cobros.php`        -> buscar deuda pendiente (con mora calculada) y procesar pago
- `reportes.php`      -> los 7 reportes pedidos en el documento

## Regla de mora
L 20.00 por cada dia de atraso, contado desde la fecha en que se dio de baja
la consulta (columna `fecha_baja`) hasta el momento del pago.
