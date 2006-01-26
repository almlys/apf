<?php
/*
  Copyright (c) 2005 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.
*/

$APF['start_time']=microtime(); //Calcular el tiempo de generación de página
/* Cargar contenido localizado de forma dinamica */
include_once(dirname(__FILE__) . "/lan/strings.php");
include_once(dirname(__FILE__) . "/bd.php");
include_once(dirname(__FILE__) . "/auth.php");
include_once(dirname(__FILE__) . "/tree.php");
include_once(dirname(__FILE__) . "/folder.php");

/**
	Base document class
*/
class ApfBaseDocument {
	var $title="Untitled";
	var $start_time=0;
	var $lan;
	var $state=0; /** state=0 (no html tags where sent) // state=1 (html tags sent) */
	var $charset="iso-8859-15";
	var $generator="App Writen with: Vim, Quanta Plus, and Cssed";
	var $stylesheets;
	var $path="tfc/";
	
	/** Constructor */
	function ApfBaseDocument($title="Untitled") {
		global $APF;
		$this->start_time=$APF['start_time'];
		$this->title=$title;
		/* Get the default language vector... */
		if($_GET["lan"]) { 
			$language=str_replace(",", "00",substr($_GET["lan"],0,2)) . "-nav,"; 
		}
		$ACCEPT_LANGUAGE=explode(",",$language . $_SERVER["HTTP_ACCEPT_LANGUAGE"] . ",en-def");
		
		//Filtrar y permitir solo estos idiomas para evitar sorpresas desagradables
		$allow=$APF["languages"];
		//array("es","en","ca");
		
		$f=0;
		$imax=count($ACCEPT_LANGUAGE);
		$emax=count($allow);
		for ($i=0; $i<$imax; $i++) {
			for ($e=0; $e<$emax; $e++) {
				if(substr($ACCEPT_LANGUAGE[$i],0,2)==$allow[$e]) {
					$final[$f++]=$ACCEPT_LANGUAGE[$i];
				}
			}
		}
		/* End setting up language */
		$this->lan=new ApfLocal($final);

	}
	
	/** Obtener el tiempo del script actual */
	function getTime($how="") {
		$START_TIME=$this->start_time; //Set startup time
		$end_time=microtime();
		$parcial_calc=explode(' ',$START_TIME . ' ' . $end_time);
		$duration=sprintf('%01.8f',($parcial_calc[2]+$parcial_calc[3])-($parcial_calc[0]+$parcial_calc[1]));
		switch($how) {
		case "s":
			$sufix=" s.";
			break;
		case "seconds":
			$sufix=" " . $this->lan->get("seconds");
			break;
		case "ms":
			$sufix=" ms.";
			$duration=$duration*1000;
			break;
		case "milliseconds":
			$sufix=" " . $this->lan->get("milliseconds");
			$duration=$duration*1000;
			break;
		case "microseconds":
			$sufix=" " . $this->lan->get("microseconds");
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
	
	/** Generates and prints the document Head */
	function head() {
		if($state==1) return;
		$state=1;
		?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<title><?php echo($this->title); ?></title>
<?php
		//Set Meta Tags
		echo("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $this->charset . "\">\n");
		echo("<meta name=\"Language\" content=\"" . substr($this->lan->language[0],0,2) . "\">\n");
		if(!empty($this->description)) {
			echo("<meta name=\"description\" content=\"" . $this->description . "\">\n");
		}
		if(!empty($this->keywords)) {
			echo("<meta name=\"keywords\" content=\"" . $this->keywords . "\">\n");
		}
		echo("<meta name=\"Generator\" content=\"" . $this->generator . "\">\n");
		//end setting meta info
		
		//set here the stylesheets
		$i=0;
		$styles=$this->stylesheets;
		while(!empty($styles[$i][0])) {
			//main
			echo('<link rel="');
			if($i!=0) echo("alternate ");
			echo('stylesheet" title="' . $styles[$i][0] .'" href="' . $styles[$i++][1] . '" type="text/css">');
		}
		?>
</head>
<body>
<?php
	}
	
	/** Generates and prints the document Footer */
	function foot() {
		?>
</body>
</html>
<?php
	
	}
	
	/** Shows an error message and ends */
	function error($msg,$title="Error") {
		if($this->state==0) $this->head();
		if($this->state==2) ApfBaseDocument::head();
		?>
<center>
<table bgcolor="Yellow">
<TR><td bgcolor="Red"><b><font color="yellow"><?php echo($title); ?></font></b></TD></TR>
<tr><td><font color="red"><?php 
		echo($msg);
		echo("<br> Requesting resource: " . $_SERVER["REQUEST_URI"]);
		echo("<br> <a href=\"" . $_SERVER["HTTP_REFERER"] . "\">Return to source page</a>");
		?></font></TD></tr>
</table>
</center>
<?php
		$this->foot();
		exit();
	}
	
	/** Returns full base URL protocol://base_install to the root of the install */
	function buildBaseURI() {
		$proto=$this->getProtocol();
		$port=$this->getPort();
		if (($proto=="http" && $port==80) || ($proto=="https" && $port==443)) {
			$port="";
		} else {
			$port=":" . $port;
		}
		return $proto . "://"  . $_SERVER['SERVER_NAME'] . $port . "/" . $this->path;
	}
	
	function getProtocol() {
		return ($HTTP_SERVER_VARS["HTTPS"]=="on" ? "https" : "http");
	}
	
	function getPort() {
		return $_SERVER["SERVER_PORT"];
	}

	//Shows the ip of the client
	//
	// Where: $format
	//   short shows the ip connection, rshort shows the cue of forwarded ip's,
	//   and long or no value shows all addresses.
	function getRemoteAddress($how="") {
		if($format=="short") {
			return($_SERVER["REMOTE_ADDR"]);
		} elseif($format=="rshort") {
			if($_SERVER["HTTP_X_FORWARDED_FOR"])
				return($_SERVER["HTTP_X_FORWARDED_FOR"]);
			elseif($_SERVER["HTTP_CLIENT_IP"])
				return($_SERVER["HTTP_CLIENT_IP"]);
			else
				return($REMOTE_ADDR);
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

	/** Implementation of getlastmod not working on new versions of php and apache */
	function getLastMod() {
		return filemtime($_SERVER["SCRIPT_FILENAME"]);
	}

}


class ApfDocument extends ApfBaseDocument {
	//Contructor
	var $authed=0;
	var $admin=0;
	var $DB;
	var $auth;
	function ApfDocument($title) {
		$this->ApfBaseDocument($title);
		$this->auth=new ApfAuth($this);
		//sessions
		session_name("ApfVoDPHPSID");
		session_start();
		if(!empty($_SESSION["AuthHash"]) && $_COOKIE["ApfVoDAuthHash"]==$_SESSION["AuthHash"]) {
			//echo("authed!");
			//$this->authed=1;
			if($this->auth->verify($_SESSION["uid"],$_SESSION["AuthHash"])) {
				$this->authed=1;
				$this->admin=$_SESSION["admin"];
			} else {
				$this->endSession();
			}
		} else {
			$this->endSession();
		}
	}
	
	function endSession() {
		$_SESSION["AuthHash"]="";
		$_SESSION["admin"]=0;
		$_SESSION["uid"]=0;
		$_SESSION["login"]="";
		setcookie("ApfVoDAuthHash","",time()-36000);
	}
	
	function redirect($to) {
		session_commit();
		header("Location: $to");
		?>
		<html><head><TITLE>Redirecting to <?php echo($to); ?></TITLE>
		</head><body>
		<a href="<?php echo($to); ?>">Click here to continue</a>
		</body>
		</html>
		<?php
		exit();
	}
	
	function checkConnection() {
		$this->state=2;
		if(empty($this->DB)) {
			$this->DB=new ApfDB();
			if($this->DB->connect()) {
				$this->error($this->DB->getError());
			}
		}
	}

	function query($what) {
		$this->checkConnection();
		//echo($what);
		if(!$this->DB->query($what)) {
			$this->error($this->DB->getError());
		}
	}
	
	function fetchArray() {
		return $this->DB->fetchArray();
	}

	function escape_string($what) {
		$this->checkConnection();
		if(get_magic_quotes_gpc()) {
			//Si magic quotes esta activado
			// los datos ya estan escapados
			return $what;
		} else {
			//los datos no estan escapados, evitar
			// inyecciones
			return(mysql_real_escape_string($what));
		}
	}
	
	function insertId() {
		return(mysql_insert_id());
	}

}

?>