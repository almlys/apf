<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//Pagina de administración
require_once(dirname(__FILE__) . "/../base/manager.php"); 

/** Página de administración.
*/
class ApfAdminPage extends ApfManager {
	///	Estado (0=lectura datos,1=datos salvados)
	var $status=0;
	/// Tipo de servidor.
	/// Posibles valores: http,rtsp,...
	var $server_type="http";
	///Constructor
	function ApfAdminPage() {
		$this->ApfManager("");

		//Obtener idioma
		global $APF;
		$lns=$APF["languages"];
		$cnt=count($lns);

		//Fijar título
		$this->setTitle(_t("admin_page"));
		//Verificar credenciales
		if(!$this->authed || !$this->admin) {
			$this->redirect2page("login");
		}

		//Obtener valores de configuración de la db.
		$query="select value from vid_cfg where `key`='server_type'";
		$this->query($query);
		if($vals=$this->fetchArray()) {
			if($vals[0]!="http" && $vals[0]!="rtsp") {
				$server_type="http";
			} else {
				$server_type=$vals[0];
			}
		} else {
			$server_type="http";
			$query='insert into vid_cfg (`key`,value) values("server_type","http")';
			$this->query($query);
		}

		if($_SERVER["REQUEST_METHOD"]=="POST") {
			for($i=0; $i<$cnt; $i++) {
				$name="intro_" . $lns[$i];
				$qtype="new_" . $name;
				$qtype=$_POST[$qtype];
				$val=$this->escape_string($_POST[$name]);
				if($qtype==1) {
					$query="insert into vid_cfg (`key`,value) values(\"$name\",\"$val\")";
				} else {
					$query="update vid_cfg set value=\"$val\" where `key`=\"$name\"";
				}
				//echo($query);
				$this->query($query);
			}
			//update server type
			$server_type=$this->escape_string($_POST["server_type"]);
			if($server_type!="http" && $server_type!="rtsp") {
				$server_type="http";
			}
			$query="update vid_cfg set value=\"$server_type\" where `key`='server_type'";
			$this->query($query);
			$this->status=1;
		}
		$this->server_type=$server_type;
	}
	
	/** Método cuerpo, redefine el método de la clase padre.
	*/
	function body() {
		global $APF;
		$lns=$APF["languages"];
		$cnt=count($lns);
		if($this->status) {
			echo(_t("data_saved"));
		}
		$server_type=$this->server_type;
		?>
		<form action="<?php echo($this->buildBaseUri() . $this->getArgs()); ?>" method="POST">
		<table border="0" width="95%" align="center" cellspacing="10" cellpadding="5"><TR><TD>
		<div class="options">
		<?php echo(_t("main_options")); ?>
		</div>
		</TD></TR>
		<tr><TD>
		<?php echo(_t("server_type") . ":"); ?>
		<SELECT name="server_type">
		<?php
			echo('<option value="http"');
			if($server_type=="http") {
				echo(' selected=""');
			}
			echo('>' . _t("server_type_http") . '</option>');
			
			echo('<option value="rtsp"');
			if($server_type=="rtsp") {
				echo(' selected=""');
			}
			echo('>' . _t("server_type_rtsp") . '</option>'); 
		?>
		</SELECT>
		</TD></tr>
		<tr><TD>
		<?php echo(_t("intro_msg")); ?>:<br>
		<table border="0"><TR><TD>
		<?php
			$from = array('<', '>');
			$to = array('&lt;', '&gt;');
			for($i=0; $i<$cnt; $i++) {
				$name="intro_" . $lns[$i];
				$query="select value from vid_cfg where `key`=\"$name\"";
				$this->query($query);
				if($vals=$this->fetchArray()) {
					$new=0;
				} else {
					$new=1;
				}
				$res = str_replace($from, $to, $vals[0]);
				echo(_t($lns[$i]) . ":<br>");
				echo('<textarea cols=80 rows=5 name="' . $name . '">' . $res . '</textarea><br>');
				echo("\n");
				echo('<input type="hidden" name="new_' . $name . '" value=' . $new . '>');
				echo("<hr>");
			}
		?>
		</td></tr></table>
		</TD></tr>
		</table>
		<INPUT type="submit" value="<?php echo(_t("go")); ?>">
		</form>
<?php
	}
}


?>