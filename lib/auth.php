<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/


///Modulo de autenticación.
class ApfAuth {
	var $login;
	var $hash;
	var $uid;
	var $DB;

	///Constructor
	function ApfAuth(&$database) {
		$this->DB=&$database;
	}
	
	///Autentica el usuario
	///@param login Login del usuario
	///@param pass Password del usuario a comprovar
	///@param sid Session del usuario
	function authenticate($login,$pass,$sid=0) {
		$hash=md5($pass);
		$query="select password,uid,admin from vid_users where name=\"$login\"";
		$this->DB->query($query);
		$vals=$this->DB->fetchArray() or return false;
		if($vals[0]==$hash) {
			$this->login=$login;
			$this->hash=$this->getHash($sid . $login . $hash);
			$this->level=$vals[2];
			$this->uid=$vals[1];
			$query="update vid_users set last=NOW(), hash=\"" . $this->hash . "\" where uid=" . $this->uid;
			$this->DB->query($query) or return false;
			return true;
		}
		return false;
	}
	
	///Verifica que el usuario esta debidamente autenticado.
	///@param uid Identificador del usuario.
	///@param hash Hash de comprovación.
	function verify($uid,$hash) {
		$query="select hash from vid_users where uid=$uid";
		$this->DB->query($query) or return false;
		$vals=$this->DB->fetchArray();
		if($vals[0]==$hash) {
			return true;
		} else {
			return false;
		}
	}

	///Genera un hash de una información aleatoria
	///@param what Entrada de datos diversos (nombre, ip, etc..)
	///Es possible cambiar la función, lo importante es que el valor
	///devuelto no pueda ser predecido por un posible atacante.
	function getHash($what="") {
		return(sha1(rand() . $what . uniqid(time() . rand())));
	}

} //end class

?>