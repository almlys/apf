<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

/// define un video, con sus respectiva propiedades
interface iVodFile {

}

///interfaz base para cada tipo de servidor VoD
///contiene los hooks que relacionan el servidor VoD con la aplicación
interface iVoDHandler {
	///Indica al VoD, que un nuevo fichero de video a sido subido al servidor
	///@param path Ruta al fichero en el sistema local
	///@param filename Nombre del fichero especificado por el usuario
	///@return Devuelve un objecto ApfVoDFile
	function UploadVideoFile($path,$filename);

	///Pasa un nombre de fichero al VoD para validar antes de ser subido
	/// @param path Nombre fichero
	/// @return Devuelve verdadero si el fichero es aceptable
	function CheckVideoFileBeforeUpload($path);

}

///Classe que define un video, con sus respectiva propiedades
class ApfVoDFile implements iVoDFile {

} //End Class ApfVodFile

///Clase virtual que define la interfaz base para cada tipo de servidor VoD
///Esta clase contine los hooks que relacionan el servidor VoD con la aplicación
abstract class ApfBaseVoDHandler implements iVoDHandler {

	///Constructor
	//function __construct() {}

	///Indica al VoD, que un nuevo fichero de video a sido subido al servidor
	///@param path Ruta al fichero en el sistema local
	///@param filename Nombre del fichero especificado por el usuario
	///@return Devuelve un objecto ApfVoDFile
	function UploadVideoFile($path,$filename) {}

	///Pasa un nombre de fichero al VoD para validar antes de ser subido
	/// @param path Nombre fichero
	/// @return Devuelve verdadero si el fichero es aceptable
	function CheckVideoFileBeforeUpload($path) {
		return true;
	}

} //End Class ApfBaseVodHandler

?>