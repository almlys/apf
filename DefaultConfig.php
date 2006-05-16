<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//configuración por defecto del gestor
// **** NO MODIFICAR ESTE FICHERO ****
//Utilizar el fichero "LocalConfig.php" para la configuración de usuario

// *** Preferencias Generales ***
///Idioma por defecto utilizado por la aplicación.
$APF['default_language']="es";
///Idiomas disponibles para la aplicación
/// Para instalar una nueava localización, es importante hacer dos cosas.
/// 1) Añadir el fichero strings.id.php al directorio lib/lan
/// 2) Actualizar los campos enumerados (enum) de la base de datos para que
/// contenplen estos nuevos valores, o simplemente cambiarlos al tipo carácter
/// para poder añadir nuevas localizaciones en el futuro sin tener que modificar
/// la base de datos.
$APF['languages']=array("ca","en","es");
$APF['language_names']=array("Catal&agrabe;","English","Espa&ntilde;ol");
// ***

// *** Directorios ***

///Ruta del directorio base del servidor (en blanco autodetectar)
///Nota: Si se va a llamar el script desde otros scripts en otras rutas
/// entonces es necessario especificar una ruta fija, ya que la autodetección
/// fallará. Si la autodetección falla, entonces es necesario fijar una ruta.
$APF['server.path']="";
///Ruta donde se encuentra instalado el script en el sistema de ficheros
$APF['system.path']=dirname(__FILE__);

///Ruta relativa a previsualizaciones, y otros datos dinámicos
$APF['cache']="cache";

///Ruta relativa a videos disponibles (para VoD sobre http y videolan)
$APF['videos']="videos";

///Indica si se utilizarán rutas relativas, o absolutas.
///Nota: No se pueden utilizar rutas relativas si se va a llamar al script desde
///otros scripts. Desactivar rutas relativas en caso de problemas de funcionamiento.
$APF['relative_paths']=1;
// ***

// *** Base de datos ***
///Servidor y puerto (en blanco para usar el socket unix)
$APF['db.host']="";
///Nombre de la base de datos
$APF['db.name']="";
///Nombre de usuario
$APF['db.user']="";
///Password
$APF['db.passwd']="";
// ***

// *** Opciones de autenticación ***

///Activa/Desactiva autenticación Desafío/Respuesta
///(Challenge Handshake Authentication Protocol)
///Para que funcione el navegador debe soportar JavaScript.
$APF['auth.chap']=1;
// ***

// *** Configuración especifica por servidor VoD ***
///Videolan
///Direccion del servidor de gestión (administración)
$APF['videolan.host']="";
///Puerto de telnet
$APF['videolan.port']="";
// ***


// Cargar configuración local si existe
if(is_readable(dirname(__FILE__) . "/LocalConfig.php")) {
	require_once(dirname(__FILE__) . "/LocalConfig.php");
}
//

//Post

?>