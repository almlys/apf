<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/../base/simplepage.php");
require_once(dirname(__FILE__) . "/../../widgets/ajax.php");

///Clase página del gestor
class ApfUploadPage extends ApfSimplePage implements iDocument {
	private $xsid='null'; ///< Identificador unico del fichero
	private $resource_type='video'; ///< Indica el tipo de recurso (video, img, etc...)
	private $end_hook=''; ///< Nombre la función (hook) a llamar al finalizar el upload
	private $uid;

	/// Constructor
	/// @param title Título de la página
	/// @param resource_type Tipo de recurso a subir (video,imagen,etc...)
	function ApfUploadPage($title='',$resource_type='video') {
		$this->resource_type=$resource_type;
		if(empty($title)) $title=_t('upload_file');
		ApfSimplePage::__construct($title,False);
		if(!$this->IAmAuthenticated() || !$this->IAmAdmin()) {
			$this->error_die("TERMINATED: Not Authenticated");
		}
		$this->xsid=md5(uniqid(time() . rand()));
		$_SESSION["xsid"]=$this->xsid;
		if(!empty($_GET["type"])) {
			$this->resource_type=$this->escape_string($_GET["type"]);
		}
		if(!empty($_GET["end_hook"])) {
			$this->end_hook=$this->escape_string($_GET["end_hook"]);
		}
		$this->uid=$this->getUID();
	}

	function dump_js() {
		global $APF;
		$upload_script=$APF['upload_script'];
?>
<script language="JavaScript" type="text/javascript">
//<![CDATA[
<?php
		$ajax=new ApfAjax();
		$ajax->write();
?>

var keeprunning=false;
var starttime=0; //start time
var fsize=0; //file size
var csize=0; //current file size
var count=0; //numero de veces a esperar hasta que el fichero este disponible
var fname=""; //Nombre del fichero
var rpcserver="<?php echo($this->buildBaseURI("?page=rpc&",false)); ?>";
var xsid="<?php echo($this->xsid); ?>";
var resource_type="<?php echo($this->resource_type); ?>";

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
				out.innerHTML="<?php echo(_t('UploadingFile')); ?> " + document.upload_form.sourcefile.value + " <?php echo(_t('PleaseWait')); ?>";
				//parent.parent_callback();
				document.upload_form.sourcefile.disabled=false;
				document.upload_form.submit();
				document.upload_form.sourcefile.disabled=true;
				keeprunning=true;
				self.setTimeout("getFileStats()",1000);
			} else {
				out.innerHTML="<font color=red><?php echo(_t('file_rejected')); ?><" + "/" + "font> ";
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
	fname=document.upload_form.sourcefile.value;
	http.open("GET", rpcserver + "cmd=validate_file&type=" + resource_type + "&name=" + encodeURIComponent(document.upload_form.sourcefile.value), true);
	http.send(null);
	//alert(document.upload_form.sourcefile.value);
	var out=document.getElementById("progress");
	out.innerHTML="<?php echo(_t('ValidatingFile')); ?>";
	document.upload_form.sourcefile.disabled=true;
}

function getFileStatsCallback(http) {
	if (!keeprunning) return;
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
					else count=0;
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
		http.open("GET", rpcserver + "cmd=file_size&xsid=" + xsid, true);
	} else {
		http.open("GET", rpcserver + "cmd=file_status&xsid=" + xsid, true);
	}
	http.send(null);
	//alert(document.upload_form.sourcefile.value);
	if(fsize==0) {
		var out=document.getElementById("progress");
		out.innerHTML="<?php echo(_t('UploadingFile')); ?>...";
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
	out.innerHTML+="<img width=\"" + pg + "\" height=\"10\" src=\"<?php
		echo($this->buildBaseURI("imgs/progress_point.png"));
	?>\">";
}

//Parar la subida
function abortUpload(msg) {
	//alert("stopped");
	keeprunning=false;
	var out=document.getElementById("progress");
	out.innerHTML="<font color=red><?php echo(_t('UploadFailed')); ?><" + "/" + "font>: " + msg;
	self.setTimeout("enableUpload()",1000);
}

//Notificar subida satisfactoria después de la llamada RPC final
function finishUploadCallback(http) {
	var out=document.getElementById("progress");
	//out.innerHTML+=".";
	//out.innerHTML+=http.readyState;
	//out.innerHTML+="<br>";
	if (http.readyState == 4) {
		if (http.status==200) {
			//out.innerHTML+=http.responseText;
			if(http.responseText=="OK") {
				//var out=document.getElementById("progress");
				out.innerHTML="<font color=green><?php echo(_t('UploadFinished')); ?><" + "/" + "font>";
				//Parent hooks
				<?php
					if(!empty($this->end_hook)) {
						echo("	parent." . $this->end_hook . "(fname);");
					}
				?>
				self.setTimeout("enableUpload()",1000);
			} else {
				abortUpload("Last RPC call failed!");
			}
		}
	}
}

//Finalizar la subida
function finishUpload() {
	keeprunning=false;
	var out=document.getElementById("progress");
	out.innerHTML="Finishing upload...";
	//out.innerHTML="<font color=green>Upload Finished<" + "/" + "font>";
	//self.setTimeout("enableUpload()",1000);
	//Llamada RPC final
	var http=get_ajax();
	http.onreadystatechange= function() {
		finishUploadCallback(http);
	}
	http.open("GET", rpcserver + "cmd=file_notify&xsid=" + xsid + "&type=" + resource_type + "&name=" + encodeURIComponent(fname), true);
	http.send(null);	
}

function enableUploadCallback(http) {
	var out=document.getElementById("progress");
	if (http.readyState == 4) {
		if (http.status==200) {
			if(http.responseText=="AUTHFAIL") {
				out.innerHTML="<font color=red>AUTHFAIL</fon>";
			} else if(http.responseText.length==32) {
				xsid=http.responseText;
				//keeprunning=true;
				fsize=0;
				csize=0;
				count=0;
				document.upload_form.sourcefile.disabled=false;
				document.upload_form.sourcefile.value="";
				document.upload_form.reset();
				document.upload_form.action="<?php 
			echo(Request::buildRootURI($upload_script . "?uid={$this->uid}&type=")); ?>" + resource_type + "&xsid=" + xsid;
				//alert(document.upload_form.action);
			} else {
				out.innerHTML="<font color=red>ERROR:</fon>" + http.responseText;
			}
		}
	}
}

//Habilitar el upload
function enableUpload() {
	var http=get_ajax();
	http.onreadystatechange= function() {
		enableUploadCallback(http);
	}
	http.open("GET", rpcserver + "cmd=regenerate_xsid", true);
	http.send(null);	
}

//]]>
</script>
<?php
	}

	function body() {
		global $APF;
		$upload_script=$APF['upload_script'];
		$this->dump_js();
		?>
		<iframe name="ghost" frameborder="0" width="90%" height="0">
		<!-- Ghost Iframe -->
		</iframe>
		<div id="upload_file">
		<form name="upload_form" target="ghost" enctype="multipart/form-data" action="<?php 
			echo(Request::buildRootURI($upload_script . "?xsid={$this->xsid}&amp;uid={$this->uid}&amp;type={$this->resource_type}"));
		?>" method="post">
		<!-- <input type="hidden" name="MAX_FILE_SIZE" value="1000"> -->
		Source file: <input type="file" name="sourcefile" onchange="validateFile()" /><br />
		<!-- Destination Filename: <input type="text" name="fname"><br> -->
		<!-- <input type="button" onclick="validateFile()" value="<?php
		echo(_t('Send_File'));
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