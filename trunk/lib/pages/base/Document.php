<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/BaseDocument.php");

//Plugins
require_once(dirname(__FILE__) . "/../../" . $APF['auth.plug']);
require_once(dirname(__FILE__) . "/../../" . $APF['db.plug']);

///Documento base, con acceso a la base de datos y control de sesiones.
class ApfDocument extends ApfBaseDocument implements iDocument {
	private $authed=0; ///<Indica si estamos autenticados
	private $admin=0; ///<Indica si tenemos privilegios administrativos
	private $uid=''; ///<Identificador del usuario
	private $login='';
	private $DB; ///<Objecto Base de datos
	private $auth; ///<Objecto de autenticación
	private $page='main'; ///<Nombre de la página
	private $params=array(); ///<Listado de parámetros extra, empezando por &amp;key=value pairs
	private $id=0; ///<Identificador de un recurso (categoria, video, etc...)

	///Constructor
	/// @param $title Título del documento
	/// @param $release_session Indica si liberamos la sessión
	function __construct($title='',$release_session=True) {
		parent::__construct($title);
		// Estilos
		$this->addStyle(_t('default_style'),$this->buildBaseUri('styles/default.css'));
		// Validar usuario
		$this->checkLogedUser($release_session);
		$this->checkPageId();
	}

	/// Empieza la sessión, solo si existe la cookie de usuario
	/// @param $force Fuerza el inicio de sessión
	function startSession($force=False) {
		global $APF;
		//sessions
		if(!isset($_SESSION) and (isset($_COOKIE[$APF['cookie.name']]) or $force)) {
			session_set_cookie_params(0,$APF['cookie.path'],$APF['cookie.domain'],$APF['cookie.secure'],$APF['cookie.http']);
			session_name($APF['session.name']);
			session_start();
		}
	}

	/// Firma y cifra la Cookie
	/// @param $data Datos de entrada
	function CookieCryptAndSign($data) {
		global $APF;
		$data=substr(md5(uniqid() . time()),0,8).$data;
		$signed=md5($data.$APF['crypt.signkey']).$data;
		if($APF['cookie.crypt']) {
			$iv_size=mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			$iv=mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
			$encrypted=mcrypt_encrypt(MCRYPT_RIJNDAEL_256,$APF['crypt.key'],$signed,MCRYPT_MODE_ECB,$iv);
			return trim(base64_encode($encrypted));
		}
		return $signed;
	}

	/// Descifra y verifica la Cookie
	/// @param $data Datos de entrada
	function CookieDecryptAndCheck($data) {
		global $APF;
		if($APF['cookie.crypt']) {
			$iv_size=mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			$iv=mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
			$decrypted=mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$APF['crypt.key'],base64_decode($data),MCRYPT_MODE_ECB,$iv);
			$data=trim($decrypted);
		}
		$signature=substr($data,0,32);
		$data=substr($data,32);
		if($signature!=md5($data.$APF['crypt.signkey'])) {
			return false;
		}
		return substr($data,8);
	}

	/// Debuelve el Desafio para el método de authenticación Desafio-Respuesta
	function getAuthChallenge() {
		return $this->auth->challenge();
	}

	/// Obtener objeto Auth
	private function getAuthObject() {
		if(!empty($this->auth)) return $this->auth;
		$this->auth=createAuthObject(&$this);
		return $this->auth;
	}

	/// Comprueba el hash de authenticación
	/// @param data Datos de entrda a comprovar
	/// @returns Verdadro si válido
	function checkAuthData($data,$uidx) {
		$this->auth=$this->getAuthObject();
		$data=$this->CookieDecryptAndCheck($data);
		if(!$data) return false;
		$data=explode(" ",$data);
		$uid=$data[0];
		$AuthHash=$data[1];
		$expire=intval($data[2]);
		// Comprovación de expiración de sessión
		if($expire-time()<=0) {
			return false;
		}
		if($uid!=$uid) {
			return false;
		}
		if(!empty($AuthHash) and $this->auth->verify($uid,$AuthHash)) {
			$this->authed=1;
			$this->admin=$this->auth->getLevel();
			$this->uid=$this->auth->getUID();
			$this->login=$this->auth->getLogin();
			return true;
		}
		return false;
	}

	/// Comprueba si el usuario ya tienen una sessión existente y valida
	/// @param release_session Indica si debemos liberar la sessión, para qu pueda ser utilizada
	function checkLogedUser($release_session=True) {
		global $APF;
		$this->auth=$this->getAuthObject();
		// 1o, Debe Existir la cookie de usuario
		if(!isset($_COOKIE[$APF['cookie.name']])) return;
		$data=$this->CookieDecryptAndCheck($_COOKIE[$APF['cookie.name']]);
		if(!$data) { //Invalid cookie, delete it
			$this->endSession();
			return;
		}
		$data=explode(" ",$data);
		$uid=$data[0];
		$AuthHash=$data[1];
		$expire=intval($data[2]);
		$refresh_session=False;
		// Comprovación de expiración de sessión
		if($expire-time()<=0) {
			// Ha expirado
			$this->endSession();
			return;
		} elseif($expire-time()<$APF['session.expire']-($APF['session.expire']/4)) {
			$refresh_session=True;
		}
		// Check for session and Start it if it is missing
		if(!isset($_SESSION)) {
			$this->startSession(True);
		}
		// 1o Comprovar en la session
		if(!$refresh_session && !empty($_SESSION['AuthHash']) && $AuthHash==$_SESSION['AuthHash'] && $uid==$_SESSION['uid']) {
			// La sessión ya existe, el usuario esta autenticado y es válido
			$this->authed=1;
			$this->admin=$_SESSION['admin'];
			$this->uid=$_SESSION['uid'];
			$this->login=$_SESSION['login'];
		} elseif(!empty($AuthHash) and $this->auth->verify($uid,$AuthHash)) {
			//No existe datos de sessión validos, comprovar usuario on la DB
			$this->setAuthVars();
		} else {
			$this->endSession();
			return;
		}
		if($refresh_session) {
			//Refrescar la cookie
			session_regenerate_id();
			$this->sendAuthCookie();
		}

		//Liberar la sessión
		if($release_session) {
			$this->release_session();
		}
	}

	private function sendAuthCookie() {
		global $APF;
		$data=$this->CookieCryptAndSign($this->uid . " " . $_SESSION["AuthHash"] . " " . intval(time()+$APF['session.expire']));
		setcookie($APF['cookie.name'],$data,time()+$APF['session.expire'],$APF['cookie.path'],$APF['cookie.domain'],$APF['cookie.secure'],$APF['cookie.http']);
	}

	private function setAuthVars() {
		$this->authed=1;
		$this->admin=$this->auth->getLevel();
		$this->uid=$this->auth->getUID();
		$this->login=$this->auth->getLogin();
		$_SESSION['login']=$this->auth->getLogin();
		$_SESSION['AuthHash']=$this->auth->getAuthHash();
		$_SESSION['admin']=$this->auth->getLevel();
		$_SESSION['uid']=$this->auth->getUID();
	}

	public function authenticate($login,$pass,$enc='plain',$sid=0) {
		if($this->auth->authenticate($login,$pass,$enc,$sid)) {
			$this->setAuthVars();
			$this->sendAuthCookie();
			return True;
		}
		return False;
	}

	public function authValidate($login,$challenge,$client_hash,$alg="md5") {
		if($this->auth->validate($login,$challenge,$client_hash,$alg)) {
			$this->setAuthVars();
			$this->sendAuthCookie();
			return True;
		}
		return False;
	}


	/// Libera la sessión
	function release_session() {
		if(isset($_SESSION)) {
			session_commit();
			unset($_SESSION);
		}
	}
	
	///Finaliza la session
	function endSession() {
		global $APF;
		$this->authed=0;
		$this->admin=0;
		$this->login='';
		$this->uid='';
		setcookie($APF['cookie.name'],False,0,$APF['cookie.path'],$APF['cookie.domain'],$APF['cookie.secure'],$APF['cookie.http']);
		if(isset($_SESSION)) {
			unset($_SESSION['AuthHash']);
			unset($_SESSION['admin']);
			unset($_SESSION['uid']);
			unset($_SESSION['login']);
			session_destroy();
			setcookie($APF['session.name'],False,0,$APF['cookie.path'],$APF['cookie.domain'],$APF['cookie.secure'],$APF['cookie.http']);
			unset($_SESSION);
		}
	}

	///Obtener id de la página
	function checkPageId() {
		if(!empty($_GET['id'])) {
			$this->id=intval($_GET['id']);
			if($this->id<=0) {
				$this->redirect2page("main");
			}
			$this->setParam('id',$this->id);
		}
		if(!empty($_GET['page'])) {
			$this->page=$_GET['page'];
		}
		$this->setParam('page',$this->page);
		$this->setParam('lan',ApfLocal::getDefaultLanguage());
	}

	///Comprobar si tenemos permisos administrativos
	function IAmAdmin() {
		return $this->admin;
	}

	///Comprobar si estamos autenticados
	function IAmAuthenticated() {
		return $this->authed;
	}

	function getLogin() {
		return $this->login;
	}

	function getId() {
		return $this->id;
	}

	function getUID() {
		return $this->uid;
	}

	function setId($id) {
		$this->id=$id;
		$this->setParam('id',$id);
		return $id;
	}

	/// Fija un parametro
	/// @param $key Nombre del parametro
	/// @param $val Valor del parametro
	function setParam($key,$val) {
		$this->params[$key]=$val;
	}

	/// Obtiene un parametro
	/// @param $key Nombre del parametro
	function getParam($key) {
		return $this->params[$key];
	}

	/// Obtener vector de argumentos del documento. (Para construir enlaces)
	/// @param override Marca valores a substituir
	/// @param mask Indicar que parámetros seran incluidos, array vacio implica todos.
	/// @param encode Si es verdadero, codificará & como &amp;
	function getArgs($override='',$mask='',$imask='',$encode=True) {
		if(!empty($override)) {
			$args=array_merge($this->params,$override);
		} else {
			$args=$this->params;
		}
		if(!empty($mask)) {
			$args=array_intersect_key($args,array_fill_keys($mask,''));
		}
		if(empty($imask)) {
			$imask=array();
		}
		ksort($args);
		if($encode) {
			$amp="&amp;";
		} else {
			$amp="&";
		}
		$result="?";
		$first=True;
		foreach ($args as $key => $val) {
			if(in_array($key,$imask)) continue;
			if(!$first) {
				$result.=$amp;
			}
			$result.="$key=$val";
			$first=False;
		}
		return $result;
	}

	/// Obtener vector de argumentos del documento. (Para uso en campos ocultos de un formulario)
	/// @param override Marca valores a substituir
	/// @param mask Indicar que parámetros seran incluidos, array vacio implica todos.
	/// @param imask Mascar inversa
	function getArgsHidden($override='',$mask='',$imask='') {
		if(!empty($override)) {
			$args=array_merge($this->params,$override);
		} else {
			$args=$this->params;
		}
		if(!empty($mask)) {
			$args=array_intersect_key($args,array_fill_keys($mask,''));
		}
		if(empty($imask)) {
			$imask=array();
		}
		ksort($args);
		$result="";
		foreach ($args as $key => $val) {
			if(in_array($key,$imask)) continue;
			$result.="<input type=\"hidden\" name=\"$key\" value=\"$val\" />";
		}
		return $result;
	}

	///Comprueba la conexión con la base de datos.
	function checkConnection() {
		global $APF;
		if(empty($this->DB)) {
			$this->DB=createDBObject($APF['db.user'],$APF['db.passwd'],$APF['db.name'],$APF['db.host']);
			$this->DB->connect();
		}
	}

	///Realiza una petición a la base de datos.
	///@param what Petición SQL.
	function query($what) {
		$this->checkConnection();
		$this->DB->query($what);
	}
	
	///Obtiene un array de los datos devueltos de la base de datos desde la última petición que devolvía datos.
	function fetchArray() {
		return($this->DB->fetchArray());
	}

	///Escapa caracteres especiales como "'".
	function escape_string($what) {
		$this->checkConnection();
		if(get_magic_quotes_gpc()) {
			//Si magic quotes esta activado
			// los datos ya estan escapados
			return $what;
		} else {
			//los datos no estan escapados, evitar
			// inyecciones SQL
			return $this->DB->escape_string($what);
			//return(mysql_real_escape_string($what));
		}
	}
	
	///Devuelve el identificador de la ltima petición de inserción realizada a la base de datos.
	function insertId() {
		//return(mysql_insert_id());
		return $this->DB->insertId();
	}

	/// Genera una redirección.
	/// @param page Página de destino
	/// @param redirect Pasar pagina actual como redirect
	function redirect2page($page,$redirect=False) {
		$args['page']=$page;
		if($redirect) {
			$args['redirect']=$this->getParam('page');
		}
		$this->redirect($this->BuildBaseUri($this->getArgs($args,'','',False)));
	}

	/// Obtiene el número de peticiones
	function getQueryCount() {
		if(!empty($this->DB)) {
			return $this->DB->getQueryCount();
		}
		return 0;
	}

}

?>