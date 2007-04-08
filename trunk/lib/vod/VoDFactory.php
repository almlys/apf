<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

/// Fabrica de manejadores para diferentes servidores VoD
class VoDFactory {
	static $handlers=array();

	function __construct() {
		throw new Exception("This class cannot be instanciated");
	}

	/// Obtiene una instancia del manejador VoD pedido
	/// @param type Tipo de manejador VoD
	/// @returns Classe manejadora del servidor VoD solicitado
	static function getVoDHandler($type) {
		if(array_key_exists($type,self::$handlers)) {
			return self::$handlers[$type];
		}
		global $APF;
		//Instanciar dinámicamente el recurso solicitado si existe
		$handler='vod.'.$type;
		if(!array_key_exists($handler,$APF)) {
			throw new ModuleNotFoundException($handler);
		}
		$ipath=dirname(__FILE__) . "/" . $APF[$handler][0];
		if(!is_readable($ipath)) throw new ModuleNotFoundException($ipath);
		require_once($ipath);
		$args=implode(",",$APF[$handler][2]);
		eval("\$vod = new {$APF[$handler][1]}($args);");
		self::$handlers[$type]=$vod;
		return $vod;
	}
}

?>