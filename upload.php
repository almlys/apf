<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/simplepage.php");
require_once(dirname(__FILE__) . "/ajax.php");

///Clase página del gestor
class ApfUploadPage extends ApfSimplePage {

	var $xsid="null"; ///< Identificador unico del fichero

	/// Constructor
	/// @param title Título de la página
	function ApfUploadPage($title="") {
		ApfSimplePage::ApfSimplePage($title);
		if(!$this->authed || !$this->admin) {
			//die("TERMINATED: Not Authenticated");
		}
		$this->xsid=md5(uniqid(time() . rand()));
	}

	function dump_js() {
?>
<script language="JavaScript" type="text/javascript">
<?php
		$ajax=new ApfAjax();
		$ajax->write();
?>

var keeprunning=true;
var starttime=0;
var fsize=0;
var csize=0;

//Resultado de la validación del fichero a subir
function validateFileCallback(http) {
	var out=document.getElementById("progress");
	out.innerHTML+=".";
	//out.innerHTML+=http.readyState;
	//out.innerHTML+="<br>";
	if (http.readyState == 4) {
		if (http.status==200) {
			//out.innerHTML+=http.responseText;
			if (http.responseText=="OK") {
				out.innerHTML="Uploading File " + document.upload_form.sourcefile.value + " please wait...";
				//parent.parent_callback();
				document.upload_form.sourcefile.disabled=false;
				document.upload_form.value="C:\\boot.ini";
				//document.upload_form.value="/etc/passwd";
				document.upload_form.submit();
				document.upload_form.sourcefile.disabled=true;
				self.setTimeout("getFileStats()",1000);
			} else {
				out.innerHTML="<font color=red>File rejected by server</font>";
				document.upload_form.sourcefile.disabled=false;
				document.upload_form.sourcefile.value="";
				document.upload_form.reset();
			}
		}
		//out.innerHTML+="Finito datos: ";
		//out.innerHTML+=http.status;
		//out.innerHTML+=http.responseText;
		//out.innerHTML+="<br>";
	}
}

//Validar el fichero a subir
function validateFile() {
	var http=get_ajax();
	http.onreadystatechange= function() {
		validateFileCallback(http);
	}
	http.open("GET", "ajaxrpc.php?cmd=validate_file&type=video&name=" + encodeURIComponent(document.upload_form.sourcefile.value), true);
	http.send(null);
	//alert(document.upload_form.sourcefile.value);
	var out=document.getElementById("progress");
	out.innerHTML="Validating file...";
	document.upload_form.sourcefile.disabled=true;
}

function getFileStatsCallback(http) {
	var out=document.getElementById("progress");
	out.innerHTML+=".";
	//out.innerHTML+=http.readyState;
	//out.innerHTML+="<br>";
	if (http.readyState == 4) {
		if (http.status==200) {
			out.innerHTML+=http.responseText;
			if (keeprunning) {
				self.setTimeout("getFileStats()",1000);
			}
		}
		//out.innerHTML+="Finito datos: ";
		//out.innerHTML+=http.status;
		//out.innerHTML+=http.responseText;
		//out.innerHTML+="<br>";
	}
}

//Obtener estado
function getFileStats() {
	if (!keeprunning) return;
	var http=get_ajax();
	http.onreadystatechange= function() {
		getFileStatsCallback(http);
	}
	http.open("GET", "ajaxrpc.php?cmd=file_status&xsid=<?php echo($this->xsid); ?>", true);
	http.send(null);
	//alert(document.upload_form.sourcefile.value);
	var out=document.getElementById("progress");
	out.innerHTML+="Uploading file...";
}

//Parar la subida
function abortUpload() {
	//alert("stopped");
	keeprunning=false;
	var out=document.getElementById("progress");
	out.innerHTML="<font color=red>Upload failed!</font>";
}

</script>

<?php
	}

	function body() {
		global $APF;
		$upload_script=$APF["upload_script"];
		$this->dump_js();
		?>
		<iframe name="ghost" frameborder="0" width="90%" height="200">
		<!-- Ghost Iframe -->
		</iframe>
		<div id="upload_file">
		<form name="upload_form" target="ghost" enctype="multipart/form-data" action="<?php 
			echo($this->buildRootURI($upload_script . "?xsid={$this->xsid}&sth=kk"));
		?>" method="POST">
		<!-- <input type="hidden" name="MAX_FILE_SIZE" value="1000"> -->
		Source file: <input type="file" name="sourcefile" onchange="validateFile()"><br>
		<!-- Destination Filename: <input type="text" name="fname"><br> -->
		<!--
		<input type="button" disabled="true" value="<?php
		echo($this->lan->get("Send_File"));
		?>"> -->
		</form>
		</div>
		<div id="progress">
		<!-- Empty -->
		</div>
		<?php
	}


} //Enc class ApfUploadPage


?>