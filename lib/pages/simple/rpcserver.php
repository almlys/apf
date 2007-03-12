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
	private $vod_type;
	private $type;
	private $file;

	function __construct() {
		global $APF;
		parent::__construct('',False);
		//Comando a procesar
		$cmd=$this->escape_string($_GET['cmd']);
		if(empty($cmd)) die("ERROR");
		$this->cmd=$cmd;
		$this->vod_type=$APF['default_vod'];
	}

	///Mostrar página
	function show() {
		try {
			$this->process();
		} catch(Exception $e) {
			echo('UNHANDLED_EXCEPTION');
			print_exception($e);
		}
	}

	///Verificar autenticación
	/// @param xsid_check VErificar xsid
	/// @param file_check verificar nombre fichero
	function AuthCheck($xsid_check=True,$file_check=False) {
		if(!$this->IAmAuthenticated() || !$this->IAmAdmin()) {
			return false;
		}
		$this->xsid=$xsid=$_GET["xsid"];
		if($xsid_check && $xsid!=$_SESSION["xsid"]) {
			return false;
		}
		if($file_check) {
			$this->type=$type=$_GET['type'];
			$this->file=$file=$_GET['name'];
			if($type!=$_SESSION['file_type'] || $file!=$_SESSION['file_name']) {
				return false;
			}
		}
		return true;
	}

	/// Obtener Manejador VoD
	/// @returns El manejador VoD solicitado
	function getVodServer() {
		//Instanciate APF_VOD class
		require_once(dirname(__FILE__) . '/../../vod/VoDFactory.php');
		$vod_server=VoDFactory::getVoDHandler($this->vod_type);
		return $vod_server;
	}

	/// Copiar una imagen
	/// @param path ruta del fichero temporal
	/// @param file nombre definido por el usuario
	/// @return Nombre del fichero final
	function copyImage($path,$file) {
		//echo($path . "--" . $file);
		if(!ereg('^[^./][^/]*$',$file)) {
			throw new InvalidFileException($file);
		}
		//echo($path . "--" . $file);
		$file=cleanFileName($file);
		if(empty($file)) {
			throw new InvalidFileException($file);
		}
		//echo($path . "--" . $file);
		global $APF;
		$imgdest=$APF['system.path'] . '/' . $APF['upload.imgs'] . '/' . $file;
		$count=0;
		$imgdestck=$imgdest;
		while(file_exists($imgdestck)) {
			$imgdestck=ereg_replace('\.(.*)$',"_$count.\\1",$imgdest);
			$file=ereg_replace('\.(.*)$',"_$count.\\1",$file);
			$count++;
		}
		$imgdest=$imgdestck;
		//echo($path . "--" . $imgdest);
		if(!rename($path,$imgdest)) {
			throw new InvalidFileException($file);
		}
		return $file;
	}

	/// Processar
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
						$vod_server=$this->getVodServer();
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

			case "file_notify":
				if($this->AuthCheck(True,True)) {
					$xsid=$this->xsid;
					$path=$APF['upload_dir'] . "/" . $xsid . "/upload.raw";

					if(get_magic_quotes_gpc()) {
						$file=stripslashes($this->file);
					}
					$file=str_replace("\\","/",$file);
					$file=basename($file);

					if(is_readable($path) && filesize($path)!=0) {
						switch($this->type) {
							case 'video':
								$_SESSION['video.path']=$path;
								$_SESSION['video.file']=$file;
								$_SESSION['video.ok']=True;
								echo("OK");
								break;
							case 'img':
								$_SESSION['img.path']=$path;
								$_SESSION['img.file']=$file;
								$_SESSION['img.ok']=True;
								echo("OK");
								break;
							default:
								echo('ERROR');
						}
					} else {
						echo("ERROR");
					}
				} else {
					echo('AUTHFAIL');
				}
				break;

			//Notificar al Servidor VoD que hemos subido un nuevo fichero
			case "file_notify_final":
				if($this->AuthCheck(True,True)) {
					$xsid=$this->xsid;
					$path=$APF['upload_dir'] . "/" . $xsid . "/upload.raw";

					if(get_magic_quotes_gpc()) {
						$file=stripslashes($this->file);
					}
					$file=str_replace("\\","/",$file);
					$file=basename($file);

					if(is_readable($path) && filesize($path)!=0) {
						//Do It!
						switch($this->type) {
							case 'video':
								$result=$vod_server=$this->getVodServer()->UploadVideoFile($path,$file);
								$_SESSION['vod_video']=$result;
								echo("OK $result");
								//echo('VOD_SERVER_ERROR');
								break;
							case 'img':
								$result=$this->copyImage($path,$file);
								echo("OK $result");
								//echo('ERROR');
								break;
							default:
								echo('ERROR');
						}
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