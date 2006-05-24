<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//Comando a procesar
$cmd=$_GET['cmd'];

if(empty($cmd)) die("ERROR");

//Cargar la configuración de la aplicación
require_once(dirname(__FILE__) . "/DefaultConfig.php");

//Procesar comando RPC recibido
switch($cmd) {
	//Validar un fichero que va ser subido
	case "validate_file":
		$type=$_GET['type'];
		$file=$_GET['name'];
		if(get_magic_quotes_gpc()) {
			$file=stripslashes($file);
		}
		$file=str_replace("\\","/",$file);
		$file=basename($file);
		switch($type) {
			case "video":
				//Instanciate APF_VOD class
				require_once(dirname(__FILE__) . "/" . $APF["vod.plug"]);
				if($APF_VOD->CheckVideoFileBeforeUpload($file)) {
					echo("OK");
				} else {
					echo("INVALID");
				}
				break;
			case "img":
				echo("UNIMPLEMENTED");
				break;
			default:
				echo("ERROR");
				break;
		}
		break;
	//Verificar que el hash the autenticación es válido
	case "auth_verify":
		$hash=$_GET["hash"];
		break;
	default:
		echo("ERROR");
		break;
}

?>