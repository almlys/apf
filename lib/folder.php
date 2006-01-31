<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

///Carpeta o directorio (Nodo) del sistema virtual de ficheros.
class ApfFolder {
	var $id;
	var $name;
	var $desc;
	var $count;
	var $parent;
	var $image;

	///Constructor.
	function ApfFolder(&$parent,$id=0,$name="unnamed",$desc="no available desc",$count=0) {
		$this->parent=&$parent;
		$this->id=$id;
		$this->name=$name;
		$this->desc=$desc;
		$this->count=$count;
		$this->image=$parent->buildBaseUri() . "/imgs/folder.jpg";
	}

	///Obtener URL carpeta.
	function getFinalUrl() {
		return $this->parent->getArgs() . "&amp;id=" . $this->id;
	}

	///Mostrar la carpeta.
	function show() {
		$max=12;
		if(strlen($this->name)>$max) {
			$shortname=substr($this->name,0,$max-3) . "...";
		} else {
			$shortname=$this->name;
		}
		?>
		<div class="folder">
		<h2><a title="<?php echo($this->name); ?>" href="<?php echo($this->getFinalUrl()); ?>"><?php echo($shortname); ?></a></h2>
		<a href="<?php echo($this->getFinalUrl()); ?>">
		<img title="<?php echo($this->name); ?>" alt="<?php echo($this->name); ?>" border="0" src="<?php echo($this->image); ?>" width="160" height="120"></a>
		<div class="description">
		<?php 
			echo($this->desc);
			$this->details();
		?>
		</div>
		</div>
		<?php
	}
	
	///Mostrar detalles.
	function details() {
			//if(!empty($this->count)) {
				echo("<br>" . $this->count . " " . $this->parent->lan->get("objects")); 
			//}
	}

}

///Clase representativa de un video.
class ApfVideo extends ApfFolder {
	var $dur=0;
	///Constructor
	function ApfVideo(&$parent,$id=0,$name="unnamed",$desc="no available desc",$prev="",$dur=0) {
		$this->parent=&$parent;
		$this->id=$id;
		$this->name=$name;
		$this->desc=$desc;
		if(empty($prev)) {
			$this->image=$parent->buildBaseUri() . "/imgs/videoimg.jpg";
		} else {
			$this->image=$parent->buildBaseUri() . "/cache/" . $prev;
		}
		$this->dur=$dur;
		$this->count="";
	}
	
	///Obtener URL
	function getFinalUrl() {
		return $this->parent->getArgs("videos") . "&amp;id=" . $this->id;
	}
	
	///Mostrar detalles.
	function details() {
		
	}

}

?>