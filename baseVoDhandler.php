<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

///Classe que define un video, con sus respectiva propiedades
class ApfVoDFile {

} //End Class ApfVodFile

///Clase virtual que define la interfaz base para cada tipo de servidor VoD
///Esta clase contine los hooks que relacionan el servidor VoD con la aplicación
class ApfBaseVoDHandler {

	///Constructor
	function ApfBaseVoDHandler() {}

	///Indica al VoD, que un nuevo fichero de video a sido subido al servidor
	///@param path Ruta al fichero en el sistema local
	///@param filename Nombre del fichero especificado por el usuario
	///@return Devuelve un objecto 
	function UploadVideoFile($path,$filename) {}

	///Pasa un nombre de fichero al VoD para validar antes de ser subido
	/// @param path Nombre fichero
	/// @return Devuelve verdadero si el fichero es aceptable
	function CheckVideoFileBeforeUpload($path) {
		return true;
	}

} //End Class ApfBaseVodHandler

?>