<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

class CannotConnectExcpetion extends Exception {}
class ProtocolProblemExcpetion extends Exception {}


/// Cliente Tcp sencillo
class SimpleTcpClient {
	private $res;
	private $buffer=array();

	/// Constructor
	/// @param destination Dirección de máquina
	/// @param port Puerto
	function __construct($destination,$port) {
		$this->res=fsockopen($destination,$port,$errno,$errstr);
		if(!$this->res) throw new CannotConnectExcpetion("$errstr ($errno)");
		
	}

	function expect($what,$throw=True) {
		$prompt='';
		while(empty($prompt)) {
			$prompt=trim($this->read(256));
			$prompt=trim($prompt,"\xff\xfc\x01\x0d\x0a");
		}
		//echo("PROMPT:---$prompt---\n");
		if(substr($prompt,0,strlen($what))==$what) {
			return True;
		} elseif($throw) {
			//print_r($this->buffer);
			throw new ProtocolProblemExcpetion("Got $prompt instead of $what");
		} else {
			return False;
		}
	}

	function write($msg) {
		//echo("SEND:$msg<-\n");
		fwrite($this->res,$msg);
	}

	function read($size) {
		if(count($this->buffer)!=0) {
			return array_shift($this->buffer);
		} elseif(!feof($this->res)) {
			$x=fread($this->res,$size);
			//echo("XX:####$x####\n");
			$this->buffer=explode("\n",$x);
			$x=array_shift($this->buffer);
			//echo("READ:----$x----\n");
			return $x; 
		}
		return '';
	}

	function __destruct() {
		fclose($this->res);
	}
}

?>