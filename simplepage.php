<?php
/*
  Copyright (c) 2005-2006 Alberto Monta�ola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/lib/main.php");

///Clase base para p�ginas simples
class ApfSimplePage extends ApfDocument {

	/// Constructor
	/// @param title T�tulo de la p�gina
	function ApfUploadPage($title="") {
		ApfDocument::Apfdocument($title);
	}

	/// M�todo cuerpo
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