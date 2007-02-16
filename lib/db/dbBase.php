<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

///Interfaz de Base de Datos
interface iDB {
	///Connectar a la base de datos
	public function connect();
	///Desconectar de la base de datos
	public function disconnect();
	///Realizar una petición a la base de datos
	public function query($query);
	///Obtener último error producido por la última petición
	public function getError();
	///Obtener array de resultados
	public function fetchArray();
	///Obtener id del último registro insertado
	public function insertId();
	///Escapar los datos
	public function escape_string($data);
}

class DatabaseException extends Exception {}

class CannotConnectDBException extends DatabaseException {
	public function __construct($db,$host,$user,$u_pass) {
		if ($u_pass) $u="Yes";
		else $u="No";
		if (empty($host)) $host="localhost";
		parent::__construct("Cannot connect to $db#$user@$host using password $u");
	}
}

class DBNotFoundException extends DatabaseException {
	public function __construct($db) {
		parent::__construct("Databse $db does not exists");
	}
}

class DBQueryException extends DatabaseException {
	public function __construct($query,$error) {
		parent::__construct("Error: $error when attempting to run: $query");
	}
}

?>