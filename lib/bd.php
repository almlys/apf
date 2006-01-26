<?php
/*
  Copyright (c) 2005 Alberto Monta�ola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.
*/

class ApfDB {
	var $query_count=0;
	var $link;
	var $result;

	//Constructor
	function ApfDB($user="apf",$password="123",$database="apf_test",$host="localhost") {
		$this->user=$user;
		$this->password=$password;
		$this->database=$database;
		$this->host=$host;
		//$this->connect();
	}
	
	/** Connect to the database */
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
	
	function getError() {
		return mysql_error();
	}
	
	function fetchArray() {
		//echo($this->result . $this->link);
		if($this->result) {
			return (mysql_fetch_array($this->result));
		}
	}

}



?>