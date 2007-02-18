<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/baseVoDhandler.php");

///Implementa los manejadores necesarios para un servidor VoD sobre HTTP
class ApfHttpVoDHandler extends ApfBaseVoDHandler {

	//Constructor
	//function ApfHttpVoDHandler() {}

	function UploadVideoFile($path,$name) {
		echo("Path: $path, name: $name");
	}

} //End Class ApfHttVodHandler



//$APF_VOD=new ApfHttpVoDHandler();
///Crear el objecto HttpVoDHanler
function createApfVoDHandler() {
	return new ApfHttpVoDHandler();
}

?>