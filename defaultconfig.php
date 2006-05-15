<?php
/*
  Copyright (c) 2005-2006 Alberto Monta�ola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//configuraci�n por defecto del gestor
// **** NO MODIFICAR ESTE FICHERO ****
//Utilizar el fichero "LocalConfig.php" para la configuraci�n de usuario

//Previsualizaciones, y otros datos din�micos
$APF['cache']=dirname(__FILE__) . "/cache";

//Directorio de videos disponibles (para VoD sobre http y videolan)
$APF['videos']=dirname(__FILE__) . "/videos";

//Base de datos
//Servidor (en blanco para usar el socket unix)
$APF['db.host']="";
//Puerto, en blanco para usar puerto por defecto
$APF['db.port']="";
//Nombre de la base de datos
$APF['db.name']="";
//Nombre de usuario
$APF['db.user']="";
//Password
$APF['db.passwd']="";

//Opciones de autenticaci�n

//Activa/Desactiva autenticaci�n Desaf�o/Respuesta
//Para que funcione el navegador debe soportar JavaScript.
$APF['auth.chap']=1;


//Configuraci�n especifica por servidor VoD
//Videolan
//Direccion del servidor de gesti�n (administraci�n)
$APF['videolan.host']="";
//Puerto de telnet
$APF['videolan.port']="";
//


?>