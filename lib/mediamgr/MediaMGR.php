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

	/// Genera el árbol de categorías
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

	/// Obtener vídeos nuevos
	/// @param cut Corte overflow
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

}


?>