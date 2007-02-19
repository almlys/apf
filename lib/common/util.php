<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

class Request {

	public function __construct() {
		throw new Exception("This class cannot be instanciated");
	}

	///Muestra la ip del cliente
	///@param how short=solo ip, rshort=ip + x_forward, sino mostrará información completa ip+x_forward+client_ip+via
	public static function getRemoteAddress($how="") {
		if($format=="short") {
			return($_SERVER["REMOTE_ADDR"]);
		} elseif($format=="rshort") {
			if($_SERVER["HTTP_X_FORWARDED_FOR"])
				return($_SERVER["HTTP_X_FORWARDED_FOR"]);
			elseif($_SERVER["HTTP_CLIENT_IP"])
				return($_SERVER["HTTP_CLIENT_IP"]);
			else
				return($_SERVER["REMOTE_ADDR"]);
		} else {
			$ret=$_SERVER["REMOTE_ADDR"];
			$proxy="";
			$extra="";
			if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
				$proxy=" (proxy)";
				$extra=$extra . " x-forwarded-for: <b>" . $_SERVER["HTTP_X_FORWARDED_FOR"] . "</b>";
			}
			if ($_SERVER["HTTP_CLIENT_IP"]) {
				$proxy=" (proxy)";
				$extra=$extra . " client-ip: <b>" . $_SERVER["HTTP_CLIENT_IP"] . "</b>";
			}
			if ($_SERVER["HTTP_VIA"]){
				$proxy=" (proxy)";
				$extra=$extra . " via: <b>" . $_SERVER["HTTP_VIA"] . "</b>";
			}
			$ret=$ret . $proxy . $extra;
			return($ret);
		}
	}

	///Obtiene el protocolo
	public static function getProtocol() {
		return ($HTTP_SERVER_VARS["HTTPS"]=="on" ? "https" : "http");
	}
	
	///Obtiene el puerto
	public static function getPort() {
		return $_SERVER["SERVER_PORT"];
	}

	/// Construye una ruta relativa a la raiz
	/// @param path Ruta
	/// @return Ruta completa
	public static function buildRootURI($path="") {
		$proto=self::getProtocol();
		$port=self::getPort();
		if (($proto=="http" && $port==80) || ($proto=="https" && $port==443)) {
			$port="";
		} else {
			$port=":" . $port;
		}
		$url=$proto . "://"  . $_SERVER['SERVER_NAME'] . $port;
		$path="/" . $path;
		$path=str_replace("//","/",$path);
		$url=$url . $path;
		return $url;
	}

}


?>