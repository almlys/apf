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
/*$APF_STR['en']="strings.en.php"; //english
$APF_STR['es']="strings.es.php"; //Español
$APF_STR['ca']="strings.ca.php"; //Català*/
// ^- Ya no es necessario (los autodectecta)
/* Final del listado */

//Vector de idiomas soportados
//$APF['languages']=array("es","en","ca");
//El vector esta en Default¢onfig.php

/**
	Gestor de idiomas, para cadenas localizadas
*/
class ApfLocal {
	var $language;
	/// Constructor
	/// @param lan Vector de idiomas
	function ApfLocal($lan) {
		$this->setLanguageVector($lan);
	}
	///Fijar el vector
	/// @param lan Vector de idiomas
	function setLanguageVector($lan) {
		$this->language=$lan;
	}
	///Devolver el vector
	/// @return Vector de idomas
	function getLanguageVector() {
		return $this->language;
	}
	///Obtener el idioma por defecto
	/// @return Idioma por defecto
	function getDefaultLanguage() {
		return(substr($this->language[0],0,2));
	}
	/// Obtener una cadena traducida.
	/// @param key Clave.
	/// @return Cadena traducida.
	/// @note Si la cadena no existe, buscara una alternativa si esta disponible en el vector.
	function get($key) {
		global $APF,$APF_STR,$APF_STRINGS;
		$language=$this->language; //$APF['accept_language']
		//Primero comprueba la Cache
		$ret=$APF_STRINGS[$key];
		$i=0;
		$lan=substr($language[$i],0,2); $i++;
		//echo("init-$lan-$i-$key-$ret-" . $APF_STRINGS["id"] . "<br>");
		if(!empty($ret)) {
			////$i--;
			//Asegurate que el resultado obtenido corresponde a nuestro idioma
			////if($APF_STRINGS["id"]==$lan) {
				return $ret;
			////}
		}
		//Inicialiación, o fallo en la cache, volver a cargar cadenas
		while(!empty($lan)) {
			$file=dirname(__FILE__) . "/strings.$lan.php";
			while(!empty($lan) && !is_readable($file)) {
				$lan=substr($language[$i],0,2); $i++;
			}
			/*while(!empty($lan) && empty($APF_STR[$lan])) {
				$lan=substr($language[$i],0,2); $i++;
			}*/
			//echo("$lan-$i-<br>");
			if(!empty($lan)) {
				$file=dirname(__FILE__) . "/strings.$lan.php";
				//echo("$file<br>");
				//if(!empty($APF_STR[$lan]) && is_file($file)) {
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
				//}
				$lan="NULL";
			}
		}
		return("UNSTRANSLATED:$key");
	}

} //End ApfLocal class


?>