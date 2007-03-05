<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

class UploadCtrl {
	private $parent;

	/// Constructor
	/// @param parent Página padre
	function __construct($parent) {
		$this->parent=$parent;
	}

	/// Escribe el control
	function write() {
		echo($this->get());
	}

	/// Obtiene el control
	/// @returns Control generado
	function get() {
		$out=<<<EOF
<iframe name="upload" src="http://pegasus/tfc/?page=iupload" frameborder="0" width="100%" height="300">
Sorry, Your browser does not support the iframe tag, and it does not meet the minimal requirements for this application.
</iframe>
EOF;
		return $out;
	}

}


?>