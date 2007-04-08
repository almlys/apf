<?
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

/*
   Fichero de cadenas, para versiones localizadas.
*/

/**
	Gestor de idiomas, para cadenas localizadas
*/
class ApfLocal {
	static private $language;
	/// Constructor
	/// @param lan Vector de idiomas
	public function __construct() {
		throw new Exception("This class cannot be instanciated");
	}

	/// Inizialización del control de localizaciones
	/// @param lan Idioma forzado
	public static function init($lan="") {
		global $APF;
		/* Obtener vector de idiomas preferidos por el cliente... */
		if($lan) { 
			$language=str_replace(",", "00",substr($lan,0,2)) . "-nav,"; 
		}
		//Fijar idioma por defecto
		$default_language=$APF['default_language'] . "-def";
		
		$ACCEPT_LANGUAGE=explode(",",$language . $_SERVER["HTTP_ACCEPT_LANGUAGE"] . ",$default_language");
		$imax=count($ACCEPT_LANGUAGE);
		if($imax>10) {
			//Acortar el vector a un mínimo de 10 idiomas
			$ACCEPT_LANGUAGE=array_slice($ACCEPT_LANGUAGE,0,10);
			$ACCEPT_LANGUAGE[9]=$default_language;
		}

		//Filtrar y permitir solo estos idiomas para evitar sorpresas desagradables
		$allow=$APF["languages"];
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
		/* Fin de construcción del vector */
		self::setLanguageVector($final);
	}

	///Fijar el vector
	/// @param lan Vector de idiomas
	public static function setLanguageVector($lan) {
		self::$language=$lan;
	}
	///Devolver el vector
	/// @return Vector de idomas
	public static function getLanguageVector() {
		return self::$language;
	}
	///Obtener el idioma por defecto
	/// @return Idioma por defecto
	public static function getDefaultLanguage() {
		return(substr(self::$language[0],0,2));
	}
	/// Obtener una cadena traducida.
	/// @param key Clave.
	/// @return Cadena traducida.
	/// @note Si la cadena no existe, buscara una alternativa si esta disponible en el vector.
	public static function get($key) {
		global $APF,$APF_STRINGS;
		$language=self::$language;
		//Primero comprueba la Cache
		$ret=$APF_STRINGS[$key];
		$i=0;
		$lan=substr($language[$i],0,2); $i++;
		if(!empty($ret)) {
				return $ret; //Hit en la cache
		}
		//Inicialiación, o fallo en la cache, volver a cargar cadenas
		while(!empty($lan)) {
			$file=dirname(__FILE__) . "/strings.$lan.php";
			while(!empty($lan) && !is_readable($file)) {
				$lan=substr($language[$i],0,2); $i++;
			}
			if(!empty($lan)) {
				$file=dirname(__FILE__) . "/strings.$lan.php";
				//guardar y restaurar copia de la antigua estructura (si existe)
				if(!empty($APF_STRINGS["id"])) {
					$bk_copy=$APF_STRINGS;
				}
				require($file);
				$ret=$APF_STRINGS[$key];
				if(!empty($bk_copy["id"])) {
					$APF_STRINGS=$bk_copy;
				}
				if(!empty($ret)) {
					//Guardar en la cache
					$APF_STRINGS[$key]=$ret;
					return($ret); 
				}
				$lan="NULL";
			}
		}
		return("UNSTRANSLATED:$key");
	}

} //End ApfLocal class

//Obtiene la cadena de texto localizada correspondiente a $key
function _t($key) {
	return ApfLocal::get($key);
}

?>