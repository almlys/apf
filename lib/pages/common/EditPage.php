<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . "/../base/manager.php"); 

///Página de edición de medios/categorias
class ApfEditPage extends ApfManager implements iDocument {
	private $rv;

	/// Expandir acción
	/// @param rv Array de props
	/// @returns Array
	function expandAction($rv) {
		switch($rv['action']) {
			//Categoria
			case 'add_ctg':
				$rv['new']=True; //Insert
				$rv['type']='ctg';
				$rv['pid']=$rv['id'];
				$rv['id']=0;
				$rv['action']='update_ctg';
				break;
			case 'edit_ctg':
				$rv['new']=False; //Update
				$rv['type']='ctg';
				$rv['action']='update_ctg';
				break;
			case 'update_ctg':
				$rv['edit']=True; //Insert/Update
				$rv['type']='ctg';
				$rv['action']='update_ctg';
				break;
			case 'delete_ctg':
				$rv['new']=False;
				$rv['delete']=True; //Delete
				if(empty($rv['id']) || $rv['id']==0) {
					$rv['id']=$rv['pid'];
				}
				$rv['edit']=True;
				$rv['type']='ctg';
				$rv['action']='update_ctg';
				break;
			//Vídeo
			case 'add_media':
				$rv['new']=True; //Insert
				$rv['type']='media';
				$rv['pid']=$rv['id'];
				$rv['id']=0;
				$rv['action']='update_media';
				break;
			case 'edit_media':
				$rv['new']=False; //Update
				$rv['type']='media';
				$rv['action']='update_media';
				break;
			case 'update_media':
				$rv['edit']=True; //Insert/Update
				$rv['type']='media';
				$rv['action']='update_media';
				break;
			case 'delete_media':
				$rv['new']=False;
				$rv['delete']=True; //Delete
				$rv['edit']=True;
				$rv['type']='media';
				$rv['action']='update_media';
				break;
			default:
				$rv['action']='none';
		}
		return $rv;
	}

	/// Comprovar consistencia de la acción
	/// @param rv Array de props
	/// @returns Array
	function checkActionConsistency($rv) {
		$rv['valid']=True;
		if($rv['delete']) {
			if(empty($rv['id']) || $rv['id']==0) {
				$rv['valid']=False;
			}
		} else {
			$req=array('name','desc','type','pid');
			foreach ($req as $ck) {
				if(empty($rv[$ck])) {
					$rv['valid']=False;
					return $rv;
				}
			}
			if($rv['type']!='ctg' && $rv['type']!='media') {
				$rv['valid']=False;
				return $rv;
			}
			if(!$rv['new'] && empty($rv['id']) || $rv['id']==0) {
				$rv['valid']=False;
				return $rv;
			}
			if($rv['type']=='ctg' && $rv['id']==$rv['pid']) {
				$rv['valid']=False;
				return $rv;
			}
		}
		return $rv;
	}

	/// Actualiza el titulo de la página en funcion de la acción
	/// @param action Acción
	function updateTitle($action) {
		switch($action) {
			case 'add_ctg':
				$this->setTitle(_t('add_ctg'));
				break;
			case 'edit_ctg':
			case 'update_ctg':
				$this->setTitle(_t('edit_ctg'));
				break;
			case 'add_media':
				$this->setTitle(_t('add_media'));
				break;
			case 'edit_media':
			case 'update_media':
				$this->setTitle(_t('edit_media'));
				break;
		}
	}

	///Constructor
	function __construct() {
		parent::__construct(_t('edit_page'));
		if($_SERVER['REQUEST_METHOD']=='POST') {
			$rv=$_POST;
		} else {
			$rv=$_GET;
		}
		//Limpiar y filtrar variables
		$rv=$this->getMediaMGR()->filterVars($rv);
		$this->updateTitle($rv['action']);
		$this->setParam('id',$rv['id']);
		$this->setParam('action',$rv['action']);
		$this->setParam('pid',$rv['pid']);

		if(!$this->IAmAuthenticated() || !$this->IAmAdmin()) {
			$this->redirect2page('login',True);
		}

		print_r($rv); echo('<br />');
		$rv=$this->expandAction($rv);
		print_r($rv); echo('<br />');
		$rv=$this->checkActionConsistency($rv);
		print_r($rv); echo('<br />');
		
		
		//END
		$this->rv=&$rv;
		return;

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
						//2o obtener el id más alto de la tabla descs
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
						//2o obtener el id más alto de la tabla descs
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
		require_once(dirname(__FILE__) . '/../../widgets/notebook.php');
		$rv=$this->rv;

		if(!$rv['valid'] && $rv['edit']) {
			echo('<div class="error">' . _t('data_error') . '</div><br />');
		}

		//Start Content
		echo('<fieldset class="setjumpfrm">');
		echo('<form action="' . $this->buildBaseUri($this->getArgs()) . '" method="post">');

		$book=new ApfNoteBook();

		// ** Página de propiedades **
		$content=_t('_id') . ":
		<input type='text' name='id' value='{$rv['id']}' disabled='true' />
";
		if(!$rv['new']) {
			$content.='&nbsp;' . _t('delete') . ":
			<input type='checkbox' name='delete' />
";
		}

		//Generar arbol de directorios
		$tree=$this->getMediaTree();

		$content.='<br />' . _t('category') . ": 
		<select name='pid'>
		" . $tree->getOptions($rv['pid']) . "
		</select>
		<hr />
		" . _t('_name') . ": 
		<input type='text' name='name' value='{$rv['name']}' /><br />
		" . _t('desc') . ": <br />
		<textarea name='desc' rows='5' cols='50'>{$rv['desc']}</textarea>
";

		$book->AddPage(_t('props'),$content);
		$book->AddPage('Hello world','Hi there');
		$book->AddPage('Hello there','No no No');
		$book->Write();

		//End Content
		echo('</form>');
		echo('</fieldset>');
		return;

		?>
		<?php
			if($this->type==1) {
?>
<script language="JavaScript" type="text/javascript">
//Definir hooks del padre
function upload_hook(file) {
	alert(file);
}
</script>


<fieldset>
<legend><?php echo(_t("upload_video")); ?></legend>
<iframe name="upload" src="<?php echo($this->buildBaseURI("iframe.php?page=upload&amp;type=video&amp;end_hook=upload_hook")); ?>" frameborder="0" width="100%" height="100">
<?php /* height="300" */
	echo(_t("unsuported_outdated_old_browser"));
?>
</iframe>
</fieldset>
<fieldset>
<legend>Old shit (outdated)</legend>

<?php
				//Vídeo
				echo(_t("properties") . ":<br>");
				if(empty($this->prev)) {
					$this->image=$this->buildBaseUri("imgs/videoimg.jpg");
				} else {
					$this->image=$this->buildBaseUri("cache/" . $this->prev);
				}
				echo('<img alt="blah" src="' . $this->image . '" border=0 width="160" height="120"><br>');
				echo(_t("preview") . ": ");
				echo('<input type="text" name="prev" value="' . $this->prev . '">
				<INPUT type="checkbox" name="prev_auto" value="1"> 
				' . _t("automatic") . '
				<br>');
				echo(_t("url") . ": ");
				echo('<input type="text" name="url" value="' . $this->url . '"><br>');
				echo(_t("lenght") . ": ");
				echo('<input type="text" name="dur" value="' . $this->dur . '"><br>');
				echo("</fieldset>");
			}
		?>
		
		<input type="hidden" name="new" value="<?php echo($this->new); ?>">
		<input type="hidden" name="action" value="<?php echo($this->action); ?>">
		<INPUT type="submit" value="<?php echo(_t("go")); ?>">
		<INPUT type="reset" value="<?php echo(_t("reset")); ?>">
		</form>
		
		<?php
	
	}

}

?>