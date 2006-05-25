<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/authbase.php");

///Modulo de autenticación.
class ApfAuth extends ApfAuthBase {
	var $DB; ///< Base de datos

	///Constructor
	function ApfAuth(&$database) {
		$this->DB=&$database;
	}
	
	///Autentica el usuario
	///@param login Login del usuario
	///@param pass Password del usuario a comprovar
	///@param sid Session del usuario
	///@return Verdadero si los datos fueron correctos, falso en caso de fallar
	function authenticate($login,$pass,$sid=0) {
		$hash=md5($pass);
		$query="select password,uid,admin from vid_users where name=\"$login\"";
		if(!$this->DB->query($query)) return false;
		//echo($query);
		$vals=$this->DB->fetchArray();
		//if(!$vals) return(false);
		if($vals[0]==$hash) {
			$this->login=$login;
			$this->hash=$this->getHash($sid . $login . $hash);
			$this->level=$vals[2];
			$this->uid=$vals[1];
			$query="update vid_users set last=NOW(), hash=\"" . $this->hash . "\" where uid=" . $this->uid;
			//echo($query);
			if (!$this->DB->query($query)) return false;
			return true;
		}
		return false;
	}
	
	///Verifica que el usuario esta debidamente autenticado.
	///@param uid Identificador del usuario.
	///@param hash Hash de comprovación.
	///@return Verdadero si los datos fueron correctos, falso en caso de fallar
	function verify($uid,$hash) {
		$query="select hash,admin from vid_users where uid=$uid";
		if (!$this->DB->query($query)) return false;
		$vals=$this->DB->fetchArray();
		if($vals[0]==$hash) {
			$this->level=$vals[1];
			$this->uid=$uid;
			$this->hash=$hash;
			$this->login=$login;
			return true;
		} else {
			$this->level=0;
			$this->uid=0;
			$this->hash=0;
			$this->login="";
			return false;
		}
	}

} //end class

//Crear instancia del modulo de autenticación
function createAuthObject($database) {
	return new ApfAuth($database);
}

?>