<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/../util.php");

///Interface que define un fichero Log
interface iLogger {
	///Escribe al fichero log
	///@param msg Mensaje a escribir
	public function write($msg);
	///Cierra el fichero log
	public function close();
}

class Logger implements iLogger {
	private $file;
	public function __construct($out) {
		if(is_writable(dirname($out))) {
			$this->file=fopen($out,"a");
		}
	}
	public function __destruct() {
		$this->close();
	}
	public function write($msg) {
		if(!empty($this->file)) {
			fwrite($this->file,date("d/m/Y H:i:s") . "> " . $msg . "\n");
		}
	}
	public function writeReq($msg) {
		$uri=$_SERVER["REQUEST_URI"];
		$agent=$_SERVER["HTTP_USER_AGENT"];
		$uri=urldecode($uri);
		$uri=html_entity_decode($uri);
		$remote=Request::getRemoteAddress();
		$this->write("REQ: \"$uri\" FROM: \"$remote\" \"$agent\" > $msg");
	}
	public function close() {
		if(!empty($this->file)) {
			fclose($this->file);
		}
	}
}

?>