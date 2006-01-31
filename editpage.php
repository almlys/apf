<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

include_once(dirname(__FILE__) . "/manager.php"); 

///Página de edición de medios/categorias
class ApfEditPage extends ApfManager {
	var $action="";
	var $name="";
	var $new=0; //0-insert, 1-update
	var $edit=0; //0->from database, 1->to database
	var $type=0; //0->ctg, 1->media
	var $pid=0;
	var $names;
	var $descs;
	var $delete=0;
	var $valid=1;
	var $image="";

	///Constructor
	function ApfEditPage() {
		global $APF;
		$this->ApfManager("");
		$this->setTitle($this->lan->get("edit_page"));
		$action="";

		$lns=$APF["languages"];
		$cnt=count($lns);

		if(!$this->authed || !$this->admin) {
			$this->redirect2page("login");
		} elseif($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["action"])) {
			$this->action=$action=$this->escape_string($_POST["action"]);
			$this->id=$id=$this->escape_string($_POST["id"]);
			$this->name=$this->escape_string($_POST["name"]);
			$this->pid=$this->escape_string($_POST["pid"]);
			$this->ctg=$this->escape_string($_POST["ctg"]);
			$this->prev=$this->escape_string($_POST["prev"]);
			$this->dur=$this->escape_string($_POST["dur"]);
			$this->url=$this->escape_string($_POST["url"]);
			for($i=0; $i<$cnt; $i++) {
				$this->names[$lns[$i]]=$this->escape_string($_POST["name_" . $lns[$i]]);
				$this->descs[$lns[$i]]=$this->escape_string($_POST["desc_" . $lns[$i]]);
				if(empty($this->names[$lns[$i]])) {
					$this->names[$lns[$i]]=$this->names[$lns[0]];
				}
				if(empty($this->descs[$lns[$i]])) {
					$this->descs[$lns[$i]]=$this->descs[$lns[0]];
				}
			}
			$this->new=$this->escape_string($_POST["new"]);
			$this->delete=$this->escape_string($_POST["delete"]);
			
		} else {
			$this->action=$action=$this->escape_string($_GET["action"]);
			$id=$this->id;
		}
		$this->params = "&amp;id=$id&amp;action=$action";
		
		switch($this->action) {
			case "add_ctg":
				$this->new=1;
				$this->type=0;
				$this->pid=$this->id;
				$this->id=0;
				$this->action="update_ctg";
				break;
			case "edit_ctg":
				$this->new=0;
				$this->type=0;
				$this->action="update_ctg";
				break;
			case "update_ctg":
				$this->edit=1;
				$this->type=0;
				$this->action="update_ctg";
				break;
			case "add_media":
				$this->new=1;
				$this->type=1;
				$this->pid=$this->id;
				$this->id=0;
				$this->action="update_media";
				break;
			case "edit_media":
				$this->new=0;
				$this->type=1;
				$this->action="update_media";
				break;
			case "update_media":
				$this->edit=1;
				$this->type=1;
				$this->action="update_media";
				break;
			default:
				$this->redirect2page("main");
		}
		
		if(empty($this->names[$lns[0]]) || empty($this->descs[$lns[0]]) || empty($this->pid) || ((empty($this->id) || $this->id==0) && $this->new==0) || $this->pid==0 || ($this->type==0 && $this->id==$this->pid)) {
			$this->valid=0;
			//echo("invalid");
			//echo($this->id . " - " . $this->pid . "-" . $this->names[$lns[0]] . "-" . $this->descs[$lns[0]] . " - " . $this->new);
		}

		if($this->edit==1) {
			//insert/update database
			if($this->valid==1) {
				if($this->type==0) { //Categorias
					if($this->new==0 && $this->delete==0) { //update
						$lans="";
						for($i=0; $i<$cnt; $i++) {
							$lans=$lans . ",name_" . $lns[$i] . '="' . $this->names[$lns[$i]] . '"' . ",desc_" . $lns[$i] . '="' . $this->descs[$lns[$i]] . '"';
						}
						$query="update vid_categ set parent = " . $this->pid . $lans . "where id = " . $this->id;
						$this->query($query);
						$this->redirect($this->BuildBaseUri() . $this->getArgs("categ",0) . "&id=" . $this->id);
					} elseif($this->new==0 && $this->delete==1) { //delete
						$query="delete from vid_categ where id=" . $this->id;
						$this->query($query);
						//TODO - borrar nodos huerfanos
						$this->redirect($this->BuildBaseUri() . $this->getArgs("categ",0) . "&id=" . $this->pid);
					} else {
						$lans="";
						for($i=0; $i<$cnt; $i++) {
							$lans=$lans . ",name_" . $lns[$i] . ",desc_" . $lns[$i];
						}
						$query="insert into vid_categ (parent$lans) values(" . $this->pid;
						for($i=0; $i<$cnt; $i++) {
							$query=$query . ',"' . $this->names[$lns[$i]] . '","' . $this->names[$lns[$i]] . '"';
						}
						$query=$query . ")";
						$this->query($query);
						$this->id=$this->insertId();
						$this->redirect($this->BuildBaseUri() . $this->getArgs("categ",0) . "&id=" . $this->id);
					}
				} else { //Videos
					if($this->new==0 && $this->delete==0) { //update
						$lans="";
						for($i=0; $i<$cnt; $i++) {
							$lans=$lans . ",name_" . $lns[$i] . '="' . $this->names[$lns[$i]] . '"' . ",desc_" . $lns[$i] . '="' . $this->descs[$lns[$i]] . '"';
						}
						$query="update vid_mfs set ctg = " . $this->pid . ', prev = "' . $this->prev . '" ' . ', dur = "' . $this->dur . '" ' . ', url = "' . $this->url . '" ' . $lans . " where id = " . $this->id;
						$this->query($query);
						$this->redirect($this->BuildBaseUri() . $this->getArgs("videos",0) . "&id=" . $this->id);
					} elseif($this->new==0 && $this->delete==1) { //delete
						$query="delete from vid_mfs where id=" . $this->id;
						$this->query($query);
						//TODO - borrar nodos huerfanos
						$this->redirect($this->BuildBaseUri() . $this->getArgs("categ",0) . "&id=" . $this->pid);
					} else {
						$lans="";
						for($i=0; $i<$cnt; $i++) {
							$lans=$lans . ",name_" . $lns[$i] . ",desc_" . $lns[$i];
						}
						$query="insert into vid_mfs (ctg,prev,dur,url$lans) values(" . $this->pid . ',"' . $this->prev . '","' . $this->dur . '","' . $this->url . '"';
						for($i=0; $i<$cnt; $i++) {
							$query=$query . ',"' . $this->names[$lns[$i]] . '","' . $this->descs[$lns[$i]] . '"';
						}
						$query=$query . ")";
						$this->query($query);
						$this->id=$this->insertId();
						$this->redirect($this->BuildBaseUri() . $this->getArgs("videos",0) . "&id=" . $this->id);
					}
				}
			}
		} else {
			//grab from database
			$lans="";
			for($i=0; $i<$cnt; $i++) {
				$lans=$lans . ",name_" . $lns[$i] . ",desc_" . $lns[$i];
			}
			if($this->new==0) {
				if($this->type==0) {
					$query="select parent$lans from vid_categ where id=" . $this->id;
					$this->query($query);
					$vals=$this->fetchArray();
					
					$this->pid=$vals[0];
					$off=1;
				} else {
					$query="select ctg,prev,dur,url$lans from vid_mfs where id=" . $this->id;
					$this->query($query);
					$vals=$this->fetchArray();
					
					$this->pid=$vals[0];
					$this->prev=$vals[1];
					$this->dur=$vals[2];
					$this->url=$vals[3];
					$off=4;
				}
				for($i=0; $i<$cnt; $i++) {
					$this->names[$lns[$i]]=$vals[$off+($i*2)];
					$this->descs[$lns[$i]]=$vals[$off+($i*2)+1];
				}

			
			}
		}

	}
	
	///Método body
	function body() {
		global $APF;
		$lns=$APF["languages"];
		$cnt=count($lns);
		if($this->valid==0 && $this->edit==1) {
			echo($this->lan->get("data_error") . "<br>");
		}
		?>
		<form action="<?php echo($this->buildBaseUri() . $this->getArgs()); ?>" method="POST">
		<?php echo($this->lan->get("_id") . ": "); ?>
		<INPUT type="hidden" name="id" value="<?php echo($this->id); ?>">
		<INPUT type="text" name="id2" disabled="true" value="<?php echo($this->id); ?>">
		<?php
			if($this->new==0) {
				echo("&nbsp;" . $this->lan->get("delete") . ":");
				?>
					<INPUT type="checkbox" name="delete" value="1">
				<?php
			}
		?>
		<br>
		<?php echo($this->lan->get("parent") . ": "); ?>
		<INPUT  type="text" name="pid" value="<?php echo($this->pid); ?>"><br>

		<hr>
		<?php
			for($i=0; $i<$cnt; $i++) {
				$sname="name_" . $lns[$i];
				$sdesc="desc_" . $lns[$i];
				$name=$this->names[$lns[$i]];
				$desc=$this->descs[$lns[$i]];
				
				echo($this->lan->get($lns[$i]) . ":<br>");
				echo($this->lan->get("_name") . ": ");
				echo('<input type="text" name="' . $sname . '" value="' . $name . '"><br>');
				echo($this->lan->get("desc") . ": ");
				echo('<input size="60" type="text" name="' . $sdesc . '" value="' . $desc . '"><br>');
				echo("<hr>");
			}
			
			if($this->type==1) {
				echo($this->lan->get("properties") . ":<br>");
				if(empty($this->prev)) {
					$this->image=$this->buildBaseUri() . "/imgs/videoimg.jpg";
				} else {
					$this->image=$this->buildBaseUri() . "/cache/" . $this->prev;
				}
				echo('<img src="' . $this->image . '" border=0 width="160" height="120"><br>');
				echo($this->lan->get("preview") . ": ");
				echo('<input type="text" name="prev" value="' . $this->prev . '">
				<INPUT type="checkbox" name="prev_auto" value="1"> 
				' . $this->lan->get("automatic") . '
				<br>');
				echo($this->lan->get("url") . ": ");
				echo('<input type="text" name="url" value="' . $this->url . '"><br>');
				echo($this->lan->get("lenght") . ": ");
				echo('<input type="text" name="dur" value="' . $this->dur . '"><br>');

			}
		?>
		
		<input type="hidden" name="new" value="<?php echo($this->new); ?>">
		<input type="hidden" name="action" value="<?php echo($this->action); ?>">
		<INPUT type="submit"><INPUT type="reset">
		</form>    
		
		<?php
	
	}

}

?>