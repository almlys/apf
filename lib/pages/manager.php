<?php
/*
  Copyright (c) 2005-2006 Alberto Monta�la Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//Inicializaci�
include_once(dirname(__FILE__) . "/../main.php"); 

///Clase p�ina del gestor
class ApfManager extends ApfDocument {
	var $page="main"; ///<Nombre de la p�ina
	var $params=""; ///<Listado de par�etros extra, empezando por &amp;key=value pairs
	var $admin=0; ///<Indica si el usuario tiene privilegios administrativos
	var $id=0; ///<Identificador de un recurso (categoria, video, etc...)
	var $tree=null; ///<Contiene una class ApfTree con el conjunto de categor�s

	///Constructor
	function ApfManager($title) {
		$this->ApfDocument("");
		//Obtener id
		if(!empty($_GET['id'])) {
			$this->id=$this->escape_string($_GET['id']);
			if($this->id!=$_GET['id']) {
				$this->redirect2page("main");
			}
			$this->params = $this->params . "&amp;id=" . $this->id;
		}
		//
		
		$this->maintitle=_t("vod_viewer");
		$this->setTitle($title);
		$this->stylesheets[0][0]=_t("default_style");
		$this->stylesheets[0][1]=$this->buildBaseUri("styles/default.css");
		$this->add2Menu(_t("main_page"),"main");
		$this->add2Menu(_t("videos_page"),"categ");
		if(!empty($_GET['page'])) {
			$this->page=$_GET['page'];
		}
		if($this->admin) {
			$this->add2Menu(_t("admin_page"),"admin");
		}
		//disconnect
		if($this->authed) {
			$this->add2Menu(_t("logout") . " " . $_SESSION["login"],"logout");
		} else {
			$this->add2Menu(_t("login_page"),"login");
			//$_SESSION["admin"]=0;
		}
	}
	
	/// A�dir una nueva entrada al men.
	/// @param title titulo de la pagina.
	/// @param page direcci� de la p�ina.
	/// @param link Valor diferente a 0 indica que page es un documento externo
	function add2Menu($title,$page,$link=0) {
		$x=count($this->menu);
		$this->menu[$x][1]=$title;
		$this->menu[$x][0]=$page;
		$this->menu[$x][2]=$link;
	}
	
	/// Obtener vector de argumentos del documento. (Para construir enlaces)
	/// @param test Si se especifica, fijar�nueva direcci� de destino.
	/// @param encode Si es verdadero, codificar�& como &amp;
	function getArgs($test="",$encode=1) {
		if(!empty($test)) {
			$page=$test;
		} else {
			$page=$this->page;
		}
		if($encode) {
			$amp="&amp;";
		} else {
			$amp="&";
		}
		$lan=ApfLocal::getDefaultLanguage();
		$args="?page=$page" . $amp . "lan=$lan";
		return $args;
	}
	
	/// Obtener vector de argumentos del documento. (Para uso en campos ocultos de un formulario)
	/// @param test Si se especifica, fijar�nueva direcci� de destino.
	function getArgsHidden($test="") {
		if(!empty($test)) {
			$page=$test;
		} else {
			$page=$this->page;
		}
		$lan=ApfLocal::getDefaultLanguage();
		//$args="?page=$page" . $amp . "lan=$lan";
		$args='<input type="hidden" name="page" value="' . $page . '">
		<input type="hidden" name="lan" value="' . $lan . '">';
		return $args;
	}
	
	/// Fija el t�ulo del documento.
	/// @param title El t�ulo.
	function setTitle($title) {
		$this->subtitle=$title;
		$this->title=$this->maintitle . " - " . $title;
	}
	
	/// Cabezera
	function head() {
		ApfDocument::head(); //Constructor de la clase base
		?>
 <table border="0" width="97%" cellpadding="0" cellspacing="0" align="center" class="header">
 <tr>
 <td>
  <!-- title/logo -->
  <table border="0" align="left" cellpadding="0" cellspacing="0" width="95%">
  <tr>
  <td>
   <div class="title"><? echo($this->maintitle); ?></div>
  </td>
  <td align="right" valign="bottom">
	<!--
	<table border="0" cellpadding="0" cellspacing="0" width="100%"><TR><TD align="right">
	<div class="mini_login">
	<form action="<?php echo($this->buildBaseUri() . $this->getArgs("login") . "&amp;redirect=" . $this->page); ?>" method="POST">
		<?php echo(_t("login") . ":"); ?>
		<INPUT type="text" name="login">
		<?php echo(_t("password") . ":"); ?>
		<INPUT type="password" name="password">
		<INPUT type="submit" value="ok">
		</form>
	</div>
	</TD></tr><tr><TD align="right"> -->
   <div class="language_selector">
<?php
		//Mostrar todos los idiomas disponibles
		$page=$this->page;
		$str_id=$this->params;
		$lan=ApfLocal::getDefaultLanguage();
		if($lan!="es") {
			echo("<a href=\"?page=$page&amp;lan=es$str_id\">Espa&ntilde;ol</a>");
		}
		if($lan!="ca") {
			echo("&nbsp;&nbsp;<a href=\"?page=$page&amp;lan=ca$str_id\">Catal&agrave;</a>");
		}
		if($lan!="en") {
			echo("&nbsp;&nbsp;<a href=\"?page=$page&amp;lan=en$str_id\">English</a>");
		}
?>
   </div> <!--
	 </tr></td>
	 </table> -->
  </td>
  </tr>
  </table>
 </td>
 </tr>
 </table>

<?php
	} //Fin del metodo head
	
	/// Crea el men de navegaci�
	function menu() {
		?>
	<table border="0" width="97%" cellspacing="0" cellpadding="0" align="center" class="doc_body">
		<tr>
		<!-- Nav -->
		<td class="menu" align="center" width="13%" valign="top">
			<table border="0" cellspacing="0" cellpadding="3" width="100%" class="fmenu">
			<tr>
				<td height="50">&nbsp;</td>
			</tr>
<?
		//Mostrar todas las secciones del �dice
		$i=0; $done=0;
		$menu=$this->menu;
		$page=$this->page;
		$lan=ApfLocal::getDefaultLanguage();
		while(!empty($menu[$i][0])) {
			echo("<tr><td align=\"center\" nowrap>");
			if($page==$menu[$i][0]) {
				echo("<div class=\"selected\"><b>&gt;" . $menu[$i][1] . "&lt;</b></div>");
				$done=1;
			} else {
				if($menu[$i][2]==0) {
					echo("<div class=\"unselected\"><a href=\"?page=" . $menu[$i][0] . "&amp;lan=" . $lan . "\">" . $menu[$i][1] . "</a></div>");
				} else {
					echo("<div class=\"unselected\"><a href=\"" . $menu[$i][0] . "\">" . $menu[$i][1] . "</a></div>");
				}
			}
			echo("</td></tr>");
			$i++;
		}
		if(!$done) {
			if($page=="videos") {
				$nampage=_t("videos");
			} elseif($page=="edit") {
				$nampage=_t("edit_page");
			} else {
				$nampage=$page;
			}
			echo("<tr><td align=\"center\" nowrap>");
			echo("<div class=\"selected\"><b>&gt;" . $nampage . "&lt;</b></div>");
			echo("</td></tr>");
		}
?>
			</table>
		</td>
		<!-- document -->
		<td class="document" align="left" valign="top">
			<table border="0" width="100%" cellspacing="3" cellpadding="3">
			<tr>
				<td>
				<!-- title -->
				<h1><? echo($this->subtitle); ?></h1>
				<!-- body -->
	<?php
		$this->body();
	?>
				</td></tr>
			</table>
		</td>
	</tr>
	</table>
<?php

	} //Final del m�odo men
	
	/// Pie de la p�ina
	function foot() {
		?>
 <!-- Footer -->
 <table border="0" width="97%" align="center" class="fot">
 <tr><td>
 <?php /*
  <!-- Second object -->
  <table border="0" align="center" width="95%">
  <tr>
  <td align="right" valign="bottom">
   <!-- validator & any brownser -->
   <table border="0" align="right">
   <tr><td>
    <a href="http://validator.w3.org/check/referer" target="_blank"><img border="0" src="<? echo($this->buildBaseURI()); ?>/imgs/valid-html401.gif" alt="Valid HTML 4.01!" height="31" width="88"></a>
   </td><td>
    <a href="http://jigsaw.w3.org/css-validator/check/referer" target="_blank"><img border="0" src="<? echo($this->buildBaseURI()); ?>/imgs/vcss" alt="Valid CSS!" height="31" width="88"></a>
   </td>
   </tr>
   </table>
  </td>
  </tr>
  </table>
 <tr><td>
 */ ?>
  <div class="debug">
  Debug: <? echo($this->getTime("milliseconds")); 
	$this->debug();
	?>
  </div>
 </td></tr>
 </table>
<?php
	ApfDocument::foot();
	} //Final del m�odo foot
	
	/// M�odo cuerpo, Genera el contenido de la p�ina.
	function body() {
		echo(_t("no_avail"));
	}
	
	/// Muestra informaci� de debug.
	function debug() {
		if(!empty($this->DB)) {
			echo("<br>" . _t("num_querys") . " " . $this->DB->query_count);
		}
	}
	
	/// Genera y env� la p�ina al cliente.
	function show() {
		$this->head();
		$this->menu();
		$this->foot();
	}

	/// Genera una redirecci�.
	/// @param page P�ina de destino
	function redirect2page($page) {
		$this->redirect($this->BuildBaseUri($this->getArgs($page,0)));
	}

	/// Genera el �bol de categor�s
	function generateMediaTree() {
		if($this->tree==null) {
			$lan=ApfLocal::getDefaultLanguage();
			$query="select a.id,a.parent,b.name
							from vid_categ a inner join vid_names b
							on a.name_id=b.id
							where b.lan=\"$lan\"";
			$this->query($query);
			$i=0;
			while($vals[$i++]=$this->fetchArray()) {
				//echo($i-1 . " " . $vals[$i-1][0] . $vals[$i-1][2] . "<br>\n");
			}
			//echo("<hr>");
			$this->tree=new ApfTree($vals);
		}
	}

} //End Class


?>