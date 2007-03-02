<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

interface iAuth {
	///Autentica el usuario
	///@param login Login del usuario
	///@param pass Password o hash del password del usuario a comprovar
	///@param enc Codificación del password (plain: Password en texto plano, md5 hash MD5 del password)
	///@param sid Session del usuario
	///@return Verdadero si los datos fueron correctos, falso en caso de fallar
	public function authenticate($login,$pass,$enc="plain",$sid=0);
	///Verifica que el usuario esta debidamente autenticado.
	///@param uid Identificador del usuario.
	///@param hash Hash de comprovación.
	///@return Verdadero si los datos fueron correctos, falso en caso de fallar
	public function verify($uid,$hash);
	///Genera un hash de una información aleatoria
	///@param what Entrada de datos diversos (nombre, ip, etc..)
	///@return Devuelve un identificador único
	///Es possible cambiar la función, lo importante es que el valor
	///devuelto no pueda ser predecido por un posible atacante.
	///Esto se utiliza principalmente para recuperar una sessión antigua, ya
	/// que las sesiones PHP no son persistentes, y expiran al cabo de un tiempo
	/// o al cerrar el navegador.
	///@note Es necesario reflejar los cambios realizados en esta función en el las 
	/// funciones de validación del script upload.py, en caso contrario
	/// el script devolverá error de validación del hash de autenticación.
	public function getHash($what="");
	///Obtener el password asociado a ese Login
	///@param enc Codificación (plain: plana, md5 hash md5 del password, etc...)
	public function getPassword($login,$enc="md5");
	//Funciones CHAP (Desafio-respuesta)
	///Obtiene un desafio (CHAP)
	public function challenge();
	///Valida el usuario con el desafio fijado
	///@param $login Login del usuario
	///@param $challenge Desafio que hemos enviado previamente al cliente
	///@param $client_hash Respuesta al desafio por el cliente
	///@param $alg Algoritmo de Hash utilizado
	public function validate($login,$challenge,$client_hash,$alg="md5");
	///Realizar el MD5(username + hashed_pass + challenge)
	///@param login Login del usuario
	///@param pass Password o hash del password del usuario a comprovar
	///@param challenge Desafio
	///@param alg Algoritmo de Hash
	public function hashme($login,$pass,$challenge,$alg="md5");

	///Hook llamado solo cuando la autenticación se considera exitosa
	public function authSuccesfull();
	public function getLogin();
	public function getLevel();
	public function getUID();
	public function getAuthHash();

	//Plain auth
	//verify(uid,hash)
	//if failed then
	//authenticate(login,pass)

	//CHAP auth
	//verify(uid,hash)
	//if failed then
	//send challenge()
	//validate(login,challenge,client_hash)
}

///Clase base abstracta del modulo de autenticación
abstract class ApfAuthBase implements iAuth {
	protected $login=""; ///< Nombre del usuario
	protected $hash=0;	///< Hash del usuario
	protected $uid=0; ///< Identificador del usuario
	protected $level=0; ///< Nivel de acceso del usuario
	protected $authed=False; ///< Indica autenticación satisfactoria

	public function getLogin() {
		if($this->authed) return $this->login;
		return "";
	}

	public function getLevel() {
		if($this->authed) return $this->level;
		return 0;
	}

	public function getUID() {
		if($this->authed) return $this->uid;
		return 0;
	}

	public function getAuthHash() {
		if($this->authed) return $this->hash;
		return "";
	}


	public function getHash($what="") {
		return(sha1(rand() . $what . uniqid(time() . rand())));
	}

	///Autentica el usuario
	///@param login Login del usuario
	///@param pass Password del usuario a comprovar
	///@param enc Codificación del password (plain: Password en texto plano, md5 hash MD5 del password)
	///@param sid Session del usuario
	///@return Verdadero si los datos fueron correctos, falso en caso de fallar
	function authenticate($login,$pass,$enc='plain',$sid=0) {
		$this->authed=False;
		switch ($enc) {
			case 'md5':
				$hash=$pass;
				break;
			case 'plain':
				$hash=md5($pass);
				break;
			default:
				throw new NotImplementedException($enc);
		}
		//Obtener el password y otros metadatos
		$cpass=$this->getPassword($login);
		if($cpass==$hash) {
			$this->hash=$this->getHash($sid . $login . $hash);
			$this->authed=True;
			$this->authSuccesfull();
			return true;
		}
		return false;
	}

	public function challenge() {
		return $this->getHash(md5(time()));
	}

	public function hashme($login,$pass,$challenge,$alg='md5') {
		if ($alg!='md5') throw new NotImplementedException($enc);
		return md5($login . $pass . $challenge);
	}

	public function validate($login,$challenge,$client_hash,$alg='md5') {
		$this->authed=False;
		$cpass=$this->getPassword($login);
		$hash_check=$this->hashme($login,$cpass,$challenge,$alg);
		if($hash_check==$challenge) {
			$this->authed=True;
			return True;
		}
		return False;
	}



} //End ApfAuthBase class

?>