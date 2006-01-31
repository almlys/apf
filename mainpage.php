<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

include_once(dirname(__FILE__) . "/manager.php"); 

///Página principal
class ApfMainPage extends ApfManager {
	///Constructor
	function ApfMainPage() {
		$this->ApfManager("");
		$this->setTitle($this->lan->get(main_page));
	}
	
	///Método cuerpo
	function body() {
		$lan=$this->lan->getDefaultLanguage();
		$query='select value from vid_cfg where `key`="intro_' . $lan . '"';
		//echo($query);
		$this->query($query);
		$vals=$this->fetchArray();
		echo($vals[0]);
	}
}

?>