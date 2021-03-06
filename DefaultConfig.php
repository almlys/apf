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
/// entonces es necessario especificar una ruta fija, ya que la autodetección
/// fallaría. Si la autodetección falla, entonces es necesario fijar una ruta.
$APF['server.path']='';
///Ruta donde se encuentra instalado el script en el sistema de ficheros
$APF['system.path']=dirname(__FILE__);

///Ruta del fichero Log
$APF['log.path']=$APF['system.path'] . "/log/stdout.log";

///Ruta relativa a previsualizaciones, y otros datos dinámicos
/// (debe ser relativa) (absoluta no vale)
$APF['upload.imgs']='upload/imgs';

///Ruta relativa a vídeos subidos
///Poner ruta absoluta si se utiliza un servidor VoD no HTTP
///para el servidor HTTP es obligatorio usar una ruta relativa y visible
///desde fuera.
$APF['upload.videos']='upload/videos';


///Indica si se utilizarán rutas relativas, o absolutas.
///Nota: No se pueden utilizar rutas relativas si se va a llamar al script desde
///otros scripts. Desactivar rutas relativas en caso de problemas de funcionamiento.
$APF['relative_paths']=1;

// *** Páginas y recursos ***
///Página por defecto
$APF['default_page']='main';

///Todas las páginas
$APF['user.pages']=array('main');
$APF['admin.pages']=array();
$APF['page.main']=array('common/MainPage.php','ApfMainPage',array());
$APF['page.categ']=array('common/MediaPage.php','ApfMediaPage',array());
$APF['page.videos']=array('common/VideoPage.php','ApfVideoPage',array());
$APF['page.login']=array('common/LoginPage.php','ApfLoginPage',array());
$APF['page.logout']=array('common/LoginPage.php','ApfLoginPage',array("True"));
$APF['page.edit']=array('common/EditPage.php','ApfEditPage',array());
$APF['page.admin']=array('common/AdminPage.php','ApfAdminPage',array());
$APF['page.iupload']=array('simple/UploadPage.php','ApfUploadPage',array());
$APF['page.rpc']=array('simple/rpcserver.php','ApfRPCServer',array());
$APF['page.fsrpc']=array('simple/FileSystemRPC.php','ApfFileSystemRPC',array());

$APF['page.test']=array('../../test/test.php','ApfTestPage',array());

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

// *** Opciones de autenticación ***

///Selecciona el modulo de autenticación a utilizar
$APF['auth.plug']='auth/SimpleAuth.php';

///Activa/Desactiva autenticación Desafío/Respuesta
///(Challenge Handshake Authentication Protocol)
///Para que funcione el navegador debe soportar JavaScript.
$APF['auth.chap']=True;


// *** Configuración servidores VoD ***

///Indica la ruta al manejador que conecta la aplicación con el servidor VoD
$APF['vod.http']=array('HttpVoDhandler.php','ApfHttpVoDHandler',array());
$APF['vod.videolan']=array('VideoLanVoDhandler.php','ApfVideoLanVoDHandler',array());

///Tipo de manejador por defecto
$APF['default_vod']='videolan';

// *** Configuración especifica por servidor VoD ***

///Videolan
///Dirección del servidor de gestión (administración)
$APF['videolan.host']='localhost';
///Puerto de telnet
$APF['videolan.port']='4212';
///Password
$APF['videolan.passwd']='admin';

// *** Subida de ficheros ***
///Script que se encarga de la subida de ficheros
///Este script nos permite saltarnos los límites de 2MBytes a la vez que nos permite
/// enviar un feedback al usuario.
$APF['upload_script']='cgi-bin/upload.py';
///Directorio de ficheros subidos (debe ser idéntico al configurado en el script)
//$APF['upload_dir']='/tmp/apf_upload';
$APF['upload_dir']='/home/apf_upload/upload';

// *** Configuración de la sessión y Cookies ***
$APF['session.expire']=3600;
$APF['cookie.path']="/";
$APF['cookie.domain']='';
$APF['cookie.secure']=False;
$APF['cookie.name']='ApfVoDAuthData';
$APF['session.name']='ApfVoDPHPSID';
$APF['cookie.crypt']=True;
$APF['cookie.http']=True;
$APF['crypt.signkey']='Ppj6DAHbzES98rxMT0pEAHyzVNi2i7p0DI8HyUhmyiTMrQHA9tSmfFgn33o1Lhb2';
$APF['crypt.key']='uEoKwxbLJoVD673euFWNYui6g0w22mIf';

?>