<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . '/../net/simpletcpclient.php');

///Excepción de contraseña incorrecta
class IncorrectPasswordException extends Exception {}

///Cliente VideoLAN
class VideoLanTelnetClient extends SimpleTcpClient {

	///Constructor
	///@param addr Dirección
	///@param port Puerto
	///@param passwd Contraseña
	function __construct($addr,$port,$passwd) {
		parent::__construct($addr,$port);
		$this->expect('Password:');
		$this->write("$passwd\n");
		if(!$this->expect('Welcome, Master',True)) throw new IncorrectPasswordException();
		$this->expect('>');
	}

	///Añadir recurso
	///@param name Nombre
	///@param path Ruta
	function addVodResource($name,$path) {
		$msg1="new $name vod enabled\n";
		$msg2="setup $name input \"$path\"\n";
		$this->write($msg1);
		$this->expect('>');
		//sleep(1);
		$this->write($msg2);
		$this->expect('>');
	}

	///Eliminar recurso
	///@param name Nombre
	function removeVodResource($name) {
		$msg1="del $name\n";
		$this->write($msg1);
		$this->expect('>');
	}


	///Destructor
	function __destruct() {
		$this->write('exit');
		parent::__destruct();
	}
}


?>