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
	private $missing_video=False;

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
			if(!$rv['new'] && (empty($rv['id']) || $rv['id']==0)) {
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
		parent::__construct(_t('edit_page'),False);
		if($_SERVER['REQUEST_METHOD']=='POST') {
			$rv=$_POST;
		} else {
			$rv=$_GET;
		}
		//Limpiar y filtrar variables
		$rv=$this->getMediaMGR()->filterVars($rv);
		$this->updateTitle($rv['action']);
		$this->setParam('id',$rv['id']);
		if($rv['action']=='update_ctg') {
			$raction='edit_ctg';
		} elseif($rv['action']=='update_media') {
			$raction='edit_media';
		} else {
			$raction=$rv['action'];
		}
		$this->setParam('action',$raction);
		$this->setParam('pid',$rv['pid']);

		if(!$this->IAmAuthenticated() || !$this->IAmAdmin()) {
			$this->redirect2page('login',True);
		}

		//print_r($rv); echo('<br />');
		$rv=$this->expandAction($rv);
		//print_r($rv); echo('<br />');
		$rv=$this->checkActionConsistency($rv);
		//print_r($rv); echo('<br />');
		
		//END
		$this->rv=&$rv;
		$this->preprocess();
		$this->release_session(); //Liberar sessión
		$this->process();
	}

	function preprocess() {
		$rv=&$this->rv;
		/*
		if($rv['edit']) {
			if(!empty($_SESSION['vod_video'])) {
				$rv['url']=$_SESSION['vod_video'];
				//echo($rv['url']);
			}
		}*/
		if($rv['edit'] && $rv['valid']) {
			if($rv['type']=='media' && isset($_SESSION['video.ok']) && $_SESSION['video.ok']) {
				$vod_server=$this->getVodServer();
				$result=$vod_server->GeneratePreview($_SESSION['video.path'],$_SESSION['video.file']);
				$rv['prev']=$result;
				//echo("prev:__" . $result);
				$result=$vod_server->UploadVideoFile($_SESSION['video.path'],$_SESSION['video.file']);
				$rv['url']=$result;
				$_SESSION['video.ok']=False;
				unset($_SESSION['video.path']);
				unset($_SESSION['video.file']);
				unset($_SESSION['video.ok']);
			}
		}
		//Revalidation (Marcar no valido, creaciones sin video)
		if($rv['new'] && $rv['valid'] && $rv['type']=='media' && empty($rv['url'])) {
			$rv['valid']=False;
			$this->missing_video=True;
		}
	}

	function process() {
		$rv=&$this->rv;
		$mgr=$this->getMediaMGR();
		if($rv['edit']) {
			//insert/update database
			if($rv['valid']) {
				if($rv['type']=='ctg') {
					if(!$rv['new'] && !$rv['delete']) {
						//update
						$mgr->saveCategory(&$rv);
					} elseif(!$rv['new'] && $rv['delete']) {
						//delete
						$mgr->deleteCategory($rv['id']);
						$this->redirect($this->BuildBaseUri($this->getArgs(array('page' => 'categ','id' => $rv['pid']),'','',False)));
					} else {
						//Insert
						$rv['id']=$mgr->createCategory(&$rv);
						//$this->redirect($this->BuildBaseUri($this->getArgs(array('page' => 'categ','id' => $rv['id']),'','',False)));
						$this->setParam('id',$rv['id']);
					}
				} elseif($rv['type']=='media') {
					//Videos
					if(!$rv['new'] && !$rv['delete']) {
						//update
						$mgr->saveVideo(&$rv);
					} elseif(!$rv['new'] && $rv['delete']) {
						//delete
						$mgr->deleteVideo($rv['id']);
						$this->redirect($this->BuildBaseUri($this->getArgs(array('page' => 'categ','id' => $rv['pid']),'','',False)));
					} else {
						//Insert
						$rv['id']=$mgr->createVideo(&$rv);
						//$this->redirect($this->BuildBaseUri($this->getArgs(array('page' => 'videos','id' => $rv['id']),'','',False)));
						$this->setParam('id',$rv['id']);
					}
				} else {
					//throw new Exception('Unknown resource type');
					$this->redirect2page('main');
				}
			}
		} else {
			//grab from database
			if(!$rv['new']) {
				//No es un nuevo registro
				if($rv['type']=='ctg') {
					$vals=$mgr->findCategory($rv['id']);
					$rv['pid']=$vals['pid'];
					$rv['name']=$vals['name'];
					$rv['desc']=$vals['desc'];
				} elseif($rv['type']=='media') {
					$vals=$mgr->getVideo($rv['id']);
					$rv['pid']=$vals['pid'];
					$rv['name']=$vals['name'];
					$rv['desc']=$vals['desc'];
					$rv['prev']=$vals['prev'];
					$rv['dur']=$vals['dur'];
					$rv['url']=$vals['url'];
				} else {
					//throw new Exception('Unknown resource type');
					$this->redirect2page('main');
				}
			}
		}
	}

	/// Obtener Manejador VoD
	/// @returns El manejador VoD solicitado
	function getVodServer() {
		global $APF;
		//Instanciate APF_VOD class
		require_once(dirname(__FILE__) . '/../../vod/VoDFactory.php');
		$vod_server=VoDFactory::getVoDHandler($APF['default_vod']);
		return $vod_server;
	}
	
	///Método body
	function body() {
		require_once(dirname(__FILE__) . '/../../widgets/notebook.php');
		require_once(dirname(__FILE__) . '/../../widgets/upload.php');
		$rv=&$this->rv;

		if($rv['edit']) {
			if(!$rv['valid']) {
				$missing='';
				if($this->missing_video) {
					$missing='<br />' . _t('missing_video');
				}
				echo('<div id="info_msg" class="error">' . _t('data_error') . $missing . '</div><br />');
			} else {
				echo('<div id="info_msg" class="message">' . _t('data_saved') . '</div><br />');
				$rv['new']=False;
			}
		} else {
				echo('<div id="info_msg" class="message"></div><br />');
		}

		//Start Content
		echo('<fieldset class="setjumpfrm">');
		echo('<form name="f1" action="' . $this->buildBaseUri($this->getArgs()) . '" method="post">');

		$book=new ApfNoteBook();

		// ** Página de propiedades **
		$props_content=_t('_id') . ":
		<input type='hidden' name='id' value='{$rv['id']}' />
		<input type='text' name='id2' value='{$rv['id']}' disabled='true' />
";
		if(!$rv['new']) {
			$props_content.='&nbsp;' . _t('delete') . ":
			<input type='checkbox' name='delete' value='1' />
";
		}

		//Generar arbol de directorios
		$tree=$this->getMediaTree();

		$props_content.='<br />' . _t('category') . ": 
		<select name='pid'>
		" . $tree->getOptions($rv['pid']) . "
		</select>
		<hr />
		" . _t('_name') . ": 
		<input type='text' name='name' value='{$rv['name']}' /><br />
		" . _t('desc') . ": <br />
		<textarea name='desc' rows='5' cols='50'>{$rv['desc']}</textarea>
";

		// ** Página de subida de vídeos **
		//$up=new UploadCtrl($this,'video','parent_callback');
		$up=new UploadCtrl($this,'video','parent_callback');
		$vid_content=$up->get();

		if($rv['type']=='media') {
			$book->AddPage(_t('upload_video'),$vid_content);
		}
		$book->AddPage(_t('props'),$props_content);
		//$book->AddPage('Hello world','Hi there');
		//$book->AddPage('Hello there','No no No');
		$book->Write();

		//End Content

?>
		<input type="hidden" name="new" value="<?php echo($rv['new']); ?>" />
		<input type="hidden" name="action" value="<?php echo($rv['action']); ?>" />
		<input type="submit" name="go" value="<?php echo(_t('go')); ?>" />
		<input type="reset" value="<?php echo(_t('reset')); ?>" />
		<input type='button' value='<?php echo(_t('return')); ?>' onclick='document.location="<?php
			if($rv['id']==0) {
				$continue_id=$rv['pid'];
				$continue_page='categ';
			} else {
				$continue_id=$rv['id'];
				if($rv['type']=='ctg') {
					$continue_page='categ';
				} else {
					$continue_page='videos';
				}
			}
			echo($this->buildBaseUri($this->getArgs(array('page' => $continue_page, 'id' => $continue_id))));
?>"'/>
		</form>
		</fieldset>
		<script language="JavaScript" type="text/javascript">
		//<![CDATA[

		function enable_submit() {
			document.f1.go.disabled=false;
		}

		function disable_submit() {
			document.f1.go.disabled=true;
		}

		//Definir hooks del padre (started,aborted,finished)
		function parent_callback(file,status,newname) {
			//alert(file + " " + status + " " + newname);
			if(status=='started') {
				disable_submit();
			} else {
				enable_submit();
			}
		}

		//]]>
		</script>

<?php
		return;

			if($this->type==1) {
?>

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
		<?php
	
	}

}

?>