<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/lib/main.php");

///Clase base para páginas simples
class ApfSimplePage extends ApfDocument {

	/// Constructor
	/// @param title Título de la página
	function ApfUploadPage($title="") {
		ApfDocument::Apfdocument($title);
	}

	/// Método cuerpo
	function body() {
		echo("Empty");
	}

	/// Muestra el documento
	function show() {
		$this->head();
		$this->body();
		$this->foot();
	}

} //Enc class ApfUploadPage


?>