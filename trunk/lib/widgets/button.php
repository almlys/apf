<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

/// Un botón
class ApfButton {
	private $value;
	private $img;
	private $target;

	/// Constructor
	/// @param value Valor
	/// @param img Imágen
	/// @param target Destino
	function __construct($parent,$value,$img,$target) {
		$this->parent=$parent;
		$this->value=$value;
		$this->img=$img;
		$this->target=$target;
	}
	
	function write() {
		?>
		<div class='button' onclick='<?php echo($this->target); ?>'>
		<img src='<?php echo($this->parent->buildBaseUri($this->img)); ?>' border='0' />
		<span><?php echo($this->value); ?></span>
		</div>
		<?php
	}

}


?>