<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//Inicialización
include_once(dirname(__FILE__) . "/lib/main.php"); 

///Clase página del gestor
class ApfManager extends ApfDocument {
	var $page="main"; ///<Nombre de la página
	var $params=""; ///<Listado de parámetros extra, empezando por &amp;key=value pairs
	var $admin=0; ///<Indica si el usuario tiene privilegios administrativos
	var $id=0; ///<Identificador de un recurso (categoria, video, etc...)
	var $tree=null; ///<Contiene una class ApfTree con el conjunto de categorías

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
		
		$this->maintitle=$this->lan->get("vod_viewer");
		$this->setTitle($title);
		$this->stylesheets[0][0]=$this->lan->get("default_style");
		$this->stylesheets[0][1]=$this->buildBaseUri("styles/default.css");
		$this->add2Menu($this->lan->get("main_page"),"main");
		$this->add2Menu($this->lan->get("videos_page"),"categ");
		if(!empty($_GET['page'])) {
			$this->page=$_GET['page'];
		}
		if($this->admin) {
			$this->add2Menu($this->lan->get("admin_page"),"admin");
		}
		//disconnect
		if($this->authed) {
			$this->add2Menu($this->lan->get("logout") . " " . $_SESSION["login"],"logout");
		} else {
			$this->add2Menu($this->lan->get("login_page"),"login");
			//$_SESSION["admin"]=0;
		}
	}
	
	/// Añadir una nueva entrada al menú.
	/// @param title titulo de la pagina.
	/// @param page dirección de la página.
	function add2Menu($title,$page) {
		$x=count($this->menu);
		$this->menu[$x][1]=$title;
		$this->menu[$x][0]=$page;
	}
	
	/// Obtener vector de argumentos del documento. (Para construir enlaces)
	/// @param test Si se especifica, fijará nueva dirección de destino.
	/// @param encode Si es verdadero, codificará & como &amp;
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
		$lan=$this->lan->getDefaultLanguage();
		$args="?page=$page" . $amp . "lan=$lan";
		return $args;
	}
	
	/// Obtener vector de argumentos del documento. (Para uso en campos ocultos de un formulario)
	/// @param test Si se especifica, fijará nueva dirección de destino.
	function getArgsHidden($test="") {
		if(!empty($test)) {
			$page=$test;
		} else {
			$page=$this->page;
		}
		$lan=$this->lan->getDefaultLanguage();
		//$args="?page=$page" . $amp . "lan=$lan";
		$args='<input type="hidden" name="page" value="' . $page . '">
		<input type="hidden" name="lan" value="' . $lan . '">';
		return $args;
	}
	
	/// Fija el título del documento.
	/// @param title El título.
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
		<?php echo($this->lan->get("login") . ":"); ?>
		<INPUT type="text" name="login">
		<?php echo($this->lan->get("password") . ":"); ?>
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
		$CURRENT_LANGUAGE=substr($this->lan->language[0],0,2);
		if($CURRENT_LANGUAGE!="es") {
			echo("<a href=\"?page=$page&amp;lan=es$str_id\">Espa&ntilde;ol</a>");
		}
		if($CURRENT_LANGUAGE!="ca") {
			echo("&nbsp;&nbsp;<a href=\"?page=$page&amp;lan=ca$str_id\">Catal&agrave;</a>");
		}
		if($CURRENT_LANGUAGE!="en") {
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
	
	/// Crea el menú de navegación
	function menu() {
		?>
 <table border="0" width="97%" cellspacing="0" cellpadding="0" align="center" class="doc_body">
 <tr>
 <td align="center" width="13%" valign="top">
  <!-- Nav -->
  <div class="menu">
  <table border="0" cellspacing="0" cellpadding="3" width="100%" class="fmenu">
  <tr>
  <td height="50">&nbsp;</td>
  </tr>
<?
		//Mostrar todas las secciones del índice
		$i=0; $done=0;
		$menu=$this->menu;
		$page=$this->page;
		$lan=substr($this->lan->language[0],0,2);
		while(!empty($menu[$i][0])) {
			echo("<tr><td align=\"center\" nowrap>");
			if($page==$menu[$i][0]) {
				echo("<div class=\"selected\"><b>&gt;" . $menu[$i][1] . "&lt;</b></div>");
				$done=1;
			} else {
				echo("<div class=\"unselected\"><a href=\"?page=" . $menu[$i][0] . "&amp;lan=" . $lan . "\">" . $menu[$i][1] . "</a></div>");
			}
			echo("</td></tr>");
			$i++;
		}
		if(!$done) {
			if($page=="videos") {
				$nampage=$this->lan->get("videos");
			} elseif($page=="edit") {
				$nampage=$this->lan->get("edit_page");
			} else {
				$nampage=$page;
			}
			echo("<tr><td align=\"center\" nowrap>");
			echo("<div class=\"selected\"><b>&gt;" . $nampage . "&lt;</b></div>");
			echo("</td></tr>");
		}
?>
  </table>
  </div>
 </td>
 <td align="left" valign="top">
  <!-- document -->
	<div class="document">
  <table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr><td>
   <!-- title -->
   <h1><? echo($this->subtitle); ?></h1>
   <!-- body -->
   <?php
		$this->body();
	 ?>
   </td></tr>
   </table>
	 </div>
  </td>
  </tr>
  </table>

<?php

	} //Final del método menú
	
	/// Pie de la página
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
	} //Final del método foot
	
	/// Método cuerpo, Genera el contenido de la página.
	function body() {
		echo($this->lan->get("no_avail"));
	}
	
	/// Muestra información de debug.
	function debug() {
		if(!empty($this->DB)) {
			echo("<br>" . $this->lan->get("num_querys") . " " . $this->DB->query_count);
		}
	}
	
	/// Genera y envía la página al cliente.
	function show() {
		$this->head();
		$this->menu();
		$this->foot();
	}

	/// Genera una redirección.
	/// @param page Página de destino
	function redirect2page($page) {
		$this->redirect($this->BuildBaseUri($this->getArgs($page,0)));
	}

	/// Genera el árbol de categorías
	function generateMediaTree() {
		if($this->tree==null) {
			$lan=$this->lan->getDefaultLanguage();
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