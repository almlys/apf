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

class UnknownResourceException extends Exception {
	public function __construct($name="None") {
		parent::__construct("Unknown resource $name requested");
	}
}

//Crear log
require_once(dirname(__FILE__). "/lib/log/logger.php");
$stdout=new Logger($APF["log.path"]);

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

	//Instanciar dinámicamente el recurso solicitado si existe
	$lookup_page='page.'.$page;
	if(!array_key_exists($lookup_page,$APF)) {
		$lookup_page='page.'.$APF['default_page'];
		$_GET['page']=$APF['default_page'];
	}

	if(array_key_exists($lookup_page,$APF)) {
		require_once(dirname(__FILE__) . "/lib/" . $APF[$lookup_page][0]);
		$args=implode(",",$APF[$lookup_page][2]);
		eval("\$doc = new {$APF[$lookup_page][1]}($args);");
	} else {
		throw new UnknownResourceException($page);
	}

	$doc->show();

} catch(Exception $e) {
		print_exception($e,True);
}

?>