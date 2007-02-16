<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//Cargar la configuración por defecto de la aplicación
require_once(dirname(__FILE__) . "/DefaultConfig.php");

// Cargar configuración local si existe
if(is_readable(dirname(__FILE__) . "/LocalConfig.php")) {
	require_once(dirname(__FILE__) . "/LocalConfig.php");
}

//Instalar handler de errores sobre excepciones
require_once(dirname(__FILE__) . "/lib/exceptions.php");

try {

	//Recurso solicitado
	if (array_key_exists('page',$_GET)) {
		$page=$_GET['page'];
	} else {
		$page="";
	}

	//Fijar main como recurso por defecto
	if(empty($page)) $page=$APF['default_page'];

	//Instanciar dinamicamente el recurso solicitado
	$lookup_page='page.'.$page;
	if(array_key_exists($lookup_page,$APF)) {
		require_once(dirname(__FILE__) . "/lib/" . $APF[$lookup_page][0]);
		$args=""; $flag=False;
		foreach ($APF[$lookup_page][2] as $arg) {
			if ($flag) $args=$args . ",";
			else $flag=True;
			$args=$args . $arg;
		}
		eval("\$doc = new {$APF[$lookup_page][1]}($args);");
	} else {
		//Run old code

	//Instanciar dinamicamente el recurso solicitado
	switch($page) {
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
	}//End 

	$doc->show();

} catch(Exception $e) {
		print_exception($e,True);
}

?>