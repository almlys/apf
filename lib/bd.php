<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

///Classe base de datos (MySQL)
class ApfDB {
	var $query_count=0;
	var $link;
	var $result;

	///Constructor
	function ApfDB($user="apf",$password="123",$database="apf_test",$host="localhost") {
		$this->user=$user;
		$this->password=$password;
		$this->database=$database;
		$this->host=$host;
		//$this->connect();
	}
	
	/** Conectar a la base de datos */
	function connect() {
		$this->link=@mysql_connect($this->host,$this->user,$this->password);
		if(!$this->link) {
			//echo("Error connecting to the DATABASE!");
			return -1;
		}
		if(!mysql_select_db($this->database,$this->link)) {
			//echo("No database found!.");
			return -2;
		}
		//echo("connected");
		return 0;
	} //end connect

	/// Enviar petición sql a la base de datos.
	/// @param query Petición
	function query($query) {
		$this->query_count++;
		$this->result=mysql_query($query,$this->link);
		//echo($this->result . $this->link);
		if(!$this->result) {
			//echo(mysql_error());
			return 0;
		}
		return 1;
	}
	
	///Obtener el último error producido en la última consulta.
	function getError() {
		return mysql_error();
	}

	///Obtener el vector de datos devueltos después de la última petición.
	function fetchArray() {
		//echo($this->result . $this->link);
		if($this->result) {
			return (mysql_fetch_array($this->result));
		}
	}

}



?>