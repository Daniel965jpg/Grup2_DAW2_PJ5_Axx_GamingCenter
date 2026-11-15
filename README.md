# Hidden Arena — Gestión de un Gaming Center

Aplicación web interna para que el personal del gaming center **Hidden Arena** consulte las salas, controle la disponibilidad de los puestos y revise el histórico de ocupaciones. No gestiona reservas futuras: refleja el estado actual del local.

## Funcionalidades

- Inicio y cierre de sesión de gamemasters y administrador, con contraseñas verificadas mediante `password_verify()` y páginas protegidas por sesión.
- Panel con el número de puestos libres y ocupados por zona: PC Standard, consolas/simuladores y VIP.
- Consulta de las salas y sus puestos, agrupados por formato Solo, Dúo, Squad y Party.
- Ocupación de un puesto libre y liberación de un puesto ocupado. Se guarda el gamemaster y la hora de inicio y fin; solo quien inició la ocupación puede liberarla.
- Vista de puestos ocupados con filtros por sala y gamemaster; histórico con filtros por sala, gamemaster, puesto y día.
- Confirmaciones y avisos con SweetAlert2, validación de los campos del login en JavaScript y comprobaciones de servidor en PHP.
- Interfaz oscura adaptable a móvil y escritorio.

La ocupación se registra por puesto/módulo. Aunque la base de datos incluye el campo de capacidad y las vistas presentan formatos de juego, la aplicación no pide cuántos clientes componen un grupo ni valida ese número contra la capacidad antes de ocupar.

## Tecnologías

- PHP y PDO para el servidor y las consultas preparadas.
- MySQL/MariaDB para usuarios, salas, módulos y ocupaciones.
- HTML, CSS responsive y JavaScript.
- SweetAlert2 y Font Awesome se cargan desde CDN, por lo que la interfaz necesita conexión a Internet para esos recursos.

## Requisitos

- Servidor local con PHP y MySQL/MariaDB (por ejemplo, WampServer).
- Extensión PDO para MySQL habilitada.
- Navegador web.

## Instalación local

1. Copia el proyecto dentro de la carpeta web del servidor; en WampServer suele ser `C:\wamp64\www\PJ5_F5_GamingCenter_`.
2. Inicia Apache y MySQL.
3. En phpMyAdmin, importa [`sql/bd_gaming_center.sql`](sql/bd_gaming_center.sql). El script crea `db_pj5_gamingcenter` y las tablas, usuarios, salas y módulos de ejemplo.
4. Comprueba los parámetros de conexión en [`bd/conexion.php`](bd/conexion.php). La configuración incluida es `localhost`, base de datos `db_pj5_gamingcenter`, usuario `root` y contraseña vacía.
5. Abre `http://localhost/PJ5_F5_GamingCenter_/`. `index.php` redirige al formulario de acceso.

> **Atención:** el SQL empieza con `DROP DATABASE IF EXISTS db_pj5_gamingcenter`; importarlo de nuevo elimina y recrea esa base de datos. Además, el archivo contiene bloques de inserción repetidos para los módulos de las salas PC 2, 3 y 4. Revísalos antes de importar para no cargar módulos duplicados.

## Acceso de prueba

El SQL crea las cuentas `admin`, `izan`, `hao`, `enric` y `dani`. Las contraseñas se guardan como hashes, pero el repositorio no documenta la contraseña en texto plano; no se debe asumir una contraseña por defecto.

Para preparar una contraseña de prueba, genera un hash en PHP:

```php
<?php echo password_hash('CAMBIA_ESTA_CLAVE', PASSWORD_DEFAULT); ?>
```

Actualiza el hash de la cuenta que quieras usar en phpMyAdmin, sustituyendo `PEGA_AQUI_EL_HASH` por el resultado:

```sql
UPDATE usuarios
SET password = 'PEGA_AQUI_EL_HASH'
WHERE username = 'admin';
```

Después inicia sesión con el nombre de usuario seleccionado y la contraseña que acabas de elegir.

## Recorrido para probar la aplicación

1. Inicia sesión y comprueba el resumen de disponibilidad de las tres zonas.
2. Entra en una zona, cambia de sala con el selector y revisa los puestos por formato.
3. Selecciona un puesto libre y confirma la ocupación. Comprueba que pasa a ocupado y aparece en **Ver Ocupados**.
4. Inicia sesión con el mismo gamemaster para liberar ese puesto; otro usuario no debe poder liberarlo.
5. Abre el **Histórico** y prueba los filtros de sala, gamemaster, puesto y día. Una ocupación activa aparece sin hora de fin.
6. La opción **Cierre de Local** finaliza todas las ocupaciones activas y deja libres los puestos.

### Datos de prueba y limitaciones conocidas

El SQL incluye las nueve salas previstas (cuatro PC Standard, tres de consolas/simuladores y dos VIP), varios tipos de puesto y cinco cuentas. **No incluye filas de ejemplo en `ocupaciones`**, por lo que el histórico estará vacío después de importar la base de datos. Para la exposición hay que registrar y liberar puestos desde la aplicación para generar histórico; si se necesitan registros previos, deben añadirse datos de prueba coherentes antes de presentar.

La base de datos tiene el rol `admin`, pero las páginas y procesos actualmente solo comprueban que exista una sesión: no aplican permisos distintos por rol. La opción de cierre de local está disponible para cualquier usuario con sesión iniciada.

## Estructura del proyecto

```text
bd/          Conexión PDO
css/         Estilos generales y responsive
img/         Logotipos, imágenes de puestos y vídeo de fondo
includes/    Funciones comunes y tarjeta de puesto
js/          Validaciones, filtros y confirmaciones
procs/       Login, logout, cambios de estado y cierre del local
sql/         Esquema y datos de salas, módulos y usuarios
vistas/      Login, panel, salas, puestos ocupados e histórico
index.php    Redirección a la pantalla de login
```

## Equipo, tareas y organización

Los documentos del proyecto identifican a cuatro integrantes. Las tareas siguientes se resumen a partir del Gantt y del registro de reuniones incluido en el repositorio:

| Integrante (según documentación del proyecto) | Tareas documentadas |
|---|---|
| Dani / Daniel Mayorov | Responsable del proyecto junto con el resto del equipo; repositorio, login, panel de salas, CSS, filtros del histórico, validaciones PHP y revisión de entrega. |
| Enric Venanci | Base de datos y conexión; consultas de módulos; panel del histórico; trabajo en salas de consolas/simuladores y revisión de código. |
| Izan Naranjo | Validación JavaScript, SweetAlert, trabajo en salas/panel e histórico y sus filtros. |
| Hao / Jun Hao Xiang | Logout y protección de páginas; ocupación y liberación de puestos; validación PHP del login; trabajo en salas VIP. |

El Gantt y las reuniones reflejan trabajo compartido en diseño, pruebas, revisión y documentación. La plantilla de reuniones diarias está en [`Plantilla Scrum daily meeting - Grupo 2 (DAW2).xlsx`](Plantilla%20Scrum%20daily%20meeting%20-%20Grupo%202%20(DAW2).xlsx), y la planificación está en [`Diagrama_de_Gantt_PJ5_F5_GamingCenter.xlsx`](Diagrama_de_Gantt_PJ5_F5_GamingCenter.xlsx).

**Roles formales del equipo:** los documentos revisados no indican quién asumió específicamente los roles de coordinador, impulsor y finalizador. Hay que asignarlos y completar esta sección antes de la entrega. Tampoco consta un quinto integrante en el Gantt ni en las actas de reuniones disponibles.
