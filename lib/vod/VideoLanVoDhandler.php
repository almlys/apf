<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/baseVoDhandler.php");

///Implementa los manejadores necesarios para un servidor VoD VideoLan
class ApfVideoLanVoDHandler extends ApfBaseVoDHandler implements iVoDHandler {

/*
	function UploadVideoFile($path,$name) {
		$result=parent::UploadVideoFile($path,$name);
		//echo("Path: $path, name: $name");
		//throw new InvalidFileException($name);
		return $result;
	}*/

	function notifyNewUploadedVideo($name,$path) {
		//Notificar al VoD
		require_once(dirname(__FILE__) . '/VideoLanTelnetClient.php');
		$cli = new VideoLanTelnetClient('localhost',4212,'admin');
		$cli->addVodResource($name,$path);
	}

} //End Class ApfVideoLanVodHandler


?>