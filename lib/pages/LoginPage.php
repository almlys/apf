<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

include_once(dirname(__FILE__) . "/manager.php"); 

///Página de autenticaci�
class ApfLoginPage extends ApfManager {
	///Valor de la autenticación�: 0=Ok, 1=Error
	var $login_status=0;
	///Constructor
	///@param logout Si es verdadero cierra la sessión, en caso contrario muestra la página de entrada.
	function ApfLoginPage($logout=False) {
		$this->ApfManager("");
		$this->setTitle($this->lan->get("login_page"));
		
		$dest=$_GET["redirect"];
		if(empty($dest)) {
			$dest="main";
		}
		$this->dest=$dest;
		
		if($logout) {
			/*$_SESSION["AuthHash"]="";
			session_unset();
			session_destroy();
			setcookie("ApfVoDAuthHash","",time()-36000);
			$this->redirect2page($dest);*/
			$this->endSession();
			$this->redirect2page($dest);
		}
		
		if($this->authed) {
			$this->redirect2page($dest);
		} elseif($_SERVER["REQUEST_METHOD"]=="POST" &&
			!empty($_POST["login"]) && !empty($_POST["password"])) {
				$login=$this->escape_string($_POST["login"]);
				$pass=$this->escape_string($_POST["password"]);
				if($this->auth->authenticate($login,$pass,session_id())) {
					$_SESSION["login"]=$this->auth->login;
					$_SESSION["AuthHash"]=$this->auth->hash;
					$_SESSION["admin"]=$this->auth->level;
					$_SESSION["uid"]=$this->auth->uid;
					setcookie("ApfVoDAuthHash",$_SESSION["AuthHash"],time()+3600,"/");
					$this->redirect2page($dest);
				}
				$this->login_status=1;
		}
	}
	
	///Cabezera
	function head() {
		ApfManager::head();
	}
	
	///M�odo cuerpo
	function body() {
		if($this->login_status==1) {
			?>
			<div class="error">
			<?php echo($this->lan->get("logon_error")); ?>
			</div>
			<br>
			<?php
		}
		?>
		<?php echo($this->lan->get("login_text")); ?>
		<form action="<?php echo($this->buildBaseUri() . $this->getArgs() . "&amp;redirect=" . $this->dest); ?>" method="POST">
		<?php echo($this->lan->get("login") . ":"); ?>
		<INPUT type="text" name="login"><br>
		<?php echo($this->lan->get("password") . ":"); ?>
		<INPUT type="password" name="password"><br>
		<INPUT type="submit" value="OK"><INPUT type="reset" value="Borrar">
		</form>
		<?php
	}

}

?>