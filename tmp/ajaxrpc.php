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

//Asegurar que los datos són salvados a lo largo de la sesión
session_name("ApfVoDPHPSID");
session_start();

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
				$vod_server=createApfVoDHandler();
				if($vod_server->CheckVideoFileBeforeUpload($file)) {
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
	//Obtener tamaño del fichero
	case "file_size":
		$xsid=$_GET["xsid"];
		if($xsid==$_SESSION["xsid"]) {
			$path=$APF['upload_dir'] . "/" . $xsid . "/lenght.txt";
			if(is_readable($path)) {
				$f=fopen($path,"r");
				echo(fread($f,filesize($path)));
				fclose($f);
			} else {
				echo("0");
			}
			//echo("XSID IS OK");
		} else {
			echo("-1");
		}
		break;
	//Obtener tamaño subido
	case "file_status":
		$xsid=$_GET["xsid"];
		if($xsid==$_SESSION["xsid"]) {
			$path=$APF['upload_dir'] . "/" . $xsid . "/upload.raw";
			if(is_readable($path)) {
				echo(filesize($path));
			} else {
				echo("0");
			}
		} else {
			echo("-1");
		}
		break;
	//Notificar al Servidor VoD que hemos subido un nuevo fichero
	case "file_notify":
		$xsid=$_GET["xsid"];
		$path=$APF['upload_dir'] . "/" . $xsid . "/upload.raw";
		if($xsid==$_SESSION["xsid"] && is_readable($path) && filesize($path)!=0) {
			//Do It!
			echo("OK");
		} else {
			echo("ERROR");
		}
		break;
	//Verificar que el hash the autenticación es válido
	case "auth_verify":
		$hash=$_GET["hash"];
		$uid=$_GET["uid"];
		require_once(dirname(__FILE__) . "/lib/" . $APF["db.plug"]);
		require_once(dirname(__FILE__) . "/lib/" . $APF["auth.plug"]);
		$DB=createDBObject($APF['db.user'],$APF['db.passwd'],$APF['db.name'],$APF['db.host']);
		$auth=createAuthObject(&$DB);
		if($auth->verify($uid,$hash) && $auth->level>0) {
			echo("OK");
		} else {
			echo("AUTHFAIL");
		}
		break;
	default:
		echo("ERROR");
		break;
}

?>