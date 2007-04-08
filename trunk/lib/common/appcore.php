<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

$APF['start_time']=microtime(); //Calcular el tiempo de generación de página

//Cargar la configuración por defecto de la aplicación
require_once($APF['config_path'] . "/DefaultConfig.php");

// Cargar configuración local si existe
if(is_readable($APF['config_path'] . "/LocalConfig.php")) {
	require_once($APF['config_path'] . "/LocalConfig.php");
}

/* Cargar contenido localizado de forma dinamica */
require_once(dirname(__FILE__) . "/../lan/strings.php");
require_once(dirname(__FILE__) . "/gatebuilder.php");

ApfLocal::init($_GET["lan"]);

///Excepción de recurso desconocido
class UnknownResourceException extends Exception {

	///constructor
	///@param name Nombre
	public function __construct($name="None") {
		if($name=="None") $name=_t("None");
		parent::__construct(_t("Unknown_resource") . " " . $name . _t("requested"));
	}
}

///Excpeción de modulo no encontrado
class ModuleNotFoundException extends Exception {

	///constructor
	///@param name Nombre
	public function __construct($name="None") {
		if($name=="None") $name=_t("None");
		parent::__construct(_t("NotFoundModule") . " " . $name);
	}
}

//Crear log
require_once(dirname(__FILE__). "/../log/logger.php");
$stdout=new Logger($APF["log.path"]);

//Instalar handler de errores sobre excepciones
require_once(dirname(__FILE__) . "/exceptions.php");

try {
	//Recurso solicitado
	if (array_key_exists('page',$_GET)) {
		$page=$_GET['page'];
	} else {
		$page="";
	}

	//Fijar main como recurso por defecto
	if(empty($page)) {
		$page=$APF['default_page'];
		$_GET['page']=$page;
	}

	//Instanciar dinámicamente el recurso solicitado si existe
	$lookup_page='page.'.$page;
	if(!array_key_exists($lookup_page,$APF)) {
		$lookup_page='page.'.$APF['default_page'];
		$_GET['page']=$APF['default_page'];
	}

	if(array_key_exists($lookup_page,$APF)) {
		$ipath=dirname(__FILE__) . "/../pages/" . $APF[$lookup_page][0];
		if(!is_readable($ipath)) throw new ModuleNotFoundException($ipath);
		require_once($ipath);
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