<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/../base/manager.php"); 

/// Navegador de la biblioteca multimedia. (Sistema virtual de ficheros)
class ApfMediaPage extends ApfManager {
	private $desc; ///<! Descripción
	private $pid=1; ///<! Id del padre

	/// Constructor
	function __construct() {
		parent::__construct(_t('videos_page'));
	}
	
	///Cabezera
	function head() {
		//Obtener id (del recurso soliciatdo)
		$id=$this->getId();
		if($id==0) $id=$this->setId(1);
		/*if($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["id"]) && is_numeric($_POST["id"])) {
			$id=$this->id=$_POST["id"];
		}*/
		if($id!=1) {
			$ctg=$this->getMediaMGR()->findCategory($id);
			if($ctg!=null) {
				$this->pid=$ctg['pid'];
				$this->setTitle($ctg['name']);
				$this->category=$ctg['name'];
				$this->desc=$ctg['desc'];
				if($ctg['count']>1) {
					$this->desc=$this->desc . ' - ' . $ctg['count'] . ' ' . _t('objects');
				}
			}
		}
		ApfManager::head();
	}

	/// Imprime la familia
	function getFamily() {
		$id=$this->getId();
		$pid=$this->pid; //Parent id
		$tree=$this->getMediaTree();
		//Show family
		$family=$this->category;
		
		if($id!=1) {
			if($pid!=1) {
				$node=&$tree->findNode($pid);
				while($pid!=1 && $node!=null) {
					$cname=$node['name'];
					$args=$this->getArgs(array('id' => $pid));
					$pid=$node['parent']['id']; //Update pid
					$node=&$node['parent'];
					$family="<a href=\"$args\">$cname</a> &gt; $family";
				}
			}
			$args=$this->getArgs(array('id' => 1));
			$family="<a href=\"$args\">Media</a> &gt; $family";
			return $family;
		}
	}

	/// Imprime el area superior del navegador de categorias
	function printTopBar() {
		$id=$this->getId();
		if($id!=1) {
			?>
			<table width="100%"><tr><td>
			<div class="family"><?php echo($this->getFamily()); ?></div>
			</td>
			<?php
		} else {
			?><table width="100%"><tr>
			<?php
		}
		echo('<td align="right">');
		$this->writeCategoryListControl($id);
		echo("</td></tr></table>\n");
		if($id!=1) {
			?>
			<div class="description"><?php echo($this->desc); ?></div>
			<?php
		}
	}
	
	///Método cuerpo.
	function body() {
		$id=$this->getId();
		$this->printTopBar();
		/*if($this->IAmAdmin()) {
			echo('<div align="right">');
			$this->showAdminButtons();
			echo('</div>');
		}*/
		?>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td>
		<?php
		//Mostrar todas las carpetas
		$this->printFolders($id);
		//Mostrar todos los recursos multimedia
		$this->printMedia($id);
		//Mostrar botones administrativos si admin
		if($this->IAmAdmin()) {
			?>
			</td></tr>
			<tr><td>
			<hr />
			<?php
			$this->showAdminButtons();
		}
		
		?>
		</td></tr></table>
		<?php
	}
	
	/// Muestra los botones de administración
	function showAdminButtons() {
		?>
		<fieldset class="setjumpfrm">
		<form action="<?php echo($this->buildBaseUri($this->getArgs(array('page' => 'edit')))); ?>" method='post'>
		<?php echo(_t("admin") . ": "); ?>
		<select name="action">
		<option value="add_ctg"><?php echo(_t("add_ctg")); ?></option>
		<?php 
			if ($this->getId()!=1) {
		?>
		<option value="edit_ctg"><?php echo(_t("edit_ctg")); ?></option>
		<option value="delete_ctg"><?php echo(_t("delete_ctg")); ?></option>
		<?php
			}
		?>
		<option value="add_media"><?php echo(_t("add_media")); ?></option>
		</select>
		<input type="hidden" name="id" value="<?php echo($this->getId()); ?>" />
		<input type="hidden" name="pid" value="<?php echo($this->pid); ?>" />
		<input type="submit" value="<?php echo(_t("go")); ?>" />
		</form>
		</fieldset>
		<?php
	}

}

?>