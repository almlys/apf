<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/../base/simplepage.php");

///Clase página del gestor
class ApfFileSystemRPC extends ApfSimplePage implements iDocument {
	private $type;

	function __construct() {
		parent::__construct('',False);
		if(!$this->IAmAuthenticated() || !$this->IAmAdmin()) {
			die('AuthFailed');
		}
		$type=$this->escape_string($_GET['type']);
		if(empty($type)) $cmd='video';
		$this->type=$type;
		$path=$_GET['path'];
		if(get_magic_quotes_gpc()) {
			$path=stripslashes($file);
		}
	}

	function show() {
		$this->process();
	}

	function process() {
		echo("Funny");
	}

}

?>