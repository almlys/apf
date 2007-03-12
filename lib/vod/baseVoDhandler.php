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
	/// @param name Nombre del fichero
	/// @param path Ruta al fichero
	function notifyNewUploadedVideo($name,$path);

	/// Borra el vídeo
	/// @param name video a borrar
	function DeleteVideoFile($name);

	/// Notifica al vod el borrado de un fichero
	/// @param name Nombre
	/// @param path Ruta absoluta
	function notifyVideoDeleted($name,$path);

	///Se peticiona la generación de una previsualización
	///@param path Ruta al fichero en el sistema local
	///@param name Nombre del fichero especificado por el usuario
	///@return Devuelve la ruta a la prev
	function GeneratePreview($path,$name);

}

///Classe que define un video, con sus respectiva propiedades
class ApfVoDFile implements iVoDFile {

} //End Class ApfVodFile

///Clase virtual que define la interfaz base para cada tipo de servidor VoD
///Esta clase contine los hooks que relacionan el servidor VoD con la aplicación
abstract class ApfBaseVoDHandler implements iVoDHandler {

	///Constructor
	//function __construct() {}

	function fileCleanner($file) {
		//echo($path . "--" . $file);
		if(!ereg('^[^./][^/]*$',$file)) {
			throw new InvalidFileException($file);
		}
		//echo($path . "--" . $file);
		$file=cleanFileName($file);
		if(empty($file)) {
			throw new InvalidFileException($file);
		}
		return $file;
	}

	function getVideoFolder() {
		global $APF;
		if(ereg('^/',$APF['upload.videos'])) {
			$imgdest=$APF['upload.videos'];
		} else {
			$imgdest=$APF['system.path'] . '/' . $APF['upload.videos'];
		}
		return $imgdest;
	}

	function getImgFolder() {
		global $APF;
		if(ereg('^/',$APF['upload.imgs'])) {
			$imgdest=$APF['upload.imgs'];
		} else {
			$imgdest=$APF['system.path'] . '/' . $APF['upload.imgs'];
		}
		return $imgdest;
	}

	function getDestinationFileName($path,$file) {
		$imgdest=$path;
		$count=0;
		$imgdestck=$imgdest;
		$nfile=$file;
		while(file_exists($imgdestck)) {
			$imgdestck=ereg_replace('\.([a-zA-Z0-9]*)$',"_$count.\\1",$imgdest);
			$nfile=ereg_replace('\.([a-zA-Z0-9]*)$',"_$count.\\1",$file);
			$count++;
		}
		$file=$nfile;
		$imgdest=$imgdestck;
		return array($file,$imgdest);
	}

	///Indica al VoD, que un nuevo fichero de video a sido subido al servidor
	///@param path Ruta al fichero en el sistema local
	///@param file Nombre del fichero especificado por el usuario
	///@return Devuelve el nombre del fichero subido al VoD server
	function UploadVideoFile($path,$file) {
		$file=$this->fileCleanner($file);
		//echo($path . "--" . $file);
		$imgdest=$this->getVideoFolder();
		$imgdest.='/' . $file;
		$r=$this->getDestinationFileName($imgdest,$file);
		$imgdest=$r[1];
		$file=$r[0];
		//echo($path . "--" . $imgdest);
		if(!rename($path,$imgdest)) {
			throw new InvalidFileException($file);
		}
		$this->notifyNewUploadedVideo($file,$imgdest);
		return $file;
	}

	function DeleteVideoFile($file) {
		$file=$this->fileCleanner($file);
		$imgdest=$this->getVideoFolder();
		$imgdest.='/' . $file;
		$this->notifyVideoDeleted($file,$imgdest);
		return unlink($imgdest);
	}

	function GeneratePreview($path,$file) {
		$file=$this->fileCleanner($file);
		$file=ereg_replace('\.([a-zA-Z0-9]*)$',".png",$file);
		$imgdest=$this->getImgFolder();
		$imgdest.='/' . $file;
		$r=$this->getDestinationFileName($imgdest,$file);
		$imgdest=$r[1];
		$file=$r[0];
		
		$this->doPreviewCmd($path,$imgdest);
		
		if(is_file($imgdest)) {
			return $file;
		} else {
			return '';
		}
	}

	function doPreviewCmd($path,$dest) {
		$cmd="ffmpeg -y -i \"$path\" -vframes 1 -ss 00:00:30 -an -vcodec png -f rawvideo -s 320x240 \"$dest\"";
		//echo $cmd;
		system($cmd);
		//if(!is_file($imgdest))
	}


	///Pasa un nombre de fichero al VoD para validar antes de ser subido
	/// @param path Nombre fichero
	/// @return Devuelve verdadero si el fichero es aceptable
	function CheckVideoFileBeforeUpload($path) {
		ereg('\.([a-zA-Z0-9]*)$',$path,$out);
		$check=array('avi','mpg','mov','wmv','mp4');
		if(in_array($out[1],$check)) {
			return true;
		}
		//echo($path . "<br />");
		//print_r($out);
		return false;
	}

} //End Class ApfBaseVodHandler

?>