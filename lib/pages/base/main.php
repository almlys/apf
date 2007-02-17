<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//Plugins
require_once(dirname(__FILE__) . "/../../" . $APF['auth.plug']);
require_once(dirname(__FILE__) . "/../../" . $APF['db.plug']);

/// Clase del documento base
class ApfBaseDocument {
	///Título de la página
	var $title="Untitled";
	///Tiempo de inicio de creación del script
	var $start_time=0;
	///Idioma de los contenidos
	var $lan;
	var $state=0; /**< state=0 (Etiquetas HTML no enviadas),
                   state=1 (Etiquetas HTML enviadas)
                   state=2 (Ha ocurrido un error) */
	///charset
	var $charset="UTF-8";
	///Generador
	var $generator="ApfManager";
	///Array de estilos disponibles
	var $stylesheets;
	///Directorio base
	var $path="";
	
	/// Constructor
	function ApfBaseDocument($title="Untitled") {
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
	
	/** Obtener el tiempo del script actual */
	function getTime($how="") {
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
		if($this->state==1) return;
		$this->state=1;
		?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<title><?php echo($this->title); ?></title>
<?php
		//Fijar Meta Tags
		echo("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $this->charset . "\">\n");
		echo("<meta name=\"Language\" content=\"" . ApfLocal::getDefaultLanguage() . "\">\n");
		if(!empty($this->description)) {
			echo("<meta name=\"description\" content=\"" . $this->description . "\">\n");
		}
		if(!empty($this->keywords)) {
			echo("<meta name=\"keywords\" content=\"" . $this->keywords . "\">\n");
		}
		echo("<meta name=\"Generator\" content=\"" . $this->generator . "\">\n");
		//Fin de fijacci� de informaci� meta
		
		//Fijar las hojas de estilo (stylesheets)
		$i=0;
		$styles=$this->stylesheets;
		while(!empty($styles[$i][0])) {
			//Principal
			echo('<link rel="');
			if($i!=0) echo("alternate ");
			echo('stylesheet" title="' . $styles[$i][0] .'" href="' . $styles[$i++][1] . '" type="text/css">');
		}
		?>
</head>
<body>
<?php
	}
	
	/** Genera e imprime el pie del documento */
	function foot() {
		?>
</body>
</html>
<?php
	}
	
	/** Muestra un mensaje de error */
	function error($msg,$title="") {
		if(empty($title)) $title=_t("error_tit");
		$this->state=2; //Fijar estado de error
		//if($this->state==0) $this->head();
		$this->head(); //El estado debe canviar a 1, sino protecci� contra llamada recursiva
		if($this->state==2) ApfBaseDocument::head();
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
	}

	///Muestra un mensaje de error y termina de forma immediata
	function error_die($msg,$title="") {
		$this->error($msg,$title);
		$this->foot();
		exit();
	}
	
	/**
		Construye una ruta relativa al directorio de la aplicaci�
		@param path Url a concatenar
		@param relative Indica si queremos la ruta relativa o absoluta
		@return Si relative paths es falso, devuelve la direcci� completa URL protocol://base_install al directorio base de la instalaci�, sino siempre
		devolver�el path relativo
	*/
	function buildBaseURI($path="",$relative=true) {
		if(!$this->UseRelativePaths || $relative==false) {
			$proto=$this->getProtocol();
			$port=$this->getPort();
			if (($proto=="http" && $port==80) || ($proto=="https" && $port==443)) {
				$port="";
			} else {
				$port=":" . $port;
			}
			$url=$proto . "://"  . $_SERVER['SERVER_NAME'] . $port;
			$path="/" . $this->path . "/" . $path;
			$path=str_replace("//","/",$path);
			$url=$url . $path;
			return $url;
		} else {
			return $path;
		}
	}
	
	/// Construye una ruta relativa a la raiz
	/// @param path Ruta
	/// @return Ruta completa
	function buildRootURI($path="") {
		$proto=$this->getProtocol();
		$port=$this->getPort();
		if (($proto=="http" && $port==80) || ($proto=="https" && $port==443)) {
			$port="";
		} else {
			$port=":" . $port;
		}
		$url=$proto . "://"  . $_SERVER['SERVER_NAME'] . $port;
		$path="/" . $path;
		$path=str_replace("//","/",$path);
		$url=$url . $path;
		return $url;
	}
	
	///Obtiene el protocolo
	function getProtocol() {
		return ($HTTP_SERVER_VARS["HTTPS"]=="on" ? "https" : "http");
	}
	
	///Obtiene el puerto
	function getPort() {
		return $_SERVER["SERVER_PORT"];
	}

	///Muestra la ip del cliente
	///@param how short=solo ip, rshort=ip + x_forward, sino mostrar�informaci� completa ip+x_forward+client_ip+via
	function getRemoteAddress($how="") {
		if($format=="short") {
			return($_SERVER["REMOTE_ADDR"]);
		} elseif($format=="rshort") {
			if($_SERVER["HTTP_X_FORWARDED_FOR"])
				return($_SERVER["HTTP_X_FORWARDED_FOR"]);
			elseif($_SERVER["HTTP_CLIENT_IP"])
				return($_SERVER["HTTP_CLIENT_IP"]);
			else
				return($_SERVER["REMOTE_ADDR"]);
		} else {
			$ret=$_SERVER["REMOTE_ADDR"];
			$proxy="";
			$extra="";
			if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
				$proxy=" (proxy)";
				$extra=$extra . " x-forwarded-for: <b>" . $_SERVER["HTTP_X_FORWARDED_FOR"] . "</b>";
			}
			if ($_SERVER["HTTP_CLIENT_IP"]) {
				$proxy=" (proxy)";
				$extra=$extra . " client-ip: <b>" . $_SERVER["HTTP_CLIENT_IP"] . "</b>";
			}
			if ($_SERVER["HTTP_VIA"]){
				$proxy=" (proxy)";
				$extra=$extra . " via: <b>" . $_SERVER["HTTP_VIA"] . "</b>";
			}
			$ret=$ret . $proxy . $extra;
			return($ret);
		}
	}

	/** Devuelve la fecha de la ltima modificaci� del script en ejecuci�. */
	function getLastMod() {
		return filemtime($_SERVER["SCRIPT_FILENAME"]);
	}

} //End ApfBaseDocument Class

///Documento base
class ApfDocument extends ApfBaseDocument {
	///Indica si estamos autenticados
	var $authed=0;
	///Indica si tenemos privilegios administrativos
	var $admin=0;
	var $uid=0; ///< Identificador del usuario
	///Objecto Base de datos
	var $DB;
	///Objecto de autenticaci�
	var $auth;
	///Constructor
	function ApfDocument($title) {
		$this->ApfBaseDocument($title);
		//$this->auth=new ApfAuth($this);
		$this->auth=createAuthObject(&$this);
		//sessions
		session_name("ApfVoDPHPSID");
		session_start();
		if(!empty($_SESSION["AuthHash"]) && $_COOKIE["ApfVoDAuthHash"]==$_SESSION["AuthHash"]) {
			//echo("authed!");
			//$this->authed=1;
			if($this->auth->verify($_SESSION["uid"],$_SESSION["AuthHash"])) {
				$this->authed=1;
				$this->admin=$_SESSION["admin"];
				$this->uid=$_SESSION["uid"];
			} else {
				$this->endSession();
			}
		} else {
			$this->endSession();
		}
	}
	
	///Finaliza la session
	function endSession() {
		$_SESSION["AuthHash"]="";
		$_SESSION["admin"]=0;
		$_SESSION["uid"]=0;
		$_SESSION["login"]="";
		setcookie("ApfVoDAuthHash","",time()-36000);
	}
	
	///Genera una redirecci�.
	///@param to Direcci� destino
	function redirect($to) {
		session_commit();
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
	
	///Comprueba la conexi� con la base de datos.
	function checkConnection() {
		global $APF;
		//$this->state=2;
		if(empty($this->DB)) {
			//$this->DB=new ApfDB($APF['db.user'],$APF['db.passwd'],$APF['db.name'],$APF['db.host']);
			$this->DB=createDBObject($APF['db.user'],$APF['db.passwd'],$APF['db.name'],$APF['db.host']);
			if($this->DB->connect()) {
				$this->error($this->DB->getError());
			}
		}
	}

	///Realiza una petici� a la base de datos.
	///@param what Petici� SQL.
	function query($what) {
		$this->checkConnection();
		//echo($what);
		if(!$this->DB->query($what)) {
			//echo("<br><font color=red>Error</font><br>");
			$this->error_die($this->DB->getError());
		}
		return 1;
	}
	
	///Obtiene un array de los datos devueltos de la base de datos desde la ltima petici� que devolvi�datos.
	function fetchArray() {
		return($this->DB->fetchArray());
		/*if($ret==null) { //Cazurro
			$this->error_die("fetchArray() " . $this->DB->getError());
		} else {
			return $ret;
		}*/
	}

	///Escapa caracteres especiales como "'".
	function escape_string($what) {
		$this->checkConnection();
		if(get_magic_quotes_gpc()) {
			//Si magic quotes esta activado
			// los datos ya estan escapados
			return $what;
		} else {
			//los datos no estan escapados, evitar
			// inyecciones SQL
			return $this->DB->escape_string($what);
			//return(mysql_real_escape_string($what));
		}
	}
	
	///Devuelve el identificador de la ltima petici� de inserci� realizada a la base de datos.
	function insertId() {
		//return(mysql_insert_id());
		return $this->DB->insertId();
	}

}

?>