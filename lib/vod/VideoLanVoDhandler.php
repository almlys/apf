<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/baseVoDhandler.php");

///Implementa los manejadores necesarios para un servidor VoD VideoLan
class ApfVideoLanVoDHandler extends ApfBaseVoDHandler {

	//Constructor
	//function ApfVideoLanVoDHandler() {}

	function UploadVideoFile($path,$name) {
		echo("Path: $path, name: $name");
	}

} //End Class ApfVideoLanVodHandler


//Crear el objecto
//$APF_VOD=new ApfVideoLanVoDHandler();
function createApfVoDHandler() {
	return new ApfVideoLanVoDHandler();
}

?>