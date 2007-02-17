<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/dbBase.php");

///Classe base de datos (MySQL)
class ApfMysqlDB implements iDB {
	private $user;
	private $password;
	private $database;
	private $host;
	private $query_count=0;
	private $link;
	private $result;

	///Constructor
	public function __construct($user="",$password="",$database="",$host="") {
		$this->user=$user;
		$this->password=$password;
		$this->database=$database;
		$this->host=$host;
		$this->link=null;
		//$this->connect();
	}

	public function __destruct() {
		$this->disconnect();
	}
	
	/** Conectar a la base de datos */
	public function connect() {
		if($this->link!=null) return; //Ya estamos connectados
		$this->link=@mysql_connect($this->host,$this->user,$this->password);
		if(!$this->link) {
			throw new CannotConnectDBException($this->database,$this->host,
			$this->user,!empty($this->password));
		}
		if(!mysql_select_db($this->database,$this->link)) {
			throw new DBNotFoundException($this->database);
		}
	}

	public function disconnect() {
		if($this->link!=null) {
			mysql_close($this->link);
		}
		$this->link=null;
	}

	/// Enviar petición sql a la base de datos.
	/// @param query Petición
	function query($query) {
		if($this->link==null) $this->connect();
		$this->query_count++;
		$this->result=mysql_query($query,$this->link);
		//echo($query);
		if(!$this->result) {
			throw new DBQueryException($query,$this->getError());
		}
		return 1;
	}
	
	///Obtener el último error producido en la última consulta.
	function getError() {
		return mysql_error($this->link);
	}

	///Obtener el vector de datos devueltos después de la última petición
	function fetchArray() {
		if($this->result) {
			return (mysql_fetch_array($this->result));
		} else {
			return null;
		}
	}

	///Escapa la cadena
	function escape_string($what) {
		return(mysql_real_escape_string($what));
	}
	
	///Devuelve el identificador de la última petición de inserción realizada a la base de datos.
	function insertId() {
		return(mysql_insert_id());
	}
	
	function getQueryCount() {
		return $this->query_count;
	}

}

///Crea el objecto de la base de datos
function createDBObject($user="",$password="",$database="",$host="") {
	return new ApfMysqlDB($user,$password,$database,$host);
}

?>