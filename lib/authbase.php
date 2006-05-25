<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/


///Clase base abstracta del modulo de autenticación
class ApfAuthBase {
	var $login; ///< Nombre del usuario
	var $hash;	///< Hash del usuario
	var $uid; ///< Identificador del usuario
	var $level; ///< Nivel de acceso del usuario

	///Autentica el usuario
	///@param login Login del usuario
	///@param pass Password del usuario a comprovar
	///@param sid Session del usuario
	///@return Verdadero si los datos fueron correctos, falso en caso de fallar
	function authenticate($login,$pass,$sid=0) {
		return false;
	}

	///Verifica que el usuario esta debidamente autenticado.
	///@param uid Identificador del usuario.
	///@param hash Hash de comprovación.
	///@return Verdadero si los datos fueron correctos, falso en caso de fallar
	function verify($uid,$hash) {
		return false;
	}

	///Genera un hash de una información aleatoria
	///@param what Entrada de datos diversos (nombre, ip, etc..)
	///@return Devuelve un identificador unico
	///Es possible cambiar la función, lo importante es que el valor
	///devuelto no pueda ser predecido por un posible atacante.
	///Esto se utiliza principalmente para recuperar una sessión antigua, ya
	/// que las sesiones PHP no son persistentes, y expiran al cabo de un tiempo
	/// o al cerrar el navegador.
	///@note Es necesario reflejar los cambios realizados en esta función en el las 
	/// funciones de validación del script upload.py, en caso contrario el script devolverá
	/// error de validación del hash de autenticación.
	function getHash($what="") {
		return(sha1(rand() . $what . uniqid(time() . rand())));
	}
} //End ApfAuthBase class

?>