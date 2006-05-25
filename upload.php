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
	var $resource_type="video"; ///< Indica el tipo de recurso (video, img, etc...)

	/// Constructor
	/// @param title Título de la página
	/// @param resource_type Tipo de recurso a subir (video,imagen,etc...)
	function ApfUploadPage($title="",$resource_type="video") {
		$this->resource_type=$resource_type;
		ApfSimplePage::ApfSimplePage($title);
		if(!$this->authed || !$this->admin) {
			die("TERMINATED: Not Authenticated");
		}
		$this->xsid=md5(uniqid(time() . rand()));
		$_SESSION["xsid"]=$this->xsid;
	}

	function dump_js() {
?>
<script language="JavaScript" type="text/javascript">
<?php
		$ajax=new ApfAjax();
		$ajax->write();
?>

var keeprunning=true;
var starttime=0; //start time
var fsize=0; //file size
var csize=0; //current file size
var count=0; //numero de veces a esperar hasta que el fichero este disponible

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
				//document.upload_form.sourcefile.value="C:\\boot.ini";
				//document.upload_form.sourcefile.value="/etc/passwd";
				document.upload_form.submit();
				document.upload_form.sourcefile.disabled=true;
				self.setTimeout("getFileStats()",1000);
			} else {
				out.innerHTML="<font color=red>File rejected by server</font>";
				out.innerHTML+=http.responseText;
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
	http.open("GET", "ajaxrpc.php?cmd=validate_file&type=<?php echo($this->resource_type); ?>&name=" + encodeURIComponent(document.upload_form.sourcefile.value), true);
	http.send(null);
	//alert(document.upload_form.sourcefile.value);
	var out=document.getElementById("progress");
	out.innerHTML="Validating file...";
	document.upload_form.sourcefile.disabled=true;
}

function getFileStatsCallback(http) {
	var out=document.getElementById("progress");
	//out.innerHTML+=".";
	//out.innerHTML+=http.readyState;
	//out.innerHTML+="<br>";
	if (http.readyState == 4) {
		if (http.status==200) {
			//out.innerHTML+=http.responseText;
			if (fsize==0) {
				fsize=parseInt(http.responseText);
				if(fsize==NaN || fsize==-1) {
					abortUpload("No size was recieved in the last RPC call");
				} else {
					starttime=new Date();
				}
			} else {
				csize=parseInt(http.responseText);
				if(csize==NaN || csize==-1 || (csize==0 && count>2)) {
					abortUpload("No partial size was recieved in the last RPC call");
				} else {
					if(csize==0) count=count+1;
					else count=100;
					updateUploadStats();
				}
			}
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
	if(fsize==0) {
		http.open("GET", "ajaxrpc.php?cmd=file_size&xsid=<?php echo($this->xsid); ?>", true);
	} else {
		http.open("GET", "ajaxrpc.php?cmd=file_status&xsid=<?php echo($this->xsid); ?>", true);
	}
	http.send(null);
	//alert(document.upload_form.sourcefile.value);
	if(fsize==0) {
		var out=document.getElementById("progress");
		out.innerHTML="Uploading file...";
	} else {
		updateUploadStats();
	}
}

function updateUploadStats() {
	var elapsed=(new Date() - starttime)/1000;
	var speed=(csize/1000) / elapsed;
	var remain=((fsize-csize)/1000)/speed;
	var out=document.getElementById("progress");
	var percent=(csize/fsize)*100;
	var pg=(self.innerWidth-40)*(percent/100);
	out.innerHTML="Uploading file...<br>";
	out.innerHTML+="Total Size: " + fsize + "<br>";
	out.innerHTML+="Uploaded Size: " + csize + "<br>";
	out.innerHTML+="Elapsed time: " + elapsed + " seconds<br>";
	out.innerHTML+="Remaining time: " + remain + " seconds<br>";
	out.innerHTML+="Speed: " + speed + " KB/s<br>";
	out.innerHTML+="Percent: " + percent + "%<br>";
	out.innerHTML+="Width: " + pg + "<br>";
	out.innerHTML+="<img width=\"" + pg + "\" height=\"10\" src=\"imgs/progress_point.png\">";
}

//Parar la subida
function abortUpload(msg) {
	//alert("stopped");
	keeprunning=false;
	var out=document.getElementById("progress");
	out.innerHTML="<font color=red>Upload failed!</font>: " + msg;
	self.setTimeout("enableUpload()",1000);
}

//Finalizar la subida
function finishUpload() {
	keeprunning=false;
	var out=document.getElementById("progress");
	out.innerHTML="<font color=green>Upload Finished</font>";
	self.setTimeout("enableUpload()",1000);
}

//Habilitar el upload
function enableUpload() {
	keeprunning=true;
	fsize=0;
	csize=0;
	count=0;
	document.upload_form.sourcefile.disabled=false;
	document.upload_form.sourcefile.value="";
	document.upload_form.reset();
}

</script>
<?php
	}

	function body() {
		global $APF;
		$upload_script=$APF["upload_script"];
		$this->dump_js();
		?>
		<iframe name="ghost" frameborder="0" width="90%" height="0">
		<!-- Ghost Iframe -->
		</iframe>
		<div id="upload_file">
		<form name="upload_form" target="ghost" enctype="multipart/form-data" action="<?php 
			echo($this->buildRootURI($upload_script . "?xsid={$this->xsid}&uid={$this->uid}&type={$this->resource_type}"));
		?>" method="POST">
		<!-- <input type="hidden" name="MAX_FILE_SIZE" value="1000"> -->
		Source file: <input type="file" name="sourcefile" onchange="validateFile()"><br>
		<!-- Destination Filename: <input type="text" name="fname"><br> -->
		<!-- <input type="button" onclick="validateFile()" value="<?php
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