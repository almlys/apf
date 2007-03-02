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
					$this->challenge=$_SESSION['challenge'];
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
		if($this->md5_chap) {
			$_SESSION['challenge']=$this->getAuthChallenge();
			$this->challenge=$_SESSION['challenge'];
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
		if($this->md5_chap) {
		?>
		<script type='text/javascript' src='<?php echo($this->buildBaseUri('js/md5.js')); ?>'></script>
		<script type='text/javascript'>
			var challenge="<?php echo($this->challenge); ?>";
			function hashme(login,pass,challenge) {
				return hex_md5(login + hex_md5(pass) + challenge);
			}
			function dohash(form) {
				if(form.login.value=="") {
					alert("<?php echo(_t('login_field_empty')); ?>");
					return false;
				}
				var out=document.getElementById("auth_status");
				out.innerHTML="<?php echo(_t('authenticating')); ?>";
				form.submit.disabled=true;
				form.reset.disabled=true;
				form.hash.value=hashme(form.login.value,form.password.value,challenge);
				form.password.value="**********";
				return true;
			}
		</script>
		<?
		}
		?>
		<?php echo(_t('login_text')); ?>
		<form action='<?php echo($this->buildBaseUri($this->getArgs())); ?>' method='post' <?
		if($this->md5_chap) {
			echo('onsubmit="return dohash(this);"');
		}
		?>>
		<?php echo(_t('login') . ':'); ?>
		<input type='text' name='login' /><br />
		<?php echo(_t('password') . ':'); ?>
		<input type='password' name='password' /><br />
		<?
		if($this->md5_chap) {
		?>
		<input type='hidden' name='hash' />
		<?
		}
		?>
		<input name='submit' type='submit' value='<?php echo(_t('OK')); ?>'  />
		<input name='reset' type='reset' value='<?php echo(_t('Delete')); ?>' />
		</form>
		<div id="auth_status"></div>
		<?php
	}

}

?>