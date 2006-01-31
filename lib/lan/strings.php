<?
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

/*
   Fichero de cadenas, para versiones localizadas.
   Añadir nuevas definiciones de idioma en este fichero.
*/

/* Recuros de idiomas */
$APF_STR['en']="strings.en.php"; //english
$APF_STR['es']="strings.es.php"; //Español
$APF_STR['ca']="strings.ca.php"; //Català
/* Final del listado */

//Vector de idiomas soportados
$APF['languages']=array("es","en","ca");

/**
	Gestor de idiomas, para cadenas localizadas
*/
class ApfLocal {
	var $language;
	/// Constructor
	/// @param lan Vector de idiomas (lista separada por comas)
	function ApfLocal($lan) {
		$this->setLanguageVector($lan);
	}
	///Fijar el vector
	/// @param lan Vector de idiomas (lista separada por comas)
	function setLanguageVector($lan) {
		$this->language=$lan;
	}
	///Obtener el idioma por defecto
	function getDefaultLanguage() {
		return(substr($this->language[0],0,2));
	}
	/// Obtener una cadena traducida.
	/// @param key Clave.
	/// @return Cadena traducida.
	/// @note Si la cadena no existe, buscara una alternativa si esta disponible en el vector. (Este codigo tiene algún bug, al usar una cache interna).
	function get($key) {
		global $APF,$APF_STR,$APF_STRINGS;
		$language=$this->language; //$APF['accept_language']
		$ret=$APF_STRINGS[$key];
		$i=0;
		$lan=substr($language[$i],0,2); $i++;
		//echo("init-$lan-$i-$key-$ret<br>");
		if(!empty($ret) && $APF_STRINGS[$id]==$lan) {
			return $ret;
		}
		while(!empty($lan)) {
			while(!empty($lan) && empty($APF_STR[$lan])) {
				$lan=substr($language[$i],0,2); $i++;
			}
			//echo("$lan-$i-<br>");
			if(!empty($lan)) {
				$file=dirname(__FILE__) . "/" . $APF_STR[$lan];
				//echo("$file<br>");
				if(!empty($APF_STR[$lan]) && is_file($file)) {
					include_once($file);
					$ret=$APF_STRINGS[$key];
					if(!empty($ret)) { return($ret); }
				}
				$lan="NULL";
			}
		}
		return("UNSTRANSLATED:$key");
	}

}


?>