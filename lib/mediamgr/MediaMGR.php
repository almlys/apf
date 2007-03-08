<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

/// Classe controladora de los recursos guardados en la biblioteca
class MediaMGR {
	private $DB;
	private $tree;

	/// Constructor
	/// @param DB Base de datos a usar
	function __construct($DB) {
		$this->DB=$DB;
	}

	function query($q) {
		return $this->DB->query($q);
	}

	function fetchArray() {
		return $this->DB->fetchArray();
	}

	function insertId() {
		return $this->DB->insertId();
	}


	/// Buscar datos de una categoria en especial
	/// @param id Identificador
	/// @returns Un array con los datos pedidos
	function findCategory($id) {
		$result=array();
		foreach (ApfLocal::getLanguageVector() as $lan) {
			$lan=substr($lan,0,2);
			/*$query="select a.parent,b.name,c.desc
							from vid_categ a, vid_names b, vid_descs c
							where	a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan and b.lan=\"$lan\" and a.id=$id";
			*/
			$query="select a.parent,b.name,c.desc,a.count
							from vid_categ a inner join (vid_names b, vid_descs c)
							on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
							where b.lan=\"$lan\" and a.id=$id";
			$this->DB->query($query);
			$vals=$this->DB->fetchArray();

			if($vals!=null) {
				//Lo hemos encontrado (fijar algunas propiedades)
				$result['pid']=$vals[0];
				$result['name']=$vals[1];
				$result['desc']=$vals[2];
				$result['count']=$vals[3];
				return $result;
			}
		}
		return null;
	}

	/// Salvar categoria
	/// @param rv Array de valores
	function saveCategory($rv) {
		//1o Actualizar nombre y descripción
		$lan=ApfLocal::getDefaultLanguage();
		$query="update vid_names b, vid_descs c, vid_categ a
						set b.name=\"{$rv['name']}\",
						c.desc=\"{$rv['desc']}\",
						a.parent=\"{$rv['pid']}\"
						where a.name_id=b.id and a.desc_id=c.id 
						and b.lan=c.lan and b.lan=\"$lan\" and a.id={$rv['id']};";
		$this->query($query);
	}

	/// Borrar categoria
	/// @param id ID
	function deleteCategory($id) {
		$query="delete from vid_categ where id=$id;";
		$this->query($query);
		///TODO - borrar nodos huerfanos
	}

	/// Crear una nueva categoria
	/// @param rv Array datos
	/// @returns id de la ctg
	function createCategory($rv) {
		global $APF;
		//1o obtener id mas alto de la tabla names
		$query="select max(id) from vid_names";
		$this->query($query);
		$ret=$this->fetchArray();
		//if($ret==null) $this->error_die("Null fetchArray at line " . __LINE__);
		$name_id=$ret[0]+1;
		//2o obtener el id más alto de la tabla descs
		$query="select max(id) from vid_descs";
		$this->query($query);
		$ret=$this->fetchArray();
		//if($ret==null) $this->error_die("Null fetchArray at line " . __LINE__);
		$desc_id=$ret[0]+1;

		//Por todos los idiomas
		foreach ($APF["languages"] as $xlan) {
			$xlan=substr($xlan,0,2);
			//Insertar el nombre
			$query="insert into vid_names (id,lan,name) values($name_id,\"$xlan\",\"{$rv['name']}\")";
			$this->query($query);
			//Insertar la descripción
			//NOTA mental, desc es reserved keyword, y debe ir entre comillas
			$query="insert into vid_descs (id,lan,`desc`) values($desc_id,\"$xlan\",\"{$rv['desc']}\")";
			$this->query($query);
		}

		//Insertar el registro
		$query="insert into vid_categ (parent,name_id,desc_id) values({$rv['pid']},$name_id,$desc_id)";
		$this->query($query);
		return $this->insertId();
	}


	/// Genera el árbol de categorías
	/// @returns El arbol
	function getMediaTree() {
		if(empty($this->tree)) {
			require_once(dirname(__FILE__) . "/../widgets/tree.php");
			$lan=ApfLocal::getDefaultLanguage();
			$query="select a.id,a.parent,b.name
							from vid_categ a inner join vid_names b
							on a.name_id=b.id
							where b.lan=\"$lan\"";
			$this->DB->query($query);
			$i=0;
			while($vals[$i++]=$this->DB->fetchArray()) {
				//echo($i-1 . " " . $vals[$i-1][0] . $vals[$i-1][2] . "<br>\n");
			}
			//echo("<hr>");
			$this->tree=new ApfTree($vals);
		}
		return $this->tree;
	}

	/// Obtener todas las carpetas
	/// @param id Identificador
	function getFolders($id) {
		$lan=ApfLocal::getDefaultLanguage();
		//$query="select id,$name,$desc,count from vid_categ where parent=" . $this->id;
		$query="select a.id,b.name,c.desc,a.count
						from vid_categ a inner join (vid_names b, vid_descs c)
						on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
						where b.lan=\"$lan\" and a.parent=" . $id;
		$this->DB->query($query);
		$r=array();
		$i=0;
		while($vals=$this->DB->fetchArray()) {
			$r[$i]['id']=$vals[0];
			$r[$i]['name']=$vals[1];
			$r[$i]['desc']=$vals[2];
			$r[$i]['count']=$vals[3];
			$i++;
		}
		return $r;
	}

	/// Obtener todas las vídeos
	/// @param id Identificador
	function getMedia($id) {
		$lan=ApfLocal::getDefaultLanguage();
		//$query="select id,$name,$desc,prev,dur from vid_mfs where ctg=" . $this->id;
		$query="select a.id,b.name,c.desc,a.prev,a.dur
						from vid_mfs a inner join (vid_names b, vid_descs c)
						on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
						where b.lan=\"$lan\" and a.ctg=" . $id;
		$this->DB->query($query);
		$r=array();
		$i=0;
		while($vals=$this->DB->fetchArray()) {
			$r[$i]['id']=$vals[0];
			$r[$i]['name']=$vals[1];
			$r[$i]['desc']=$vals[2];
			$r[$i]['prev']=$vals[3];
			$r[$i]['dur']=$vals[4];
			$i++;
		}
		return $r;
	}

	/// Obtener vídeos nuevos
	/// @param cut Corte overflow
	/// @returns array
	function getNewMedia($cut) {
		$lan=ApfLocal::getDefaultLanguage();
		//$query="select id,$name,$desc,prev,dur from vid_mfs where ctg=" . $this->id;
		$query="select a.id,b.name,c.desc,a.prev,a.dur
						from vid_mfs a inner join (vid_names b, vid_descs c)
						on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
						where b.lan=\"$lan\" order by a.created desc limit $cut";
		$this->DB->query($query);
		$r=array();
		$i=0;
		while($vals=$this->DB->fetchArray()) {
			$r[$i]['id']=$vals[0];
			$r[$i]['name']=$vals[1];
			$r[$i]['desc']=$vals[2];
			$r[$i]['prev']=$vals[3];
			$r[$i]['dur']=$vals[4];
			$i++;
		}
		return $r;
	}

	/// Obtener vídeos más vistos
	/// @param cut Corte overflow
	/// @returns array
	function getTopMedia($cut) {
		$lan=ApfLocal::getDefaultLanguage();
		//$query="select id,$name,$desc,prev,dur from vid_mfs where ctg=" . $this->id;
		$query="select a.id,b.name,c.desc,a.prev,a.dur
						from vid_mfs a inner join (vid_names b, vid_descs c)
						on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
						where b.lan=\"$lan\" order by a.hits desc limit $cut";
		$this->DB->query($query);
		$r=array();
		$i=0;
		while($vals=$this->DB->fetchArray()) {
			$r[$i]['id']=$vals[0];
			$r[$i]['name']=$vals[1];
			$r[$i]['desc']=$vals[2];
			$r[$i]['prev']=$vals[3];
			$r[$i]['dur']=$vals[4];
			$i++;
		}
		return $r;
	}

	/// Debuelve un array con datos del vídeo solicitado
	/// @param id Identificador
	/// @returns array
	function getVideo($id) {
		$lan=ApfLocal::getDefaultLanguage(); //Obtener idioma por defecto
		//$query="select ctg,$name,$desc,prev,dur,url from vid_mfs where id=$id;";
		$query="select a.ctg,b.name,c.desc,a.prev,a.dur,a.url
						from vid_mfs a inner join (vid_names b, vid_descs c)
						on (a.name_id=b.id and a.desc_id=c.id and b.lan=c.lan)
						where b.lan=\"$lan\" and a.id=$id";
		$this->DB->query($query);
		$vals=$this->DB->fetchArray();
		if($vals==null) return null;
		$r['pid']=$vals[0];
		$r['name']=$vals[1];
		$r['desc']=$vals[2];
		$r['prev']=$vals[3];
		$r['dur']=$vals[4];
		$r['url']=$vals[5];
		
		//$query="select $name from vid_categ where id=" . $this->pid;
		$query="select b.name
						from vid_categ a inner join vid_names b
						on a.name_id=b.id
						where b.lan=\"$lan\" and a.id=" . $r['pid'];
		$this->DB->query($query);
		$vals=$this->DB->fetchArray();
		$r['category']=$vals[0];
		return $r;
	}

	/// Incrementa el número de hits de un vídeo
	/// @param id Identificador
	function increaseVideoHitCount($id) {
		$query="update vid_mfs set hits = hits+1
						where vid_mfs.id = $id;";
		$this->DB->query($query);
	}

	/// Salva un video en la DB
	/// @param rv array
	function saveVideo($rv) {
		$lan=ApfLocal::getDefaultLanguage();
		//1o Actualizar nombre y descripción
		$query="update vid_names b, vid_descs c, vid_mfs a
						set b.name=\"{$rv['name']}\",
						c.desc=\"{$rv['desc']}\",
						a.ctg=\"{$rv['pid']}\",
						a.prev=\"{$rv['prev']}\",
						a.dur=\"{$rv['dur']}\",
						a.url=\"{$rv['url']}\"
						where a.name_id=b.id and a.desc_id=c.id
						and b.lan=c.lan and b.lan=\"$lan\" and a.id={$rv['id']}";
		$this->query($query);
	}

	/// Borra un vídeo
	/// @param id Identificador
	function deleteVideo($id) {
		$query="delete from vid_mfs where id=$id";
		$this->query($query);
		///TODO - borrar nodos huerfanos
	}

	/// Crea un vídeo
	/// @param rv Array
	/// @returns id
	function createVideo($rv) {
		global $APF;
		//1o obtener id mas alto de la tabla names
		$query="select max(id) from vid_names";
		$this->query($query);
		$ret=$this->fetchArray();
		//if($ret==null) $this->error_die("Null fetchArray at line " . __LINE__);
		$name_id=$ret[0]+1;
		//2o obtener el id más alto de la tabla descs
		$query="select max(id) from vid_descs";
		$this->query($query);
		$ret=$this->fetchArray();
		//if($ret==null) $this->error_die("Null fetchArray at line " . __LINE__);
		$desc_id=$ret[0]+1;
		//Por todos los idiomas
		foreach ($APF["languages"] as $xlan) {
			$xlan=substr($xlan,0,2);
			//Insertar el nombre
			$query="insert into vid_names (id,lan,name) values($name_id,\"$xlan\",\"{$rv['name']}\")";
			$this->query($query);
			//Insertar la descripción
			//NOTA mental, desc es una reserved keyword
			$query="insert into vid_descs (id,lan,`desc`) values($desc_id,\"$xlan\",\"{$rv['desc']}\")";
			$this->query($query);
		}
		//Insertar el registro
		$query="insert into vid_mfs (ctg,prev,dur,url,name_id,desc_id,created) values({$rv['pid']},\"{$rv['prev']}\",\"{$rv['dur']}\",\"{$rv['url']}\",$name_id,$desc_id,NOW())";
		$this->query($query);
		return $this->insertId();
	}


	//Edit Stuff

	/// Filtrar variables
	/// @param rv Array de entrada
	/// @returns Array filtrado y limpio
	function filterVars($rv) {
		$out=array();
		if(!isset($rv['action'])) {
			$out['action']='none';
			return $out;
		} else {
			$out['action']=$this->DB->escape_string($rv['action']);
		}
		$chk['id']=array('type' => 'int', 'def' => 0);
		$chk['name']=array('type' => 'str', 'def' => '');
		$chk['pid']=array('type' => 'int', 'def' => 0);
		//$chk['ctg']=array('type' => 'int', 'def' => 'none');
		$chk['prev']=array('type' => 'str', 'def' => '');
		$chk['dur']=array('type' => 'str', 'def' => '');
		$chk['url']=array('type' => 'str', 'def' => '');
		$chk['desc']=array('type' => 'str', 'def' => '');
		//True -> Insert, False -> Update
		$chk['new']=array('type' => 'int', 'def' => False);
		$chk['delete']=array('type' => 'int', 'def' => False);
		//ctg,media
		$chk['type']=array('type' => 'str', 'def' => 'none');
		//True -> Insert/Update, False -> Select
		$chk['edit']=array('type' => 'int', 'def' => False);
		$chk['valid']=array('type' => 'int', 'def' => False, 'ing' => True);
		foreach ($chk as $key => $c) {
			if(isset($rv[$key]) && !$c['ing']) {
				if($c['type']=='int') {
					$out[$key]=intval($rv[$key]);
				} else { //str
					$out[$key]=$this->DB->escape_string($rv[$key]);
				}
			} else {
				$out[$key]=$c['def'];
			}
		}
		return $out;
	}


}


?>