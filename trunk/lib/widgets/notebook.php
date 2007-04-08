<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

///Clase página de un libro
class ApfNoteBookTab {
	public $name;
	public $content;

	///Constructor
	///@param name Nombre de la página
	///@param content Contenido de la página
	function __construct($name,$content) {
		$this->name=$name;
		$this->content=$content;
	}
}

///Clase NoteBook (Un conjunto de páginas agrupadas)
class ApfNoteBook {
	private $name=""; ///<! Nombre del NoteBook
	private $pages=array();

	///Constructor
	///@param name Nombre del NoteBook
	function __construct($name='') {
		if(empty($name)) {
			$name='NoteBook_' . md5(rand() . uniqid());
		}
		$this->name=$name;
	}

	///Añade una nueva página
	///@param name Nombre de la página
	///@param content Contenido de la página
	function AddPage($name,$content) {
		$page = new ApfNoteBookTab($name,$content);
		$this->pages[] = &$page;
	}

	///Escribe el notebook con todos las páginas especificadas
	function Write() {
		echo($this->Get());
	}

	///Obtiene el notebook
	function Get() {
		$script = <<<SAFE_SCRIPT
<script language="JavaScript" type="text/javascript">
//<![CDATA[

//Este código esta inspirado en el utilizado en MediaWiki en la página de preferencias
var current_tab_{$this->name}=0;

function hide_tabs_{$this->name}() {
	var sth = document.getElementById("{$this->name}");
	var nodes = sth.childNodes;
	//var count=0;
	var i=0;
	for (i = 0; i < nodes.length; i++) {
		var node = nodes[i];
		if (node.nodeName.toLowerCase()=="fieldset") {
			var leg=node.getElementsByTagName("legend");
			//leg[0].firstChild.nodeValue = "";
			leg[0].style.display = "none";
			//leg[0].firstChild.node.style.display = "none";
			//node.id="sth" + count;
			node.style.display = "none";
			//count++;
		}
	}
}

function show_tab_{$this->name}(num) {
	var wopt = document.getElementById("{$this->name}_" + num);
	wopt.style.display = "block";
	var aopt = document.getElementById("{$this->name}_a" + num);
	aopt.className='notebook_tabname_selected';
	if (num!=current_tab_{$this->name}) {
		wopt = document.getElementById("{$this->name}_" + current_tab_{$this->name});
		wopt.style.display = "none";
		aopt = document.getElementById("{$this->name}_a" + current_tab_{$this->name});
		aopt.className='notebook_tabname';
	}
	current_tab_{$this->name}=num;
}
//]]>
</script>
<div class='notebook' id="{$this->name}">
SAFE_SCRIPT;

$cnt=0;
foreach ($this->pages as $page) {
	$id1=$this->name . "_a" . $cnt;
	//$id2=$this->name . "_m" . $cnt;
	$script.="<a id='$id1' class='notebook_tabname' href='javascript:;' onclick=\"show_tab_{$this->name}($cnt)\">{$page->name}</a>\n";
	$cnt++;
}

$cnt=0;
foreach ($this->pages as $page) {
	$id=$this->name . "_" . $cnt;
	//$id3=$this->name . "_a" . $cnt;
	//echo("<a name=\"$id3\"></a>");
	$script.="<fieldset class='notebook_tab' id=\"$id\">\n<legend>{$page->name}</legend>\n{$page->content}\n</fieldset>\n";
	$cnt++;
}

$script .=<<<SAFE_SCRIPT
</div>
<script language="JavaScript" type="text/javascript">
	hide_tabs_{$this->name}();
	show_tab_{$this->name}(0);
</script>
SAFE_SCRIPT;
		return $script;
	}

} //End Class ApfNoteBook


?>