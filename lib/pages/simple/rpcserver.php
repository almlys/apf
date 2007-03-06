<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/../base/simplepage.php");

///Clase página del gestor
class ApfRPCServer extends ApfSimplePage implements iDocument {
	private $cmd;
	private $xsid;

	function __construct() {
		parent::__construct('',False);
		//Comando a procesar
		$cmd=$this->escape_string($_GET['cmd']);
		if(empty($cmd)) die("ERROR");
		$this->cmd=$cmd;
	}

	function show() {
		$this->process();
	}

	function AuthCheck($xsid_check=True,$file_check=False) {
		if(!$this->IAmAuthenticated() || !$this->IAmAdmin()) {
			return false;
		}
		$this->xsid=$xsid=$_GET["xsid"];
		if($xsid_check && $xsid!=$_SESSION["xsid"]) {
			return false;
		}
		if($file_check) {
			$type=$_GET['type'];
			$file=$_GET['name'];
			if($type!=$_SESSION['file_type'] || $file!=$_SESSION['file_name']) {
				return false;
			}
		}
		return true;
	}

	function process() {
		global $APF;
		//Procesar comando RPC recibido
		switch($this->cmd) {
			//Validar un fichero que va ser subido
			case "validate_file":
				if(!$this->AuthCheck(False,False)) {
					echo('AUTHFAIL');
					break;
				}
				$type=$_GET['type'];
				$file=$_GET['name'];
				$_SESSION['file_type']=$type;
				$_SESSION['file_name']=$file;
				if(get_magic_quotes_gpc()) {
					$file=stripslashes($file);
				}
				$file=str_replace("\\","/",$file);
				$file=basename($file);
				switch($type) {
					case "video":
						//Instanciate APF_VOD class
						require_once(dirname(__FILE__) . '/../../vod/VoDFactory.php');
						$vod_server=VoDFactory::getVoDHandler('http');
						if($vod_server->CheckVideoFileBeforeUpload($file)) {
							echo("OK");
						} else {
							echo("INVALID");
						}
						break;
					case "img":
						$ext=substr($file,-4);
						$check=array('.png','.jpg','.gif');
						if(in_array($ext,$check)) {
							echo('OK');
						} else {
							echo('INVALID');
						}
						break;
					default:
						echo("ERROR");
						break;
				}
				break;
			//Obtener tamaño del fichero
			case "file_size":
				if($this->AuthCheck()) {
					$xsid=$this->xsid;
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
				if($this->AuthCheck()) {
					$xsid=$this->xsid;
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
				if($this->AuthCheck(True,True)) {
					$xsid=$this->xsid;
					$path=$APF['upload_dir'] . "/" . $xsid . "/upload.raw";
					if(is_readable($path) && filesize($path)!=0) {
						//Do It!
						//
						echo("OK");
					} else {
						echo("ERROR");
					}
				} else {
					echo('AUTHFAIL');
				}
				break;
			case 'regenerate_xsid':
				if(!$this->AuthCheck(False,False)) {
					echo('AUTHFAIL');
					break;
				}
				$xsid=md5(uniqid(time() . rand()));
				$_SESSION["xsid"]=$xsid;
				echo($xsid);
				break;
			//Verificar que el hash the autenticación es válido
			case "auth_verify":
				$hash=$_GET['hash'];
				$uid=intval($_GET['uid']);
				if($this->checkAuthData($hash,$uid) && $this->IAmAdmin()) {
					echo("OK");
				} else {
					echo("AUTHFAIL");
				}
				break;
			default:
				echo("ERROR");
				break;
		}
	}
}

?>