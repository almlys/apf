<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

///Interfaz Documento
interface iDocument {
	public function show();
	public function head();
	public function body();
	public function foot();
}

/// Clase del documento base
class ApfBaseDocument implements iDocument {
	///Título de la página
	private $title="Untitled";
	///Tiempo de inicio de creación del script
	private $start_time=0;
	private $state=0; /**< state=0 (Etiquetas HTML no enviadas),
                   state=1 (Se estan enviando las etiquetas),
                   state=2 (Etiquetas HTML enviadas),
                   state=3 (Ha ocurrido un error (head))
                   state=4 (Enviando error (foot)) */
	///charset
	private $charset='UTF-8';
	///Generador
	private $generator='ApfManager';
	///Array de estilos disponibles
	private $stylesheets;
	///Directorio base
	private $path='';
	private $UseRelativePaths=False;
	private $bodyclass='';
	private $unload_actions=array(); ///<Codigo a ejecutar al producirse un onUnload
	
	/// Constructor
	/// @param $title Título del documento
	public function __construct($title="Untitled") {
		global $APF;
		$this->start_time=$APF['start_time'];
		$this->title=$title;
		if($APF['relative_paths']) {
			$this->UseRelativePaths=true;
		} else {
			$this->UseRelativePaths=false;
		}
		if(empty($APF['server.path'])) {
			$APF['server.path']=dirname($_SERVER["SCRIPT_NAME"]);
			//echo($APF['server.path'] . "<br>");
			//$APF['server.path']=str_replace("//","/",$APF['server.path']);
			//echo($APF['server.path'] . "<br>");
		}
		$this->path=$APF['server.path'];
	}

	/// Fija el título del documento
	/// @param $title Título
	public function setTitle($title) {
		$this->title=$title;
	}

	/// Fija la classe de Body
	/// @param class Classe
	public function setBodyClass($class) {
		$this->bodyclass=$class;
	}

	/// Añade un nuevo estilo en cascada
	/// @param $name Nombre del estilo
	/// @param $path Ruta al estilo
	public function addStyle($name,$path) {
		$x=count($this->stylesheets);
		$this->stylesheets[$x][0]=$name;
		$this->stylesheets[$x][1]=$path;
	}
	
	/// Obtener el tiempo del script actual
	/// @param $how Especifica la medida (s,seconds,ms,milliseconds,us,microseconds
	public function getTime($how="") {
		$START_TIME=$this->start_time; //Fijar el tiempo de inicio
		$end_time=microtime();
		$parcial_calc=explode(' ',$START_TIME . ' ' . $end_time);
		$duration=sprintf('%01.8f',($parcial_calc[2]+$parcial_calc[3])-($parcial_calc[0]+$parcial_calc[1]));
		switch($how) {
		case "s":
			$sufix=" s.";
			break;
		case "seconds":
			$sufix=" " . _t("seconds");
			break;
		case "ms":
			$sufix=" ms.";
			$duration=$duration*1000;
			break;
		case "milliseconds":
			$sufix=" " . _t("milliseconds");
			$duration=$duration*1000;
			break;
		case "microseconds":
			$sufix=" " . _t("microseconds");
			$duration=$duration*1000000;
			break;
		case "us":
			$sufix=" &micro;s";
			$duration=$duration*1000000;
			break;
		default:
			return($duration);
		}
		return($duration . $sufix);
	}
	
	/** Genera y imprime la cabezera del documento */
	function head() {
		try {
			if($this->state!=0 and $this->state!=3) return; //Ya ha sido enviada
			$this->state=1;
			?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
			//Fijar Meta Tags
			echo("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $this->charset . "\" />\n");
			echo("<meta name=\"Language\" content=\"" . ApfLocal::getDefaultLanguage() . "\" />\n");
			if(!empty($this->description)) {
				echo("<meta name=\"description\" content=\"" . $this->description . "\" />\n");
			}
			if(!empty($this->keywords)) {
				echo("<meta name=\"keywords\" content=\"" . $this->keywords . "\" />\n");
			}
			echo("<meta name=\"Generator\" content=\"" . $this->generator . "\" />\n");
			//Fin de fijacción de información meta
			//Fijar título
			echo("<title>{$this->title}</title>");
			
			//Fijar las hojas de estilo (stylesheets)
			$i=0;
			$styles=$this->stylesheets;
			while(!empty($styles[$i][0])) {
				//Principal
				echo('<link rel="');
				if($i!=0) echo("alternate ");
				echo('stylesheet" title="' . $styles[$i][0] .'" href="' . $styles[$i++][1] . '" type="text/css" />');
			}
			echo("</head>\n");
			$unload='';
			if(!empty($this->unload_actions)) {
				$unload=' onunload="onUnloadDocument()"';
			}
			if(empty($this->bodyclass)) {
				echo("<body$unload>\n");
			} else {
				echo("<body class='{$this->bodyclass}'$unload>");
			}
			if(!empty($this->unload_actions)) {
				$unload=' onunload="onUnloadDocument()"';
				echo("<script type='text/javascript'>
				//<![CDATA[
				function onUnloadDocument() {
				");
				foreach ($this->unload_actions as $action) {
					echo("
					$action();
					");
				}
				echo("
				}
				//]]>
				</script>");
			}
			$this->state=2;
		} catch(Exception $e) {
			$this->print_exception($e,True);
		}
	}

	/// Registra una función js a ejecutar cuando se cierre la página
	/// @param hook Funcion a ejecutar
	function registerUnloadHook($hook) {
		array_push($this->unload_actions,$hook);
	}

	function body() {}

	/** Genera e imprime el pie del documento */
	function foot() {
		?>
</body>
</html>
<?php
	}

	function print_exception($e,$die=True) {
		if(empty($this->title)) $this->title=_t("error_tit");
		if($this->state==0) {
			$this->state=3; //Fijar estado de error head
			$this->head();
		}
		echo("<center>");
		print_exception($e,False);
		echo("</center>");
		if($die and $this->state!=4) {
			$this->state=4; //Fijar estado de error foot
			$this->foot();
			exit();
		}
	}
	
	/// Muestra un mensaje de error
	/// @param msg Mensaje
	/// @param title Título
	/// @param die Matar aplicación?
	function error($msg,$title="",$die=False) {
		if(empty($this->title)) $this->title=_t("error_tit");
		if($this->state==0) {
			$this->state=2; //Fijar estado de error
			$this->head();
		}
		?>
<center>
<table bgcolor="Yellow">
<TR><td bgcolor="Red"><b><font color="yellow"><?php echo($title); ?></font></b></TD></TR>
<tr><td><font color="red"><?php 
		echo($msg);
		echo("<br> " . _t("error_req") . $_SERVER["REQUEST_URI"]);
		echo("<br> <a href=\"" . $_SERVER["HTTP_REFERER"] . "\">" . _t("error_ret") . "</a>");
		?></font></TD></tr>
</table>
</center>
<?php
		if($die) {
			$this->foot();
			exit();
		}
	}

	/// Mostrar mensaje de error y morir
	/// @param msg Mensjae
	/// @param title Título
	function error_die($msg,$title='') {
		$this->error($msg,$title,True);
	}
	

	/// Genera y envía la página al cliente.
	function show() {
		try {
			$this->head();
			$this->body();
			$this->foot();
		} catch (Exception $e) {
			$this->print_exception($e,True);
		}
	}
	
	/**
		Construye una ruta relativa al directorio de la aplicación
		@param path Url a concatenar
		@param relative Indica si queremos la ruta relativa o absoluta
		@return Si relative paths es falso, devuelve la dirección completa URL protocol://base_install al directorio base de la instalación, sino siempre
		devolverá el path relativo
	*/
	function buildBaseURI($path="",$relative=true) {
		if(!$this->UseRelativePaths || $relative==false) {
			return Request::buildRootURI($this->path . "/" . $path);
		} else {
			return $path;
		}
	}

	/** Devuelve la fecha de la última modificación del script en ejecución. */
	function getLastMod() {
		return filemtime($_SERVER["SCRIPT_FILENAME"]);
	}

	///Genera una redirección.
	///@param to Dirección destino
	function redirect($to) {
		header("Location: $to");
		?>
		<html><head><TITLE><?php echo(_t("redirecting_to") . $to); ?></TITLE>
		</head><body>
		<a href="<?php echo($to); ?>"><?php echo(_t("click_2_continue")); ?></a>
		</body>
		</html>
		<?php
		exit();
	}

} //End ApfBaseDocument Class

?>