<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//Recurso solicitado
$page=$_GET['page'];

//Fijar main como recurso por defecto
if(empty($page)) $page="main";

//Cargar la configuración de la aplicación
require_once(dirname(__FILE__) . "/DefaultConfig.php");

//Instanciar dinamicamente el recurso solicitado
switch($page) {
	case "filesystem":
		require_once(dirname(__FILE__) . "/filesystem.php");
		$doc = new ApfFileSystemPage();
		break;
	case "upload":
		require_once(dirname(__FILE__) . "/upload.php");
		$doc = new ApfUploadPage();
		break;
	default:
		require_once(dirname(__FILE__) . "/simplepage.php");
		$doc = new ApfSimplePage();
		break;
}

$doc->show();

?>