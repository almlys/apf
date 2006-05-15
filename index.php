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
	case "main":
		require_once(dirname(__FILE__) . "/mainpage.php");
		$doc = new ApfMainPage();
		break;
	case "categ":
		require_once(dirname(__FILE__) . "/mediapage.php");
		$doc = new ApfMediaPage();
		break;
	case "videos":
		require_once(dirname(__FILE__) . "/videopage.php");
		$doc = new ApfVideoPage();
		break;
	case "login":
		require_once(dirname(__FILE__) . "/loginpage.php");
		$doc = new ApfLoginPage();
		break;
	case "logout":
		require_once(dirname(__FILE__) . "/loginpage.php");
		$doc = new ApfLoginPage(1);
		break;
	case "edit":
		require_once(dirname(__FILE__) . "/editpage.php");
		$doc = new ApfEditPage();
		break;
	case "admin":
		require_once(dirname(__FILE__) . "/adminpage.php");
		$doc = new ApfAdminPage();
		break;
	default:
		require_once(dirname(__FILE__) . "/manager.php");
		$doc = new ApfManager("Desconocido");
}


$doc->show();

?>


