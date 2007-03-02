<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/../base/manager.php"); 

///Página de autenticación
class ApfLoginPage extends ApfManager implements iDocument {
	private $login_status=0; 	///<! Valor de la autenticación: 0=Ok, 1=Error
	private $dest; ///<! Valor de destino de la redirección
	private $md5_chap=False; ///<! Indica si usamos MD5-Chap
	private $challenge='';

	///Constructor
	///@param logout Si es verdadero cierra la sessión, en caso contrario muestra la página de entrada.
	function __construct($logout=False) {
		parent::__construct(_t('login_page'),False);
		global $APF;
		$this->md5_chap=$APF['auth.chap'];

		$this->startSession(True);

		if($this->md5_chap) {
			if(!isset($_SESSION['challenge'])) {
				$_SESSION['challenge']=$this->getAuthChallenge();
				$this->challenge=$_SESSION['challenge'];
			}
		}

		//Fijar destino
		$dest=$_GET['redirect'];
		if(empty($dest)) {
			$dest='main';
		}
		$this->dest=$dest;

		//Logout?
		if($logout) {
			$this->endSession();
			$this->redirect2page($dest);
		}

		if($this->IAmAuthenticated()) {
			// Usuario previamente autenticado
			$this->redirect2page($dest);
		} elseif($_SERVER['REQUEST_METHOD']=='POST' &&
			!empty($_POST['login']) && !empty($_POST['password'])) {
				$login=$this->escape_string($_POST['login']);
				if($this->md5_chap) {
					$client_hash=$this->escape_string($_POST['hash']);
					$result=$this->authValidate($login,$this->challenge,$client_hash,'md5');
				} else { //Plain Auth
					$pass=$this->escape_string($_POST['password']);
					$result=$this->authenticate($login,$pass,'plain',$sid=session_id());
				}

				if($result) {
					//Exito
					$this->redirect2page($dest);
				}
				//Fracaso
				$this->login_status=1;
		}
	}
	
	/* //Cabezera
	function head() {
		parent::head();
	}*/
	
	///Método cuerpo
	function body() {
		$this->setParam('redirect',$this->dest);
		if($this->login_status==1) {
			?>
			<div class="error">
			<?php echo(_t('logon_error')); ?>
			</div>
			<br>
			<?php
		}
		?>
		<?php echo(_t('login_text')); ?>
		<form action="<?php echo($this->buildBaseUri($this->getArgs())); ?>" method="post">
		<?php echo(_t('login') . ":"); ?>
		<input type="text" name="login" /><br />
		<?php echo(_t('password') . ":"); ?>
		<input type="password" name="password" /><br />
		<input type="submit" value='<?php echo(_t('OK')); ?>' />
		<input type="reset" value='<?php echo(_t('Delete')); ?>' />
		</form>
		<?php
	}

}

?>