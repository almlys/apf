<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/dbBase.php");

///Classe base de datos (MySQL)
class ApfMysqlDB implements iDB {
	private $query_count=0;
	private $link;
	private $result;

	///Constructor
	function ApfDB($user="",$password="",$database="",$host="") {
		$this->user=$user;
		$this->password=$password;
		$this->database=$database;
		$this->host=$host;
		$this->link=null;
		//$this->connect();
	}
	
	/** Conectar a la base de datos */
	function connect() {
		if($this->link!=null) return;
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

	/// Enviar petici� sql a la base de datos.
	/// @param query Petici�
	function query($query) {
		if($this->link==null) $this->connect();
		$this->query_count++;
		$this->result=mysql_query($query,$this->link);
		//echo($query);
		//echo($this->result . $this->link);
		if(!$this->result) {
			//echo(mysql_error());
			return 0;
		}
		return 1;
	}
	
	///Obtener el ltimo error producido en la ltima consulta.
	function getError() {
		return mysql_error($this->link);
	}

	///Obtener el vector de datos devueltos despu� de la ltima petici�.
	function fetchArray() {
		//echo($this->result . $this->link);
		if($this->result) {
			return (mysql_fetch_array($this->result));
		} else {
			return null;
		}
	}

	function escape_string($what) {
		return(mysql_real_escape_string($what));
	}
	
	///Devuelve el identificador de la ltima petici� de inserci� realizada a la base de datos.
	function insertId() {
		return(mysql_insert_id());
	}

}

///Crea el objecto de la base de datos
function createDBObject($user="",$password="",$database="",$host="") {
	return new ApfDB($user,$password,$database,$host);
}

?>