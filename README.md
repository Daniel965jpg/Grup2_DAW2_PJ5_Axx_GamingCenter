# Hidden Arena

Hidden Arena es nuestro gaming center y esta es la app que usan los trabajadores para controlarlo. No hay reservas ni nada de eso: la idea es tener una **foto en tiempo real del local**, saber qué puestos están libres, cuáles están ocupados y quién los ha ocupado, y poder mirar después todo lo que ha pasado en el histórico.

La hemos hecho con PHP (procedural y con PDO), MySQL, HTML, CSS y JavaScript, más SweetAlert2 para los avisos y Font Awesome para los iconos. Estas dos últimas se cargan desde internet, así que para verlas bien hace falta conexión.

---

## El equipo

| Quién | Rol | Qué ha hecho |
|---|---|---|
| **Izan Naranjo** | Coordinador | Consultas de las salas, validación con JavaScript y los SweetAlert. |
| **Dani Mayorov** | Impulsor | Crear el repositorio, el login, el panel de salas, el CSS, la consulta del histórico con filtros, la validación en PHP de ocupar y liberar y la revisión de la entrega. |
| **Enric Venanci** | Finalizador | La base de datos y la conexión, las consultas de los módulos, el panel del histórico, la liberación de puestos y la validación en PHP de los filtros. |
| **Jun Hao Xiang** | Support | El logout y la protección de las páginas, la ocupación de puestos y la validación en PHP del login. |

Entre todos hemos hecho las reuniones, el mockup en Figma, las pruebas, los comentarios del código y este README.

---

## Cómo ponerlo en marcha

1. Copiamos la carpeta del proyecto dentro de `www` de Wamp (por ejemplo `C:\wamp64\www\PJ5_F5_GamingCenter_`).
2. Arrancamos Wamp y, en phpMyAdmin, importamos [`sql/db_pj5_gamingCenter.sql`](sql/db_pj5_gamingCenter.sql). El script borra la base de datos `db_pj5_gamingcenter` si ya existe y la vuelve a crear con todas las tablas y los datos de prueba.
3. Miramos que [`bd/conexion.php`](bd/conexion.php) coincida con nuestro MySQL. Viene con `localhost`, usuario `root` y sin contraseña, que es lo normal en Wamp.
4. Abrimos `http://localhost/PJ5_F5_GamingCenter_/` (o el nombre que tenga la carpeta). El `index.php` nos manda directamente al login.

---

## Usuarios para entrar

| Usuario | Rol | Qué puede hacer |
|---|---|---|
| `admin` | admin | Solo el **Cierre de Local**. No ocupa ni libera puestos. |
| `izan`, `hao`, `enric`, `dani` | gamemaster | Ocupar y liberar puestos y consultar todo. |

**La contraseña de todos es `1234`.**

No hay registro de usuarios: son los trabajadores del local, así que los metemos directamente en la base de datos. Las contraseñas no se guardan en claro. Para sacar el hash hicimos un `hash.php` de un solo uso con:

```php
<?php echo password_hash('1234', PASSWORD_DEFAULT); ?>
```

Copiamos el resultado en los `INSERT` del SQL y luego borramos el archivo. Al hacer login, `password_verify()` compara lo que escribe el usuario con ese hash.

---

## Datos de prueba

Al importar el SQL tenemos:

- **9 salas**: 4 de PC Standard, 3 de consolas y simuladores (una de ellas de VR) y 2 VIP.
- **92 puestos**: 40 en las salas de PC, 30 en las de consolas y 22 en las VIP. Por tipo de módulo: 30 de Squad, 36 de Dúo, 12 de Party y 14 de Solo.
- **35 ocupaciones** repartidas entre los cuatro gamemasters: **30 terminadas** (del 9 al 12 de noviembre) y **5 en curso**, con sus puestos marcados como ocupados para que se vea la app funcionando nada más entrar.

---

## Cómo funciona la ocupación

Cada fila de la tabla `modulos` es **un puesto para un jugador** (un PC, un mando, unas gafas de VR, un simulador...). Los puestos están agrupados por tipo de módulo:

- **Squad**: 5 PCs juntos.
- **Dúo**: parejas de puestos (en la sala salen en cajas de 2).
- **Party**: los mandos de los sofás de las salas de consolas.
- **Solo**: puestos sueltos.

El gamemaster ocupa **puesto a puesto**. Cuando llega un grupo, mira en la sala qué módulos tienen sitios libres (cada bloque y cada caja dúo dicen cuántos quedan) y va ocupando. Si no queda ningún módulo de su tamaño, se usan puestos sueltos.

Al ocupar se guarda en `ocupaciones` quién lo ha hecho y la hora de inicio. Al liberar se pone la hora de fin. Mientras `fecha_fin` está vacía, el puesto sigue ocupado. **Solo puede liberar un puesto el gamemaster que lo ocupó.**

---

## Las pantallas

- **Login**: con el vídeo de fondo. Si falta algún campo, avisa antes de enviar.
- **Panel**: para ver de un vistazo cómo está todo. Hay una tarjeta por zona (PC Standard, Consolas y Simuladores, VIP) con los puestos libres y ocupados y la lista de sus salas, y otra tarjeta para el histórico.
- **Sala**: aquí se trabaja. Salen los puestos de la sala agrupados por Squad/Party, Dúo y Solo, con la capacidad y los libres de cada bloque. Al pulsar un puesto sale un SweetAlert para confirmar si lo ocupas o lo liberas, y en los ocupados pone quién lo tiene. Con el desplegable se cambia de sala, y "Historial de Sala" abre el histórico ya filtrado por esa sala.
- **Ocupados**: el control global de todo el local. Muestra todos los puestos ocupados, con filtros por sala y por gamemaster (se aplican solos al cambiar el desplegable). Desde aquí también se puede liberar. El admin tiene además el botón de **Cierre de Local**, que, después de confirmarlo, cierra todas las ocupaciones abiertas y deja todos los puestos libres.
- **Histórico**: para analizar. Arriba hay un resumen con el total de ocupaciones de cada sala, y debajo la tabla con todas las ocupaciones (sala, módulo, puesto, gamemaster, inicio y fin). Se puede filtrar por sala, gamemaster, tipo de módulo, puesto y día.

Todo se adapta a móvil y tablet con `css/responsive.css`. En el ordenador, las salas están pensadas para caber en la pantalla sin scroll.

---

## Seguridad 

- **Consultas preparadas con PDO**: los datos nunca se pegan dentro del SQL. Van aparte con `?`, así nadie puede colarnos una inyección SQL.
- **`htmlspecialchars`**: lo que escribe el usuario se limpia antes de pintarlo, para que no puedan meternos código en la página (XSS).
- **Contraseñas con `password_hash` y `password_verify`**: en la BD solo hay hashes. Si falla el usuario o la contraseña, el mensaje es el mismo para no dar pistas.
- **`session_regenerate_id(true)`**: al entrar se crea un id de sesión nuevo, así que si alguien tenía el de antes ya no le sirve.
- **Páginas protegidas**: el panel, las salas, ocupados, el histórico y los procesos de ocupar, liberar y cerrar el local comprueban que haya sesión. Si no la hay, te mandan al login.
- **Validación en JS y en PHP**: el JavaScript avisa al momento, pero como se puede saltar, el PHP lo vuelve a comprobar todo (campos vacíos, usuario solo con letras y números, que el puesto exista...).
- **Dos gamemasters a la vez**: al ocupar hacemos `UPDATE ... WHERE estado = 'lliure'` y miramos con `rowCount()` si se ha cambiado la fila. Si dos pulsan el mismo puesto a la vez, solo le funciona al primero, y al segundo le sale un aviso. Ocupar (o liberar) cambia dos tablas, así que va dentro de una transacción: o se hacen los dos cambios o ninguno.
- **Permisos del admin**: el rol se mira en la base de datos, no solo en la sesión. El admin no puede ocupar ni liberar puestos, y el Cierre de Local solo lo puede hacer él, aunque alguien escriba la URL a mano.

---

## Estructura de carpetas

```
PJ5_F5_GamingCenter_/
├── index.php               → nos manda al login
├── bd/conexion.php         → la conexión PDO
├── sql/                    → db_pj5_gamingCenter.sql (tablas y datos de prueba)
├── includes/
│   ├── funciones.php       → funciones sencillas (limpiar datos, imagen de cada puesto, contar libres...)
│   └── tarjeta_puesto.php  → el HTML de la tarjeta de un puesto (lo usan sala y ocupados)
├── vistas/                 → login, dashboard (panel), sala, ocupados e historico
├── procs/                  → login, logout, ocupar/liberar y cierre de local
├── js/script.js            → validación del login, filtros y SweetAlerts
├── css/                    → styles.css (escritorio) y responsive.css (móvil y tablet)
├── img/                    → imágenes de los puestos, logos y el vídeo de fondo
└── documentacion/          → Gantt, plantilla de daily meeting y mockup de Figma
```

Las **vistas** solo leen de la base de datos y pintan la página. Los **procesos** son los que cambian datos y, al acabar, nos devuelven a la vista con un `header('Location: ...')`.

---

## Documentación

- [Diagrama de Gantt](documentacion/Diagrama_de_Gantt_PJ5_F5_GamingCenter.xlsx)
- [Plantilla de daily meeting](documentacion/Plantilla%20Scrum%20daily%20meeting%20-%20Grupo%202%20(DAW2).xlsx)
- [Mockup en Figma](documentacion/HIDDEN.fig)
