<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

///Clase página de un libro
class ApfNoteBookTab {
	var $name;
	var $content;
	///Constructor
	///@param name Nombre de la página
	///@param content Contenido de la página
	function ApfNoteBookTab($name,$content) {
		$this->name=$name;
		$this->content=$content;
	}
}

///Clase NoteBook (Un conjunto de páginas agrupadas)
class ApfNoteBook {
	var $name=""; ///<! Nombre del NoteBook
	var $pages=array();

	///Constructor
	///@param name Nombre del NoteBook
	function ApfNoteBook($name="") {
		if(empty($name)) {
			$name="NoteBook_" . md5(rand() . uniqid());
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
?>
<script language="JavaScript" type="text/javascript">

//Este código esta inspirado en el utilizado en MediaWiki en la página de preferencias
var current_tab_<?php echo($this->name); ?>=0;

function hide_tabs_<?php echo($this->name); ?>() {
	var sth = document.getElementById("<?php echo($this->name); ?>");
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

function show_tab_<?php echo($this->name); ?>(num) {
	var wopt = document.getElementById("<?php echo($this->name) ?>_" + num);
	wopt.style.display = "block";
	if (num!=current_tab_<?php echo($this->name); ?>) {
		wopt = document.getElementById("<?php echo($this->name) ?>_" + current_tab_<?php echo($this->name); ?>);
		wopt.style.display = "none";
	}
	current_tab_<?php echo($this->name); ?>=num;
}
</script>
<div id="<?php echo($this->name); ?>">
<?php

$cnt=0;
foreach ($this->pages as $page) {
	$id1=$this->name . "_a" . $cnt;
	//$id2=$this->name . "_m" . $cnt;
	echo("<a href=#$id1 onClick=\"show_tab_{$this->name}($cnt)\">{$page->name}</a>\n");
	$cnt++;
}

$cnt=0;
foreach ($this->pages as $page) {
	$id=$this->name . "_" . $cnt;
	$id3=$this->name . "_a" . $cnt;
	echo("<a name=\"$id3\"></a>");
	echo("<fieldset id=\"$id\">\n<legend>{$page->name}</legend>\n{$page->content}\n</fieldset>\n");
	$cnt++;
}

?>
</div>
<script language="JavaScript" type="text/javascript">
	hide_tabs_<?php echo($this->name); ?>();
	show_tab_<?php echo($this->name); ?>(0);
</script>
<?php
	}

} //End Class ApfNoteBook


?>
<html>
<head><TITLE>test</TITLE>
</head>
<BODY>

<script language="JavaScript" type="text/javascript">
function parent_callback() {
	alert("parent callback");
}
</script>

<?php

$book = new ApfNoteBook("Opts");
$book->AddPage("Upload",'
<iframe name="upload" src="iframe.php?page=upload" frameborder="0" width="100%" height="300">
Sorry, Your browser does not support the iframe tag, and it does not meet the minimal requirements for this application.
</iframe>
');
$book->AddPage("Something else",'
Another fieldset
<table><TR><TD>a table</TD></TR><tr><TD>la , la, la la</TD></tr></table>
<hr>
Ooossp
');
$book->AddPage("Another page",'
The content of the page
');
$book->AddPage("WEEEEEEEEEEEEEEEEEE",'
stha
faf
fs<br>
afjkaf
saf
');
$book->Write();

?>

</BODY>
</html>