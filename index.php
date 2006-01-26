<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.
*/

//Recurso solicitado
$page=$_GET['page'];

if(empty($page)) $page="main";

//Instanciar dinamicamente el recurso solicitado

switch($page) {
	case "main":
		include_once(dirname(__FILE__) . "/mainpage.php");
		$doc = new ApfMainPage();
		break;
	case "categ":
		include_once(dirname(__FILE__) . "/mediapage.php");
		$doc = new ApfMediaPage();
		break;
	case "videos":
		include_once(dirname(__FILE__) . "/videopage.php");
		$doc = new ApfVideoPage();
		break;
	case "login":
		include_once(dirname(__FILE__) . "/loginpage.php");
		$doc = new ApfLoginPage();
		break;
	case "logout":
		include_once(dirname(__FILE__) . "/loginpage.php");
		$doc = new ApfLoginPage(1);
		break;
	case "edit":
		include_once(dirname(__FILE__) . "/editpage.php");
		$doc = new ApfEditPage();
		break;
	case "admin":
		include_once(dirname(__FILE__) . "/adminpage.php");
		$doc = new ApfAdminPage();
		break;
	default:
		include_once(dirname(__FILE__) . "/manager.php");
		$doc = new ApfManager("Unknown");
}


$doc->show();

?>


