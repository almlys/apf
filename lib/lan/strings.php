<?
/*
  Copyright (c) 2005 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

   Strings file, for localized versions.
   Add new language definitions here.

   Fichero de cadenas, para versiones localizadas.
   Añadir nuevas definiciones de idioma en este fichero.

*/
/* Set Here new language resources */
$APF_STR['en']="strings.en.php"; //english
$APF_STR['es']="strings.es.php"; //Español
$APF_STR['ca']="strings.ca.php"; //Català
/* End language resources */

//define allowed languages
$APF['languages']=array("es","en","ca");

/**
	Language Manager - object (for localized strings)
*/
class ApfLocal {
	var $language;
	/** Constructor
		/param Supported language code vector. (es,ca,en...)
		/param The vector is a comma separated list
	*/
	function ApfLocal($lan) {
		$this->setLanguageVector($lan);
	}
	function setLanguageVector($lan) {
		$this->language=$lan;
	}
	function getDefaultLanguage() {
		return(substr($this->language[0],0,2));
	}
	/**
		Get a localized string
		/param String Key
		/return Translated String in local language
		If not exists it will search an alternative localization, if it is available in the language vector.
	*/ 
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