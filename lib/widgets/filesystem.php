<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

///Interfaz para trabajar sobre sistemas de ficheros, reales, virtuales, etc...
interface iFileController {
	
}


///Clase de gestion de ficheros
class ApfFileSystem {
	private $name;
	private $parent;
	private $type;

	///Constructor
	function __construct($parent,$name='',$type='video') {
		$this->parent=$parent;
		if(empty($name)) {
			$name='FileSystem_' . md5(rand() . uniqid());
		}
		$this->name=$name;
		$this->type=$type;
	}
	
	function get() {
		$out.=<<<EOF
<script language="JavaScript" type="text/javascript">
//<![CDATA[
EOF;
		require_once(dirname(__FILE__) . '/ajax.php');
		$out.=ApfAjax::get();

		$rpcserver=$this->parent->buildBaseURI("?page=fsrpc&",false);

		$out.=<<<EOF

function {$this->name}_dorequestCallback(http) {
	var out=document.getElementById("{$this->name}");
	if (http.readyState == 4) {
		if (http.status==200) {
			out.innerHTML=http.responseText;
		}
	}
}

//Habilitar el upload
function {$this->name}_dorequest(req) {
	var http=get_ajax();
	var rpcserver="$rpcserver";
	http.onreadystatechange= function() {
		{$this->name}_dorequestCallback(http);
	}
	http.open("GET", rpcserver + "name={$this->name}&type={$this->type}&" +  req, true);
	http.send(null);	
}

{$this->name}_dorequest("initial")

EOF;

		$out.='
//]]>
</script>';
		$out.="<div id='{$this->name}' class='filesystem'>
</div>
";
		return $out;
	}

}


?>