<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.
*/

//configuración del gestor

//Previsualizaciones, y otros datos dinámicos
$APF['cache']=dirname(__FILE__) . "/cache";

//Directorio de videos disponibles (para VoD sobre http y videolan)
$APF['videos']=dirname(__FILE__) . "/videos";

//Base de datos
//Servidor (en blanco para usar el socke unix)
$APF['db.host']="";
//Puerto, en blanco para usar puerto por defecto
$APF['db.port']="";
//Nombre de la base de datos
$APF['db.name']="";
//Nombre de usuario
$APF['db.user']="";
//Password
$APF['db.passwd']="";

//Configuración especifica por servidor VoD
//Videolan
//Direccion del servidor de gestión (administración)
$APF['videolan.host']="";
//Puerto de telnet
$APF['videolan.port']="";
//


?>