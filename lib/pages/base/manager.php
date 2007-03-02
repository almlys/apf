<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//Inicialización
include_once(dirname(__FILE__) . "/Document.php"); 

///Clase página del gestor
class ApfManager extends ApfDocument implements iDocument {
	//private $tree=null; ///<Contiene una class ApfTree con el conjunto de categorías
	private $maintitle;
	private $subtitle;
	private $menu;

	///Constructor
	function __construct($title='',$release_session=True) {
		parent::__construct('',$release_session);
		$this->maintitle=_t('vod_viewer');
		$this->setTitle($title);
		// Estilos
		$this->addStyle(_t('default_style'),$this->buildBaseUri('styles/default.css'));
		// Menu
		$this->add2Menu(_t('main_page'),'main','','imgs/home.png');
		$this->add2Menu(_t('videos_page'),"categ",'','imgs/video.png');
		/*if($this->IAmAdmin()) {
			$this->add2Menu(_t('admin_page'),'admin','','imgs/config.png');
		}*/
		//disconnect
		if($this->IAmAuthenticated()) {
			$this->add2Menu(_t('logout') . " " . $this->getLogin(),'logout','','imgs/logout.png');
		} else {
			$this->add2Menu(_t('login_page'),'login','','imgs/login.png');
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
	/// @param link Valor diferente a '' indica que page es un documento externo
	/// @param image Valor diferente a '' indica ruta a un icono
	function add2Menu($title,$page,$link='',$image='') {
		$x=count($this->menu);
		$this->menu[$x][1]=$title;
		$this->menu[$x][0]=$page;
		$this->menu[$x][2]=$link;
		$this->menu[$x][3]=$image;
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
		$lan=ApfLocal::getDefaultLanguage();
		if($lan!="es") {
			$str_id=$this->getArgs(array('lan' => 'es'));
			echo("<a href=\"$str_id\">Espa&ntilde;ol</a>");
		}
		$str_id=$this->getArgs(array('lan' => 'ca'));
		if($lan!="ca") {
			echo("&nbsp;&nbsp;<a href=\"$str_id\">Catal&agrave;</a>");
		}
		$str_id=$this->getArgs(array('lan' => 'en'));
		if($lan!="en") {
			echo("&nbsp;&nbsp;<a href=\"$str_id\">English</a>");
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
		$page=$this->getParam('page');
		$mask=array('lan','page');
		while(!empty($menu[$i][0])) {
			if(!empty($menu[$i][3])) {
				$img="<img border='0' src=\"" . $this->buildBaseURI($menu[$i][3]) ."\" alt=\"{$menu[$i][1]}\" />";
			} else {
				$img='';
			}
			echo("<tr><td align=\"center\" nowrap='nowrap'>");
			if($page==$menu[$i][0]) {
				echo("<div class=\"selected\">$img<br /><b>&gt;{$menu[$i][1]}&lt;</b></div>");
				$done=1;
			} else {
				if(empty($menu[$i][2])) { //Link interno
					$str_id=$this->getArgs(array('page' => $menu[$i][0]),$mask);
					echo("<div class=\"unselected\"><a href=\"$str_id\">$img<br />{$menu[$i][1]}</a></div>");
				} else { //Link externo
					echo("<div class=\"unselected\"><a href=\"{$menu[$i][2]}\">$img<br />{$menu[$i][1]}</a></div>");
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
			echo("<tr><td align=\"center\" nowrap='nowrap'>");
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

	//Widgets

	/// Obtener Gestor del catálogo
	function getMediaMGR() {
		if(empty($this->mediaMGR)) {
			require_once(dirname(__FILE__) . '/../../mediamgr/MediaMGR.php');
			$this->mediaMGR=new MediaMGR($this);
		}
		return $this->mediaMGR;
	}

	/// Genera el árbol de categorías
	function getMediaTree() {
		return $this->getMediaMGR()->getMediaTree();
	}

	/// Escribe el árbol de categorias
	function writeCategoryListControl($id=-1) {
		if($id==-1) $id=$this->id;
		//Quick navigation
		echo('<form name="jumpfrm" action="' . $this->buildBaseUri() . '" method="get">' . "\n");
		echo(_t("category") . ": ");
		echo('<SELECT name="id" onchange="document.jumpfrm.submit()">' . "\n");
		$this->getMediaTree()->writeOptions($id);
		echo("</select>\n");
		echo($this->getArgsHidden(array('page' => 'categ'),'',array('id')));
		echo('<INPUT type="submit" value="' . _t("go") . '" />' . "\n");
		echo('</form>');
	}

	/// Escribe todas las carpetas
	/// @param id Identificador
	function printFolders($id) {
		$lan=ApfLocal::getDefaultLanguage();
		//$query="select id,$name,$desc,count from vid_categ where parent=" . $this->id;
		$query="select a.id,b.name,c.desc,a.count
						from vid_categ a inner join (vid_names b, vid_descs c)
						on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
						where b.lan=\"$lan\" and a.parent=" . $id;
		$this->query($query);
		while($vals=$this->fetchArray()) {
			$folder=new ApfFolder($this,$vals[0],$vals[1],$vals[2],$vals[3]);
			$folder->show();
		}
	}

	/// Escribe recursos multimedia
	/// @param id Identificador
	function printMedia($id) {
		$lan=ApfLocal::getDefaultLanguage();
		//$query="select id,$name,$desc,prev,dur from vid_mfs where ctg=" . $this->id;
		$query="select a.id,b.name,c.desc,a.prev,a.dur
						from vid_mfs a inner join (vid_names b, vid_descs c)
						on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
						where b.lan=\"$lan\" and a.ctg=" . $id;
		$this->query($query);
		while($vals=$this->fetchArray()) {
			$folder=new ApfVideo($this,$vals[0],$vals[1],$vals[2],$vals[3],$vals[4]);
			$folder->show();
		}
	}

	/// Escribe las ultimas novedades multimedia
	/// @param cut Limite
	function printNewMedia($cut=4) {
	}

	/// Escribe los recuros más visitados
	/// @param cut Limite
	function printTopMedia($cut=4) {
	}


} //End Class


?>