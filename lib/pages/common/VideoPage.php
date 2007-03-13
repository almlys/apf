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
		?>
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
			//<![CDATA[
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
				vlc=null;
			}
			function stop_video() {
				// Work around for unsolved bug #908 http://trac.videolan.org/vlc/ticket/908
				// DOES NOT WORK, the bug is different, argh!!
				//
				//
				// argn=type, argv=application/x-vlc-plugin
				// argn=pluginspage, argv=http://www.videolan.org
				// argn=version, argv=VideoLAN.VLCPlugin.2
				// argn=name, argv=video1
				// argn=autoplay, argv=true
				// argn=hidden, argv=no
				// argn=loop, argv=no
				// argn=id, argv=vlc
				// argn=target, argv=rtsp://pegasus:5000/Elephants_Dream_1024.avi
				// argn=height, argv=300
				// argn=width, argv=400
				// 
				// Program received signal SIGSEGV, Segmentation fault.
				// [Switching to Thread -1222678048 (LWP 17100)]
				// 0xb7429c0d in confstr () from /lib/tls/i686/cmov/libc.so.6
				// (gdb) bt
				// #0  0xb7429c0d in confstr () from /lib/tls/i686/cmov/libc.so.6
				// #1  0xb742b121 in confstr () from /lib/tls/i686/cmov/libc.so.6
				// #2  0xb742b2cf in getopt_long () from /lib/tls/i686/cmov/libc.so.6
				// #3  0xaf0a0572 in __config_PutPsz () from /usr/lib/libvlc.so.1
				// #4  0xaf01dd11 in libvlc_InternalInit () from /usr/lib/libvlc.so.1
				// #5  0xb1ea46cd in libvlc_new () from /usr/lib/libvlc-control.so.0
				// #6  0xb1ed35fe in VlcPlugin::init () from /usr/lib/mozilla/plugins/libvlcplugin.so
				// #7  0xb1ed2278 in NPP_New () from /usr/lib/mozilla/plugins/libvlcplugin.so
				// #8  0xb1eddb29 in Private_New () from /usr/lib/mozilla/plugins/libvlcplugin.so
				// #9  0x082bec85 in ?? ()
				// #10 0xabd772e0 in ?? ()
				// #11 0xa74d547c in ?? ()
				// #12 0x00000001 in ?? ()
				// #13 0x0000000b in ?? ()
				// #14 0xa77c5360 in ?? ()
				// #15 0xb0b38f30 in ?? ()
				// #16 0x00000000 in ?? ()

				if(vlc!=null) {
					vlc.playlist.stop();
					//sleep(1);
					//alert('bye');
				}
			}
			//]]>
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