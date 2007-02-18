<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/../base/manager.php"); 
require_once(dirname(__FILE__) . "/../../widgets/tree.php");
require_once(dirname(__FILE__) . "/../../widgets/folder.php");

/// Navegador de la biblioteca multimedia. (Sistema virtual de ficheros)
class ApfMediaPage extends ApfManager {
	var $desc;
	var $pid=1;
	/// Constructor
	function ApfMediaPage() {
		$this->ApfManager("");
		$this->setTitle(_t("videos_page"));
	}
	
	///Cabezera
	function head() {
		if($this->state!=0) return;
		//Obtener id (del recurso soliciatdo)
		$id=$this->id;
		if($id==0) $id=$this->id=1;
		/*if($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["id"]) && is_numeric($_POST["id"])) {
			$id=$this->id=$_POST["id"];
		}*/
/*		$lan=_tDefaultLanguage(); //Obtener idioma por defecto

		$name="name_" . $lan;
		$desc="desc_" . $lan;*/
		
		if($id!=1) {
			//1ra peticion
			//$query="select parent,$name,$desc,count from vid_categ where id=$id;";
			/*$query="select a.parent,b.name,a.$desc
							from  vid_categ a inner join vid_names b
							on ja.name_id=b.id where a.id=$id and b.lan=\"$lan\"";*/
			foreach (ApfLocal::getLanguageVector() as $lan) {
				$lan=substr($lan,0,2);
				/*$query="select a.parent,b.name,c.desc
								from vid_categ a, vid_names b, vid_descs c
								where	a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan and b.lan=\"$lan\" and a.id=$id";
				*/
				$query="select a.parent,b.name,c.desc
								from vid_categ a inner join (vid_names b, vid_descs c)
								on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
								where b.lan=\"$lan\" and a.id=$id";
				$this->query($query);
				
				$vals=$this->fetchArray();
				if($vals!=null) {
					//Lo hemos encontrado (fijar algunas propiedades)
					$this->pid=$vals[0];
					$this->setTitle($vals[1]);
					$this->category=$vals[1];
					$this->desc=$vals[2];
					if($vals[3]>1) {
						$this->desc=$this->desc . " - " . $vals[3] . " " . _t("objects");
					}
					break;
				}
			}
		}
		ApfManager::head();
	}
	
	///Método cuerpo.
	function body() {
		$pid=$this->pid; //Parent id
		$lan=ApfLocal::getDefaultLanguage();
		$this->generateMediaTree();
		
		$tree=&$this->tree;
		//Show family
		$family=$this->category;
		
		if($this->id!=1) {
			if($pid!=1) {
				$node=&$tree->findNode($pid);
				while($pid!=1 && $node!=null) {
					$cname=$node["name"];
					$args=$this->getArgs() . "&amp;id=$pid";
					$pid=$node["parent"]["id"]; //Update pid
					$node=&$node["parent"];
					$family="<a href=\"$args\">$cname</a> &gt; $family";
				}
			}
			
			$args=$this->getArgs() . "&amp;id=1";
			$family="<a href=\"$args\">Media</a> &gt; $family";
			?>
			<table width="100%"><tr><td>
			<div class="family"><?php echo($family); ?></div>
			</td>
			<?php
		} else {
			?><table width="100%"><tr>
			<?php
		}
		//Quick navigation
		echo('<td align="right">');
		echo('<form name="jumpfrm" action="' . $this->buildBaseUri() . $this->getArgs() . '" method="GET">' . "\n");
		echo($this->getArgsHidden());
		echo(_t("category") . ": ");
		echo('<SELECT name="id" onchange="document.jumpfrm.submit()">' . "\n");
		$tree->writeOptions($this->id);
		echo("</select>\n");
		echo('<INPUT type="submit" value="' . _t("go") . '">' . "\n");
		echo('</form>');
		echo("</td></tr></table>\n");
		if($this->id!=1) {
			?>
			<div class="description"><?php echo($this->desc); ?></div>
			<?php
		}
		?>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><TR><TD>
		<?php
		//Mostrar todas las carpetas
		//$query="select id,$name,$desc,count from vid_categ where parent=" . $this->id;
		$query="select a.id,b.name,c.desc,a.count
						from vid_categ a inner join (vid_names b, vid_descs c)
						on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
						where b.lan=\"$lan\" and a.parent=" . $this->id;
		$this->query($query);
		while($vals=$this->fetchArray()) {
			$folder=new ApfFolder($this,$vals[0],$vals[1],$vals[2],$vals[3]);
			$folder->show();
		}
		
		//Mostrar todos los recursos multimedia
		//$query="select id,$name,$desc,prev,dur from vid_mfs where ctg=" . $this->id;
		$query="select a.id,b.name,c.desc,a.prev,a.dur
						from vid_mfs a inner join (vid_names b, vid_descs c)
						on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
						where b.lan=\"$lan\" and a.ctg=" . $this->id;
		$this->query($query);
		while($vals=$this->fetchArray()) {
			$folder=new ApfVideo($this,$vals[0],$vals[1],$vals[2],$vals[3],$vals[4]);
			$folder->show();
		}
		
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
	
	/// Muestra los botones de administración
	function showAdminButtons() {
		?>
		<form action="<?php echo($this->buildBaseUri() . $this->getArgs("edit")); ?>" method="POST">
		<?php echo(_t("admin") . ": "); ?>
		<SELECT name="action">
		<option value="add_ctg"><?php echo(_t("add_ctg")); ?></option>
		<?php 
			if ($this->id!=1) {
		?>
		<option value="edit_ctg"><?php echo(_t("edit_ctg")); ?></option>
		<option value="delete_ctg"><?php echo(_t("delete_ctg")); ?></option>
		<?php
			}
		?>
		<option value="add_media"><?php echo(_t("add_media")); ?></option>
		</SELECT>
		<INPUT type="hidden" name="id" value="<?php echo($this->id); ?>">
		<INPUT type="hidden" name="pid" value="<?php echo($this->pid); ?>">
		<INPUT type="submit" value="<?php echo(_t("go")); ?>">
		</form>
		<?php
	}

}

?>