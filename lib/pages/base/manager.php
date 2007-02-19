<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//Inicialización
include_once(dirname(__FILE__) . "/Document.php"); 

///Clase página del gestor
class ApfManager extends ApfDocument {
	//private $tree=null; ///<Contiene una class ApfTree con el conjunto de categorías
	private $maintitle;
	private $subtitle;
	private $menu;

	///Constructor
	function __construct($title) {
		parent::__construct('');
		$this->maintitle=_t('vod_viewer');
		$this->setTitle($title);
		$this->addStyle(_t('default_style'),$this->buildBaseUri('styles/default.css'));
		$this->add2Menu(_t('main_page'),'main');
		$this->add2Menu(_t('videos_page'),"categ");
		if($this->IAmAdmin()) {
			$this->add2Menu(_t('admin_page'),'admin');
		}
		//disconnect
		if($this->IAmAuthenticated()) {
			$this->add2Menu(_t('logout') . " " . $this->getLogin(),'logout');
		} else {
			$this->add2Menu(_t('login_page'),'login');
		}
	}

	/// Fija el título del documento.
	/// @param title El título.
	function setTitle($title) {
		$this->subtitle=$title;
		parent::setTitle($this->maintitle . ' - ' . $title);
	}
	
	/// Añadir una nueva entrada al menú.
	/// @param title titulo de la página.
	/// @param page dirección de la página.
	/// @param link Valor diferente a 0 indica que page es un documento externo
	function add2Menu($title,$page,$link=0,$image='') {
		$x=count($this->menu);
		$this->menu[$x][1]=$title;
		$this->menu[$x][0]=$page;
		$this->menu[$x][2]=$link;
		$this->menu[$x][2]=$image;
	}
	
	/// Cabezera
	function head() {
		parent::head(); //Constructor de la clase base
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
  <td align="right" valign="bottom"><?php /*
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
	</TD></tr><tr><TD align="right"> --> */?>
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
	
	/// Crea el menú de navegación
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
		//Mostrar todas las secciones del índice
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
	
	/// Modo cuerpo, Genera el contenido de la página.
	function body() {
		echo(_t("no_avail"));
	}
	
	/// Muestra información de debug.
	function debug() {
		if(!empty($this->DB)) {
			echo("<br>" . _t("num_querys") . " " . $this->DB->getQueryCount());
		}
	}

	/// Genera una redirección.
	/// @param page Página de destino
	function redirect2page($page) {
		$this->redirect($this->BuildBaseUri($this->getArgs($page,0)));
	}

	/// Genera y envía la página al cliente.
	function show() {
		try {
			$this->head();
			$this->menu();
			$this->foot();
		} catch (Exception $e) {
			$this->print_exception($e,True);
		}
	}

	/// Genera el árbol de categorías
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