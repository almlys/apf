<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

include_once(dirname(__FILE__) . "/manager.php"); 

///Página de edición de medios/categorias
class ApfEditPage extends ApfManager {
	var $action=""; ///< acción
	var $name=""; ///< Nombre del nodo
	var $new=0; ///< 0-insert, 1-update
	var $edit=0; ///< 0->from database, 1->to database
	var $type=0; ///< 0->ctg, 1->media
	var $pid=0; ///< Identificador del padre
	var $desc=""; ///< Descripción del nodo
	var $delete=0; ///< ¿Borrar este nodo?
	var $valid=1; ///< Validación de la entrada
	var $image=""; ///< Imagen del nodo

	///Constructor
	function ApfEditPage() {
		global $APF;
		$this->ApfManager("");
		$this->setTitle($this->lan->get("edit_page"));
		$action="";
		//$lns=$APF["languages"];
		//$cnt=count($lns);
		$lan=$this->lan->getDefaultLanguage();

		if(!$this->authed || !$this->admin) {
			$this->redirect2page("login");
		} elseif($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["action"])) {
			$this->action=$action=$this->escape_string($_POST["action"]);
			$this->id=$id=intval($_POST["id"]);
			$this->name=$this->escape_string($_POST["name"]);
			$this->pid=intval($_POST["pid"]);
			$this->ctg=intval($_POST["ctg"]);
			$this->prev=$this->escape_string($_POST["prev"]);
			$this->dur=$this->escape_string($_POST["dur"]);
			$this->url=$this->escape_string($_POST["url"]);
			$this->desc=$this->escape_string($_POST["desc"]);
			$this->new=intval($_POST["new"]);
			$this->delete=intval($_POST["delete"]);
		} else {
			$this->action=$action=$this->escape_string($_GET["action"]);
			$id=$this->id;
		}
		$this->params = "&amp;id=$id&amp;action=$action";
		
		switch($this->action) {
			//Categoria
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
			case "delete_ctg":
				$this->new=0;
				if(empty($this->id) || $this->id==0) {
					$this->id=$this->pid;
				}
				$this->edit=1;
				$this->type=0;
				$this->delete=1;
				$this->action="update_ctg";
				break;
			//Vídeo
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
			case "delete_media":
				$this->new=0;
				$this->edit=1;
				$this->type=1;
				$this->delete=1;
				$this->action="update_media";
				break;
			default:
				$this->redirect2page("main");
		}
		
		if(((empty($this->name) || empty($this->desc) || empty($this->pid) || ((empty($this->id) || $this->id==0) && $this->new==0) || $this->pid==0 || ($this->type==0 && $this->id==$this->pid)) && $this->delete==0) || ($this->delete==1 && (empty($this->id) || $this->id==0))) {
			$this->valid=0;
			//echo("invalid");
			//echo("id: " . $this->id . " - pid: " . $this->pid . "- name: " . $this->name . "- desc: " . $this->desc . " - new:" . $this->new . " - delete:" . $this->delete);
		}

		if($this->edit==1) {
			//insert/update database
			if($this->valid==1) {
				if($this->type==0) { //Categorias
					if($this->new==0 && $this->delete==0) { //update
						//1o Actualizar nombre y descripción
						$query="update vid_names b, vid_descs c, vid_categ a
										set b.name=\"" . $this->name . "\",
												c.desc=\"" . $this->desc . "\",
												a.parent=\"" . $this->pid . "\"
										where a.name_id=b.id and a.desc_id=c.id 
												and b.lan=c.lan and b.lan=\"$lan\" and a.id=" . $this->id;
						//}

						//old code
						/*$lans="";
						for($i=0; $i<$cnt; $i++) {
							$lans=$lans . ",name_" . $lns[$i] . '="' . $this->names[$lns[$i]] . '"' . ",desc_" . $lns[$i] . '="' . $this->descs[$lns[$i]] . '"';
						}
						$query="update vid_categ set parent = " . $this->pid . $lans . "where id = " . $this->id;*/
						$this->query($query);
						$this->redirect($this->BuildBaseUri() . $this->getArgs("categ",0) . "&id=" . $this->id);
						///TODO si el update falla realizar una inserción
					} elseif($this->new==0 && $this->delete==1) { //delete
						$query="delete from vid_categ where id=" . $this->id;
						$this->query($query);
						///TODO - borrar nodos huerfanos
						$this->redirect($this->BuildBaseUri() . $this->getArgs("categ",0) . "&id=" . $this->pid);
					} else { //Insert
						//1o obtener id mas alto de la tabla names
						$query="select max(id) from vid_names";
						$this->query($query);
						$ret=$this->fetchArray();
						if($ret==null) $this->error_die("Null fetchArray at line " . __LINE__);
						$name_id=$ret[0]+1;
						//2o obtener el id más lato de la tabla descs
						$query="select max(id) from vid_descs";
						$this->query($query);
						$ret=$this->fetchArray();
						if($ret==null) $this->error_die("Null fetchArray at line " . __LINE__);
						$desc_id=$ret[0]+1;
						//Por todos los idiomas
						foreach ($APF["languages"] as $xlan) {
							$xlan=substr($xlan,0,2);
							//Insertar el nombre
							$query="insert into vid_names (id,lan,name) values($name_id,\"$xlan\",\"" . $this->name . "\")";
							//echo($query);
							$this->query($query);
							//Insertar la descripción
							//NOTA mental, desc es una fucking reserved keyword (y es fucking despues de estar 3 horas mirando la consulta intentado localizar el maldito error)
							$query="insert into vid_descs (id,lan,`desc`) values($desc_id,\"$xlan\",\"" . $this->desc . "\")";
							//echo($query);
							$this->query($query);
						}
						//Insertar el registro
						$query="insert into vid_categ (parent,name_id,desc_id) values(" . $this->pid .
						",$name_id,$desc_id)";
						$this->query($query);
						/*
						$lans="";
						for($i=0; $i<$cnt; $i++) {
							$lans=$lans . ",name_" . $lns[$i] . ",desc_" . $lns[$i];
						}
						$query="insert into vid_categ (parent$lans) values(" . $this->pid;
						for($i=0; $i<$cnt; $i++) {
							$query=$query . ',"' . $this->names[$lns[$i]] . '","' . $this->names[$lns[$i]] . '"';
						}
						$query=$query . ")"; */
						//$query="make me";
						//$this->query($query);
						$this->id=$this->insertId();
						$this->redirect($this->BuildBaseUri() . $this->getArgs("categ",0) . "&id=" . $this->id);
					}
				} else { //Videos
					if($this->new==0 && $this->delete==0) { //update
						//1o Actualizar nombre y descripción
						$query="update vid_names b, vid_descs c, vid_mfs a
										set b.name=\"" . $this->name . "\",
												c.desc=\"" . $this->desc . "\",
												a.ctg=\"" . $this->pid . "\",
												a.prev=\"" . $this->prev . "\",
												a.dur=\"" . $this->dur . "\",
												a.url=\"" . $this->url . "\"
										where a.name_id=b.id and a.desc_id=c.id 
												and b.lan=c.lan and b.lan=\"$lan\" and a.id=" . $this->id;
						/* Old code
						$lans="";
						for($i=0; $i<$cnt; $i++) {
							$lans=$lans . ",name_" . $lns[$i] . '="' . $this->names[$lns[$i]] . '"' . ",desc_" . $lns[$i] . '="' . $this->descs[$lns[$i]] . '"';
						}
						$query="update vid_mfs set ctg = " . $this->pid . ', prev = "' . $this->prev . '" ' . ', dur = "' . $this->dur . '" ' . ', url = "' . $this->url . '" ' . $lans . " where id = " . $this->id;
						*/
						///TODO si el update falla, realizar una insercción
						$this->query($query);
						$this->redirect($this->BuildBaseUri() . $this->getArgs("videos",0) . "&id=" . $this->id);
					} elseif($this->new==0 && $this->delete==1) { //delete
						$query="delete from vid_mfs where id=" . $this->id;
						$this->query($query);
						///TODO - borrar nodos huerfanos
						$this->redirect($this->BuildBaseUri() . $this->getArgs("categ",0) . "&id=" . $this->pid);
					} else {
						/* Old code
						$lans="";
						for($i=0; $i<$cnt; $i++) {
							$lans=$lans . ",name_" . $lns[$i] . ",desc_" . $lns[$i];
						}
						$query="insert into vid_mfs (ctg,prev,dur,url$lans) values(" . $this->pid . ',"' . $this->prev . '","' . $this->dur . '","' . $this->url . '"';
						for($i=0; $i<$cnt; $i++) {
							$query=$query . ',"' . $this->names[$lns[$i]] . '","' . $this->descs[$lns[$i]] . '"';
						}
						$query=$query . ")";
						$this->query($query);*/
						//1o obtener id mas alto de la tabla names
						$query="select max(id) from vid_names";
						$this->query($query);
						$ret=$this->fetchArray();
						if($ret==null) $this->error_die("Null fetchArray at line " . __LINE__);
						$name_id=$ret[0]+1;
						//2o obtener el id más lato de la tabla descs
						$query="select max(id) from vid_descs";
						$this->query($query);
						$ret=$this->fetchArray();
						if($ret==null) $this->error_die("Null fetchArray at line " . __LINE__);
						$desc_id=$ret[0]+1;
						//Por todos los idiomas
						foreach ($APF["languages"] as $xlan) {
							$xlan=substr($xlan,0,2);
							//Insertar el nombre
							$query="insert into vid_names (id,lan,name) values($name_id,\"$xlan\",\"" . $this->name . "\")";
							//echo($query);
							$this->query($query);
							//Insertar la descripción
							//NOTA mental, desc es una fucking reserved keyword (y es fucking despues de estar 3 horas mirando la consulta intentado localizar el maldito error)
							$query="insert into vid_descs (id,lan,`desc`) values($desc_id,\"$xlan\",\"" . $this->desc . "\")";
							//echo($query);
							$this->query($query);
						}
						//Insertar el registro
						$query="insert into vid_mfs (ctg,prev,dur,url,name_id,desc_id) values(" . 
						$this->pid . ",\"" . $this->prev . "\",\"" . 
						$this->dur . "\",\"" . $this->url . "\",$name_id,$desc_id)";
						//echo($query);
						$this->query($query);
						$this->id=$this->insertId();
						$this->redirect($this->BuildBaseUri() . $this->getArgs("videos",0) . "&id=" . $this->id);
					}
				}
			}
		} else {
			//grab from database
			/*
			$lans="";
			for($i=0; $i<$cnt; $i++) {
				$lans=$lans . ",name_" . $lns[$i] . ",desc_" . $lns[$i];
			}*/
			if($this->new==0) {
				if($this->type==0) {
					//$query="select parent$lans from vid_categ where id=" . $this->id;
					$query="select a.parent,b.name,c.desc
									from vid_categ a inner join (vid_names b,vid_descs c)
									on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
									where b.lan=\"$lan\" and a.id=" . $this->id;
					//echo($query);
					$this->query($query);
					$vals=$this->fetchArray();
					
					$this->pid=$vals[0];
					$this->name=$vals[1];
					$this->desc=$vals[2];
					//$off=1;
				} else {
					//$query="select ctg,prev,dur,url$lans from vid_mfs where id=" . $this->id;
					$query="select a.ctg,a.prev,a.dur,a.url,b.name,c.desc
									from vid_mfs a inner join (vid_names b,vid_descs c)
									on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
									where b.lan=\"$lan\" and a.id=" . $this->id;
					$this->query($query);
					$vals=$this->fetchArray();
					
					$this->pid=$vals[0];
					$this->prev=$vals[1];
					$this->dur=$vals[2];
					$this->url=$vals[3];
					$this->name=$vals[4];
					$this->desc=$vals[5];
					//$off=4;
				}
				/*for($i=0; $i<$cnt; $i++) {
					$this->names[$lns[$i]]=$vals[$off+($i*2)];
					$this->descs[$lns[$i]]=$vals[$off+($i*2)+1];
				}*/
			}
		}

	}
	
	///Método body
	function body() {
		global $APF;
//		$lns=$APF["languages"];
//		$cnt=count($lns);
		if($this->valid==0 && $this->edit==1) {
			echo($this->lan->get("data_error") . "<br>");
		}
		//Generar arbol de directorios
		$this->generateMediaTree();
		?>
		<form action="<?php echo($this->buildBaseUri($this->getArgs())); ?>" method="POST">
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
		<?php 
			//echo($this->lan->get("parent") . ": "); 
			echo($this->lan->get("category") . ": "); 
		?>
		<?php /*
		<INPUT  type="text" name="pid" value="<?php echo($this->pid); ?>"><br>
		*/ ?>

		<SELECT name="pid">
		<?php
		$this->tree->writeOptions($this->pid);
		?>
		</select>
		<hr>
		<?php echo($this->lan->get("_name") . ": "); ?>
		<input type="text" name="name" value="<?php echo($this->name); ?>"><br>
		<?php echo($this->lan->get("desc") . ": "); ?><br>
		<?php /*
		<input size="60" type="text" name="desc" value="<?php echo($this->desc); ?>"><br>
		*/
		?>
		<textarea name="desc" rows="5" cols="50"><?php echo($this->desc); ?></textarea>
		<hr>

		<?php
			/*
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
			}*/
			
			if($this->type==1) {
				//Vídeo
				echo($this->lan->get("properties") . ":<br>");
				if(empty($this->prev)) {
					$this->image=$this->buildBaseUri("imgs/videoimg.jpg");
				} else {
					$this->image=$this->buildBaseUri("cache/" . $this->prev);
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
		<INPUT type="submit" value="<?php echo($this->lan->get("go")); ?>">
		<INPUT type="reset" value="<?php echo($this->lan->get("reset")); ?>">
		</form>
		
		<?php
	
	}

}

?>