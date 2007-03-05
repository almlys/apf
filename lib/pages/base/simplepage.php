<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/Document.php");

///Clase base para páginas simples
class ApfSimplePage extends ApfDocument implements iDocument {

	/// Constructor
	/// @param title Título de la página
	/// @param release_session Indica si debemos liberar la sessión
	function __construct($title='',$release_session=True) {
		parent::__construct($title,$release_session);
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

} //Enc class ApfSimplePage


?>