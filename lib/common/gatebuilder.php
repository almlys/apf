<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

///Los constructores de cosas
class Builder {

	public function __construct() {
		throw new Exception("This class cannot be instanciated");
	}

	static private function lookup($what) {
		$resources['VoDFactory']='vod/VoDFactory.php';
		$res=$resources[$what];
		$ipath=dirname(__FILE__) . '/../' . $res;
		if(!is_readable($ipath) || is_dir($ipath)) throw new ModuleNotFoundException($ipath);
		else require_once($ipath);
	}

	///Intenta construir el objeto solicitado
	static public function build($what) {
		self::lookup($what);
	}

}


?>