<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

///Carpeta o directorio (Nodo) del sistema virtual de ficheros.
class ApfFolder {
	protected $id;
	protected $name;
	protected $desc;
	protected $count;
	protected $parent;
	protected $image;

	/// Constructor
	/// @param parent Página padre
	/// @param vals Array con las claves id, name, desc, count, etc...
	function __construct(&$parent,$vals) {
		$this->parent=&$parent;
		$this->id=$vals['id'];
		$this->name=$vals['name'];
		$this->desc=$vals['desc'];
		$this->count=$vals['count'];
		$this->image=$parent->buildBaseUri("imgs/folder.png");
	}

	///Obtener URL carpeta.
	function getFinalUrl() {
		return $this->parent->getArgs(array('id' => $this->id));
	}

	function getShortName($input,$max) {
		if(strlen($input)>$max) {
			$shortname=substr($input,0,$max-3) . "...";
		} else {
			$shortname=$input;
		}
		return $shortname;
	}


	///Mostrar la carpeta.
	function show() {
		$shortname=$this->getShortName($this->name,12);
		?>
		<div class="folder" onclick='javascript:document.location="<?php echo($this->getFinalUrl()); ?>"'>
		<div class="folder_title">
		<a title="<?php echo($this->name); ?>" href="<?php echo($this->getFinalUrl()); ?>">
		<?php echo($shortname); ?>
		</a>
		</div>
		<a href="<?php echo($this->getFinalUrl()); ?>">
		<img title="<?php echo($this->name); ?>" alt="<?php echo($this->name); ?>" border="0" src="<?php echo($this->image); ?>" width="160" height="120" /></a>
		<div class="description">
		<?php 
			echo($this->getShortName($this->desc,30));
			$this->details();
		?>
		</div>
		</div>
		<?php
	}
	
	///Mostrar detalles.
	function details() {
			if($this->count>1) {
				echo("<br />" . $this->count . " " . _t("objects")); 
			} elseif($this->count==1) {
				echo("<br />" . $this->count . " " . _t("object")); 
			}
	}

}

///Clase representativa de un video.
class ApfVideo extends ApfFolder {
	protected $dur=0;

	///Constructor
	/// @param parent Página padre
	/// @param vals Array con las claves id, name, desc, count, etc...
	function __construct(&$parent,$vals) {
		global $APF;
		parent::__construct($parent,$vals);
		if(empty($vals['prev'])) {
			$this->image=$parent->buildBaseUri("imgs/video_big.png");
		} else {
			$this->image=$parent->buildBaseUri($APF['upload.imgs'] . "/" . $vals['prev']);
		}
		$this->dur=$vals['dur'];
	}
	
	///Obtener URL
	function getFinalUrl() {
		return $this->parent->getArgs(array('page' => 'videos','id' => $this->id));
	}
	
	///Mostrar detalles.
	function details() {
		
	}

}

?>