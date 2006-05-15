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
// ***

// *** Directorios ***

///Previsualizaciones, y otros datos dinámicos
$APF['cache']=dirname(__FILE__) . "/cache";

///Directorio de videos disponibles (para VoD sobre http y videolan)
$APF['videos']=dirname(__FILE__) . "/videos";
// ***

// *** Base de datos ***
///Servidor (en blanco para usar el socket unix)
$APF['db.host']="";
///Puerto, en blanco para usar puerto por defecto
$APF['db.port']="";
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
if(file_exists(dirname(__FILE__) . "/LocalConfig.php")) {
	require_once(dirname(__FILE__) . "/LocalConfig.php");
}
//

//Post

?>