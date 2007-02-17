<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/AuthBase.php");

///Modulo de autenticaci�.
class ApfAuth extends ApfAuthBase implements iAuth {
	var $DB; ///< Base de datos

	///Constructor
	///@param database Referencia a un objeto base de datos, que contenga la tabla de usuarios
	function __construct(&$database) {
		$this->DB=&$database;
	}

	///Verifica que el usuario esta debidamente autenticado.
	///@param uid Identificador del usuario.
	///@param hash Hash de comprovación.
	///@return Verdadero si los datos fueron correctos, falso en caso de fallar
	function verify($uid,$hash) {
		$query="select hash,admin from vid_users where uid=$uid";
		$this->DB->query($query);
		$vals=$this->DB->fetchArray();
		if($vals[0]==$hash) {
			$this->level=$vals[1];
			$this->uid=$uid;
			$this->hash=$hash;
			$this->login=$login;
			$this->authed=True;
			return true;
		} else {
			$this->level=0;
			$this->uid=0;
			$this->hash=0;
			$this->login="";
			$this->authed=False;
			return false;
		}
	}

	public function getPassword($login,$enc="md5") {
		if ($enc!="md5") throw new NotImplementedException($enc);
		$query="select password,uid,admin from vid_users where name=\"$login\"";
		$this->DB->query($query);
		$vals=$this->DB->fetchArray();
		$this->login=$login;
		$this->level=$vals[2];
		$this->uid=$vals[1];
		$this->authed=False;
		return $vals[0];
	}

	public function authSuccesfull() {
		$query="update vid_users set last=NOW(), hash=\"" . $this->hash . "\" where uid=" . $this->uid;
		$this->DB->query($query);
	}


} //end class

//Crear instancia del modulo de autenticaci�
function createAuthObject($database) {
	return new ApfAuth($database);
}

?>