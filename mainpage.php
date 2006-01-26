<?php

include_once(dirname(__FILE__) . "/manager.php"); 

class ApfMainPage extends ApfManager {
	//Contructor
	function ApfMainPage() {
		$this->ApfManager("");
		$this->setTitle($this->lan->get(main_page));
	}
	
	//Method body - override parent class method
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