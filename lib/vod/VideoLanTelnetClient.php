<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . '/../net/simpletcpclient.php');

class IncorrectPasswordException extends Exception {}

class VideoLanTelnetClient extends SimpleTcpClient {

	function __construct($addr,$port,$passwd) {
		parent::__construct($addr,$port);
		$this->expect('Password:');
		$this->write("$passwd\n");
		if(!$this->expect('Welcome, Master',True)) throw new IncorrectPasswordException();
		$this->expect('>');
	}

	function addVodResource($name,$path) {
		$msg1="new $name vod enabled\n";
		$msg2="setup $name input \"$path\"\n";
		$this->write($msg1);
		$this->expect('>');
		$this->write($msg2);
		$this->expect('>');
	}

	function __destruct() {
		$this->write('exit');
		parent::__destruct();
	}
}


?>