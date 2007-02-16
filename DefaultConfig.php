<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

// Configuración por defecto del gestor
// **** NO MODIFICAR ESTE FICHERO ****
// Utilizar el fichero 'LocalConfig.php' para la configuración de usuario

/*#############################################################################
**** Preferencias Generales ***************************************************
#############################################################################*/

// *** Errores y Excepciones ***

///Mostrar Excepciones
$APF['display_exceptions']=True;

// *** Localización y Codificación ***

///Idioma por defecto utilizado por la aplicación.
$APF['default_language']='es';
///Idiomas disponibles para la aplicación
/// Para instalar una nueava localización, es importante hacer dos cosas.
/// 1) Añadir el fichero strings.id.php al directorio lib/lan
/// 2) Actualizar los campos enumerados (enum) de la base de datos para que
/// contenplen estos nuevos valores, o simplemente cambiarlos al tipo carácter
/// para poder añadir nuevas localizaciones en el futuro sin tener que modificar
/// la base de datos.
$APF['languages']=array('ca','en','es');
$APF['language_names']=array('Catal&agrabe;','English','Espa&ntilde;ol');

// *** Directorios ***

///Ruta del directorio base del servidor (en blanco autodetectar)
///Nota: Si se va a llamar el script desde otros scripts en otras rutas
/// entonces es necessario especificar una ruta fija, ya que la autodetecci�
/// fallar� Si la autodetecci� falla, entonces es necesario fijar una ruta.
$APF['server.path']='';
///Ruta donde se encuentra instalado el script en el sistema de ficheros
$APF['system.path']=dirname(__FILE__);

///Ruta del fichero Log
$APF['log.path']=$APF['system.path'] . "/log/stdout.log";

///Ruta relativa a previsualizaciones, y otros datos dinámicos
$APF['cache']='cache';

///Ruta relativa a videos disponibles (para VoD sobre http y videolan)
$APF['videos']='videos';

///Indica si se utilizar� rutas relativas, o absolutas.
///Nota: No se pueden utilizar rutas relativas si se va a llamar al script desde
///otros scripts. Desactivar rutas relativas en caso de problemas de funcionamiento.
$APF['relative_paths']=1;

// *** Páginas y recursos ***
///Página por defecto
$APF['default_page']='main';

///Todas las páginas
$APF['user.pages']=array('main');
$APF['admin.pages']=array();
$APF['page.main']=array('pages/MainPage.php','ApfMainPage',array());
$APF['page.categ']=array('pages/MediaPage.php','ApfMediaPage',array());
$APF['page.videos']=array('pages/VideoPage.php','ApfVideoPage',array());
$APF['page.login']=array('pages/LoginPage.php','ApfLoginPage',array());
$APF['page.logout']=array('pages/LoginPage.php','ApfLoginPage',array("True"));
$APF['page.edit']=array('pages/EditPage.php','ApfEditPage',array());
$APF['page.admin']=array('pages/AdminPage.php','ApfAdminPage',array());

// *** Base de datos ***
///Plugin de la base de datos
$APF['db.plug']='db/MySQL.php';
///Servidor y puerto (en blanco para usar el socket unix)
$APF['db.host']='';
///Nombre de la base de datos
$APF['db.name']='';
///Nombre de usuario
$APF['db.user']='';
///Password
$APF['db.passwd']='';

// *** Opciones de autenticación� ***

///Selecciona el modulo de autenticación a utilizar
$APF['auth.plug']='auth.php';

///Activa/Desactiva autenticación Desafío/Respuesta
///(Challenge Handshake Authentication Protocol)
///Para que funcione el navegador debe soportar JavaScript.
$APF['auth.chap']=1;


// *** Configuración servidores VoD ***

///Indica la ruta al manejador que conecta la aplicación con el servidor VoD
$APF['vod.plug']='HttpVoDhandler.php';
//$APF['vod.plug']='VideoLanVoDhandler.php';

// *** Configuración especifica por servidor VoD ***

///Videolan
///Dirección del servidor de gestión (administración)
$APF['videolan.host']='';
///Puerto de telnet
$APF['videolan.port']='';


// *** Subida de ficheros ***
///Script que se encarga de la subida de ficheros
///Este script nos permite saltarnos los l�ites de 2MBytes a la vez que nos permite
/// enviar un feedback al usuario.
$APF['upload_script']='cgi-bin/upload.py';
///Directorio de ficheros subidos (debe ser idéntico al configurado en el script)
//$APF['upload_dir']='/tmp/apf_upload';
$APF['upload_dir']='/home/apf_upload';


?>