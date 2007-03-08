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
	///@return Devuelve verdadero en caso de éxito
	function UploadVideoFile($path,$filename);

	///Pasa un nombre de fichero al VoD para validar antes de ser subido
	/// @param path Nombre fichero
	/// @return Devuelve verdadero si el fichero es aceptable
	function CheckVideoFileBeforeUpload($path);

	/// Notifica al VoD que un fichero nuevo a sido subido
	/// @param path Ruta al fichero
	function notifyNewUploadedVideo($path);

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
	///@param file Nombre del fichero especificado por el usuario
	///@return Devuelve el nombre del fichero subido al VoD server
	function UploadVideoFile($path,$file) {
		//echo($path . "--" . $file);
		if(!ereg('^[^./][^/]*$',$file)) {
			throw new InvalidFileException($file);
		}
		//echo($path . "--" . $file);
		$file=cleanFileName($file);
		if(empty($file)) {
			throw new InvalidFileException($file);
		}
		//echo($path . "--" . $file);
		global $APF;
		if(ereg('^/',$APF['upload.videos'])) {
			$imgdest=$APF['upload.videos'];
		} else {
			$imgdest=$APF['system.path'] . '/' . $APF['upload.videos'];
		}
		$imgdest.='/' . $file;
		$count=0;
		$imgdestck=$imgdest;
		while(file_exists($imgdestck)) {
			$imgdestck=ereg_replace('\.(.*)$',"_$count.\\1",$imgdest);
			$file=ereg_replace('\.(.*)$',"_$count.\\1",$file);
			$count++;
		}
		$imgdest=$imgdestck;
		//echo($path . "--" . $imgdest);
		if(!rename($path,$imgdest)) {
			throw new InvalidFileException($file);
		}
		$this->notifyNewUploadedVideo($imgdest);
		return $file;
	}

	///Pasa un nombre de fichero al VoD para validar antes de ser subido
	/// @param path Nombre fichero
	/// @return Devuelve verdadero si el fichero es aceptable
	function CheckVideoFileBeforeUpload($path) {
		ereg('\.(.*)$',$path,$out);
		$check=array('avi','mpg');
		if(in_array($out[1],$check)) {
			return true;
		}
		return false;
	}

} //End Class ApfBaseVodHandler

?>