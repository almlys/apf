<?php
/*
  Copyright (c) 2005-2006 Alberto Monta�ola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/lib/main.php");

///Clase p�gina del gestor
class ApfUploadPage extends ApfDocument {

	/// Constructor
	/// @param title T�tulo de la p�gina
	function ApfUploadPage($title="") {
		//Noop
	}

	function body() {
		?>
		<form enctype="multipart/form-data" action="uploadfile.php" method="POST">
		<input type="hidden" name="MAX_FILE_SIZE" value="1000">
		Source file: <input type="file" name="sourcefile"><br>
		Destination Filename: <input type="text" name="fname"><br>
		<input type="submit" value="Send File">
		</form>
		<?php
	}

	function show() {
		$this->head();
		$this->body();
		$this->foot();
	}


} //Enc class ApfUploadPage


?>