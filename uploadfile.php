<?php
/*
  Copyright (c) 2005-2006 Alberto Monta�ola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//Cargar la configuraci�n de la aplicaci�n
require_once(dirname(__FILE__) . "/DefaultConfig.php");

//phpinfo();
require_once(dirname(__FILE__) . "/" . $APF["vod.plug"]);

//echo("this does not work");

//echo($_FILES["sourcefile"]["tmp_name"]);

$file_path=$_FILES["sourcefile"]["tmp_name"];
$user_file_name=basename($_FILES["sourcefile"]["name"]);
$error=$_FILES["sourcefile"]["error"];
$size=$_FILES["sourcefile"]["size"];

if(!empty($file_path) && $size!=0 && $error==0) {
	//echo($file_path);
	//echo("<br>");
	//echo(basename($user_file_name));
	$APF_VOD->uploadVideoFile($file_path,$user_file_name);
} else {
	echo("Upload error");
}

//phpinfo();

?>