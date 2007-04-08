<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/../base/manager.php"); 

///Página del vídeo
class ApfVideoPage extends ApfManager implements iDocument {
	private $desc;
	private $pid=1;
	private $vid;

	///Constructor
	function __construct() {
		parent::__construct(_t('untitled'));
		//$this->registerUnloadHook('stop_video');
		$vmgr=$this->getMediaMGR();
		$vid=$vmgr->getVideo($this->getId());
		$vmgr->increaseVideoHitCount($this->getId());
		$this->vid=$vid;
		$this->pid=$vid['pid'];
		$this->setTitle($vid['name']);
		$this->desc=$vid['desc'];
		$this->prev=$vid['prev'];
		$this->dur=$vid['dur'];
		$this->url=$vid['url'];
		$this->category=$vid['category'];
	}
	
	///Método cuerpo
	function body() {
		$args=$this->getArgs(array('page' => 'categ','id' => $this->pid));
		$family="<a href=\"$args\">" . $this->category . "</a>";
		echo('<div class="family">' . $family . "</div>");
		?>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><TR><TD>

		<div class="description"><?php echo($this->desc); ?>
		<br />
		<?php
			//echo(_t("lenght") . ": " . $this->dur . "<br />");
		?>
		</div>
		<?php
			$rtsp_path="rtsp://{$_SERVER["SERVER_NAME"]}:5000/{$this->url}";
			require_once(dirname(__FILE__) . '/../../players/VideoLAN.php');
			$pl=new VideoLANPlayer($this,$rtsp_path);
			$pl->write();

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
	
	///Muestra los botones de administración.
	function showAdminButtons() {
		?>
		<fieldset class="setjumpfrm">
		<form action="<?php echo($this->buildBaseUri($this->getArgs(array('page' => 'edit')))); ?>" method="post">
		<?php echo(_t('admin') . ': '); ?>
		<select name="action">
		<option value="edit_media"><?php echo(_t('edit_media')); ?></option>
		<option value="delete_media"><?php echo(_t('delete_media')); ?></option>
		</select>
		<input type="hidden" name="id" value="<?php echo($this->getId()); ?>" />
		<input type="hidden" name="pid" value="<?php echo($this->pid); ?>" />
		<input type="submit" value="<?php echo(_t('go')); ?>" />
		</form>
		</fieldset>
		<?php
	}

}

?>