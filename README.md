## Landing Page for Moodle | Level Up Americana
1. [Información general](#informacion-general)
3. [Tecnologías](#tecnologías)
3. [Instalación](#instalación)
### Información general
***
Landing Pages for Moodle, es una utilidad que extiende cualquier tema de moodle para que enseñe una landing page de compras de cursos a los usuarios sin la necesidad de inicio de sesión.
### Captura de pantalla
![Image text](https://cec.americana.edu.co/theme/moove/americana/src/vistas/img/readmeimage.png)
### Tecnologías
***
Este proyecto fue desarrollado para usar con Nginx, php7 y utiliza PostgreSQL, y utiliza la pasarela de Pagos PayU.

## Instalación
***
1) Clona el repositorio en la siguiente ruta de tu sistema moodle: TU_PROYECTOMOODLE/themes/EL_TEMA_QUE_USAS/**americana**  En donde americana es la carpeta en donde ha de colonarse el proyecto, debe llamarse así "americana".<br><br>
2) Ejecuta la migración de base de datos accediendo con tu nagevador a la siguente url: https://mistio.com/info/sitio/generarbasededatos  se deben crear las tablas *cuponesdedescuento* y *factura* dentro de tu base de datos de moodle.<br><br>
3) Edita el archivo  TU_PROYECTOMOODLE/theme/EL_TEMA_QUE_USAS/layout/frontpage.php y agrégale la siguiente línea de codigo php despues de la linea que dice **defined('MOODLE_INTERNAL') || die()** <br><br>
Agregar:<br>
```
	include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/htmladicionalhead.php');
```
4) Agrega el siguiente mod_rewrite a tu NGINX o apache, en nginx por ejemplo edita el archivo /etc/nginx/sites-avaliable/TU_ARCHIVO_DE_CONFIGURACION_DEL_SITIO<br>
Y agrega entre las lineas de server { }  lo siguiente:

```
	location /info {
  		rewrite ^/info/(.+)$ /theme/moove/americana/index.php?url=$1 last;
	}
```
En donde **moove** es el tema que estás usando en moodle, podría ser cualquier otro y **americana** la carpeta donde se clonó el repositorio.<br><br>
5) Edita el archivo americana/src/controladores/ControladorCarrito.php y coloca los datos de un usuario que tenga permisos para crear usuarios pero que no sea el administrador principal en la linea 58 donde aparece invocada la funcion **iniciarSesionMoodle(username, password)**. <br><br>
6) Entra a la edición de las categorías de cursos y crea una categoría y colócale en **Número ID de la categoría** un 1. El programa buscará la categoria con este número de id y asumirá que esta es la categoría cuyo cursos en su interior se quieren ofertar.<br><br>
7) Deberás agregar subcategorías de un solo nivel dentro de la categoría que creaste en el paso anterior, estas categorías aparecerán en la navegación del landing page, y los cursos dentro de ella serán los mostrados.<br><br>
8) Crea cursos dentro de las subcategorías creadas en el punto anterior y colócales una fecha de inicio futura, todos los cursos que tengan una fecha de inicio futura serán ofertados, los cursos que alcancen la fecha de inicio dejarán de mostrarse en la landing page.<br><br>
9) Colócale una imagen a cada curso adjuntado la imagen en la sección llamada "**Resumen del curso**".<br><br>
10) Coloca las credenciales de PayU para las compras en el archivo: **americana/src/controladores/ContorladorCarrito.php** en la función llamada **getCredenciales()**, nota que retorna las credenciales de pruebas y las credenciales reales, debes reemplazar los datos en las credenciales reales.<br><br>


