<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

include_once(dirname(__FILE__) . "/manager.php"); 

///Página del vídeo
class ApfVideoPage extends ApfManager {
	var $desc;
	var $pid=1;
	///Constructor
	function ApfVideoPage() {
		$this->ApfManager("");
		$this->setTitle($this->lan->get("untitled"));
	}
	
	///Cabezera
	function head() {
		if($this->state!=0) return;
		//$this->state=2;
		//Obtener id
		$id=$this->id;
		$lan=$this->lan->getDefaultLanguage(); //Obtener idioma por defecto

		$name="name_" . $lan;
		$desc="desc_" . $lan;
		
		//1ra peticion
		//$query="select ctg,$name,$desc,prev,dur,url from vid_mfs where id=$id;";
		$query="select a.ctg,b.name,c.desc,a.prev,a.dur,a.url
						from vid_mfs a inner join (vid_names b, vid_descs c)
						on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
						where b.lan=\"$lan\" and a.id=$id";
		$this->query($query);
		$vals=$this->fetchArray();
		$this->pid=$vals[0];
		$this->setTitle($vals[1]);
		$this->desc=$vals[2];
		$this->prev=$vals[3];
		$this->dur=$vals[4];
		$this->url=$vals[5];
		
		//$query="select $name from vid_categ where id=" . $this->pid;
		$query="select b.name
						from vid_categ a inner join vid_names b
						on a.name_id=b.id
						where b.lan=\"$lan\" and a.id=" . $this->pid;
		$this->query($query);
		$vals=$this->fetchArray();
		$this->category=$vals[0];

		ApfManager::head();
	}
	
	///Método cuerpo
	function body() {
		$args=$this->getArgs("categ") . "&amp;id=" . $this->pid;
		$family="<a href=\"$args\">" . $this->category . "</a>";
		echo('<div>' . $family . "</div>");
		?>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><TR><TD>

		<div class="description"><?php echo($this->desc); ?>
		<br>
		<?php
			echo($this->lan->get("lenght") . ": " . $this->dur . "<br>");
		?>
		</div>
		<a href="<?php echo($this->buildBaseUri() . "videos/" . $this->url); ?>">Play HTTP</a>
		
		<br><br>
		<embed type="application/x-vlc-plugin"
       name="video1"
			 autoplay="true" hidden="no" loop="yes" width="400" height="300"
			 target="rtsp://<?php echo($_SERVER["SERVER_NAME"]); ?>:5000/<?php echo($this->url); ?>" />
		<br/>

		<a href="javascript:;" onclick="document.video1.play()">Play RTSP</a>
		<a href="javascript:;" onclick="document.video1.pause()">Pause RTSP</a>
		<a href="javascript:;" onclick="document.video1.stop()">Stop RTSP</a>
		<a href="javascript:;" onclick="document.video1.fullscreen()">Fullscreen RTSP</a>
		<a href="javascript:;" onclick="document.video1.seek(10,1)">Seek</a>
		
		
		<!--
		<embed src="<?php echo($this->buildBaseUri() . "videos/" . $this->url); ?>" width="1200" height="800"> -->
		<!-- <object width="640" height="480"> 
		<param name="src" value="<?php echo($this->buildBaseUri() . "videos/" . $this->url); ?>">
		</object> -->
		<?php

		//Mostrar botones administrativos si admin
		if($this->admin) {
			?>
			</TD></TR>
			<tr><TD>
			<hr>
			<?php

			$this->showAdminButtons();
		}
		
		?>
		</TD></tr></table>
		<?php
	}
	
	///Muestra los botones de administración.
	function showAdminButtons() {
		?>
		<form action="<?php echo($this->buildBaseUri() . $this->getArgs("edit")); ?>" method="POST">
		<?php echo($this->lan->get("admin") . ": "); ?>
		<SELECT name="action">
		<option value="edit_media"><?php echo($this->lan->get("edit_media")); ?></option>
		</SELECT>
		<INPUT type="hidden" name="id" value="<?php echo($this->id); ?>">
		<INPUT type="submit" value="<?php echo($this->lan->get("go")); ?>">
		</form>
		<?php
	}

}

?>