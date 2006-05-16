<?php
/*
  Copyright (c) 2005-2006 Alberto Monta�ola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//configuraci�n por defecto del gestor
// **** NO MODIFICAR ESTE FICHERO ****
//Utilizar el fichero "LocalConfig.php" para la configuraci�n de usuario

// *** Preferencias Generales ***
///Idioma por defecto utilizado por la aplicaci�n.
$APF['default_language']="es";
///Idiomas disponibles para la aplicaci�n
/// Para instalar una nueava localizaci�n, es importante hacer dos cosas.
/// 1) A�adir el fichero strings.id.php al directorio lib/lan
/// 2) Actualizar los campos enumerados (enum) de la base de datos para que
/// contenplen estos nuevos valores, o simplemente cambiarlos al tipo car�cter
/// para poder a�adir nuevas localizaciones en el futuro sin tener que modificar
/// la base de datos.
$APF['languages']=array("ca","en","es");
$APF['language_names']=array("Catal&agrabe;","English","Espa&ntilde;ol");
// ***

// *** Directorios ***

///Previsualizaciones, y otros datos din�micos
$APF['cache']=dirname(__FILE__) . "/cache";

///Directorio de videos disponibles (para VoD sobre http y videolan)
$APF['videos']=dirname(__FILE__) . "/videos";
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

// *** Opciones de autenticaci�n ***

///Activa/Desactiva autenticaci�n Desaf�o/Respuesta
///(Challenge Handshake Authentication Protocol)
///Para que funcione el navegador debe soportar JavaScript.
$APF['auth.chap']=1;
// ***

// *** Configuraci�n especifica por servidor VoD ***
///Videolan
///Direccion del servidor de gesti�n (administraci�n)
$APF['videolan.host']="";
///Puerto de telnet
$APF['videolan.port']="";
// ***


// Cargar configuraci�n local si existe
if(is_readable(dirname(__FILE__) . "/LocalConfig.php")) {
	require_once(dirname(__FILE__) . "/LocalConfig.php");
}
//

//Post

?>