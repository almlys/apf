<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

class UploadCtrl {
	private $parent;
	private $resource_type;
	private $callback;

	/// Constructor
	/// @param parent Página padre
	function __construct($parent,$resource_type='video',$callback='') {
		$this->parent=$parent;
		$this->resource_type=$resource_type;
		$this->callback=$callback;
	}

	/// Escribe el control
	function write() {
		echo($this->get());
	}

	/// Obtiene el control
	/// @returns Control generado
	function get() {
		if(!empty($this->parent)) {
			$addr=$this->buildBaseURI('?page=iupload');
			$msg=_t('unsuported_outdated_old_browser');
		} else {
			$addr='http://pegasus/tfc/?page=iupload';
			$msg='Sorry, Your browser does not support the iframe tag, and it does not meet the minimal requirements for this application.';
		}
		$addr.="&amp;type={$this->resource_type}";
		if(!empty($this->callback)) {
			$addr.="&amp;end_hook={$this->callback}";
		}
		$out=<<<EOF
<iframe name="upload" src="$addr" frameborder="0" width="100%" height="300">
$msg
</iframe>
EOF;
		return $out;
	}

}


?>