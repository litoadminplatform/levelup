## Landing Page for Moodle | Level Up Americana
1. [Información general](#informacion-general)
2. [Tecnologías](#tecnologías)
3. [Instalación](#instalación)
### Información general
***
Landing Pages for Moodle, es una utilidad que extiende cualquier tema de moodle para que enseñe una landing page y poder comprar cursos ofertados.
### Captura de pantalla
![Image text](https://cec.americana.edu.co/theme/moove/americana/src/vistas/img/readmeimage.png)
### Tecnologías
***
Este proyecto fue desarrollado para usar con Nginx o Apache, php7 y utiliza PostgreSQL, y utiliza la pasarela de Pagos PayU.

## Instalación
***
1) Se debe definir el tema grafico con que el se va a trabajar en moodle, ya que se va a incluir una linea de codigo en el core del tema escogido para la ejecución de las landing pages.<br><br>
2) En una instalación de moodle nueva existen 8 roles de usuarios, usted debe crear un nuevo rol de usuario con cualquier nombre, sugerimos "Soporte" y este quedará con el id 9. Se le debe asignar a los usuarios de moodle que van a editar las opciones de LangindPages este rol. LandingPages buscará los usuarios que tengan este id rol asignado para que sean los configuradores de LandingPages, esto es: Editar precios de cursos, descipciones de cursos, los textos copy de la portada, los textos de la invitación a registrarse, y el telefono de whatsapp.<br><br>
3) Las politicas de contarseñas del sitio deben permitir contraseñas simples (no obligrar a introducir simbolos y números) y el mínimo deberia ser 4 caracteres. (Ya que al momento de LandinPages crear usuarios utiliza la cedula como contraseña por defecto).<br><br>
4) Clona el repositorio en la siguiente ruta de tu sistema moodle: TU_PROYECTOMOODLE/themes/EL_TEMA_QUE_USAS/**americana**  En donde americana es la carpeta en donde ha de colonarse el proyecto, debe llamarse así "americana".<br><br>
5) Ejecuta la migración de base de datos accediendo con tu nagevador a la siguente url: https://mistio.com/info/api/sitio/generarbasededatos/postgres  DONDE "postgres" es el rol de postgre que tiene permitido crear y editar las tablas de su base de datos moodle, se deben crear automáticamente las tablas *cuponesdedescuento* y *factura* y sus respectivos "secuences" dentro de tu base de datos de moodle.<br><br>
6) Entra en la carpeta TU_PROYECTOMOODLE/theme/EL_TEMA_QUE_USAS/layout y dentro de ella habrán varios archivos, a cada uno agrégale la siguiente línea de codigo php despues de la linea que dice **defined('MOODLE_INTERNAL') || die()** <br><br>
Agregar:<br>
```
	include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/htmladicionalhead.php');
```
7) Agrega el siguiente mod_rewrite a tu NGINX o apache, en nginx por ejemplo edita el archivo /etc/nginx/sites-avaliable/TU_ARCHIVO_DE_CONFIGURACION_DEL_SITIO<br>
Y agrega entre las lineas de server { }  lo siguiente:

```
	location /info {
  		rewrite ^/info/(.+)$ /theme/EL_TEMA_QUE_USAS/americana/index.php?url=$1 last;
	}
```
En apache crea un archivo .htaccess en el root de tu moodle y agregale la siguientes lineas:
```
RewriteEngine On
RewriteRule ^info/(.+)$ theme/EL_TEMA_QUE_USAS/americana/index.php?url=$1 [QSA,L]
```
Importante: El "EL_TEMA_QUE_USAS" debe ser reemplazado por el nombre de la carpeta del tema escogido con el que tabaja moodle. Si se llega a cambiar el tema debes editar de nuevo la configuracion de ngnix o apache en este punto. Y **americana** es la carpeta donde se clonó el repositorio.<br><br>

8) Edita el archivo **americana/src/controladores/ControladorUsuario.php** y coloca los datos de un usuario que tenga permisos para crear usuarios pero que no sea el administrador principal en la linea 58 donde aparece invocada la funcion **iniciarSesionMoodle(username, password)**. <br><br>
9) Entra a la edición de las categorías de cursos y crea una categoría y colócale en **Número ID de la categoría** un 1. El programa buscará la categoria con este número de id y asumirá que esta es la categoría cuyo cursos en su interior se quieren ofertar.<br><br>
10) Deberás agregar subcategorías de un solo nivel dentro de la categoría que creaste en el paso anterior, estas categorías aparecerán en la navegación del landing page, y los cursos dentro de ella serán los mostrados.<br><br>
11) Crea cursos dentro de las subcategorías creadas en el punto anterior y colócales una fecha de inicio futura, todos los cursos que tengan una fecha de inicio futura serán ofertados, los cursos que alcancen la fecha de inicio dejarán de mostrarse en la landing page.<br><br>
12) Colócale una imagen a cada curso adjuntado la imagen en la sección llamada "**Resumen del curso**".<br><br>
13) Coloca las credenciales de PayU para las compras en el archivo: **americana/src/controladores/ContorladorCarrito.php** en la función llamada **getCredenciales()**, nota que retorna las credenciales de pruebas y las credenciales reales, debes reemplazar los datos en las credenciales reales.<br><br>
14) Para editar los colores, logotipos, información del footer, css, recomendamos crear una nueva versión del repositorio y editar los archivos directamente en el a su gusto.<br><br>

