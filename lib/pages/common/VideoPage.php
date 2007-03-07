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
			echo(_t("lenght") . ": " . $this->dur . "<br />");
		?>
		</div><?php /*
		<!-- <a href="<?php echo($this->buildBaseUri() . "videos/" . $this->url); ?>">Play HTTP</a> -->*/?>
		
		<br /><br />
		<embed type="application/x-vlc-plugin" pluginspage="http://www.videolan.org" 
			version="VideoLAN.VLCPlugin.2"
			name="video1"
			autoplay="true" hidden="no" loop="no" width="400" height="300" id="vlc"
			target="rtsp://<?php echo($_SERVER["SERVER_NAME"]); ?>:5000/<?php echo($this->url); ?>" />
		<br />

		<a href="javascript:;" onclick="document.video1.playlist.play()">Play RTSP</a>
		<a href="javascript:;" onclick="document.video1.playlist.togglePause()">Pause RTSP</a>
		<a href="javascript:;" onclick="document.video1.playlist.stop()">Stop RTSP</a>
		<a href="javascript:;" onclick="document.video1.video.fullscreen=true">Fullscreen RTSP</a>
		<a href="javascript:;" onclick="document.video1.input.position=0.5">Seek</a>
		
		<hr />
		<div id="debug">
		</div>
		<script type='text/javascript'>
			function log_write(text) {
				var out=document.getElementById("debug");
				out.innerHTML+=text+"<br />";
			}
			log_write("Initializing...");
			try {
				var vlc=document.getElementById("vlc");
				var version=vlc.versionInfo();
				log_write("VideoLAN version: " + version);
			} catch(e) {
				log_write("VideoLAN not found, or unsuported browser");
			}
		</script>

		<?php /*
		<!--
		<embed src="<?php echo($this->buildBaseUri() . "videos/" . $this->url); ?>" width="1200" height="800"> -->
		<!-- <object width="640" height="480"> 
		<param name="src" value="<?php echo($this->buildBaseUri() . "videos/" . $this->url); ?>">
		</object> --> */ ?>
		<?php

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