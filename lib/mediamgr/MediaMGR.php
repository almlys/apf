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



}


?>