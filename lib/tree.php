<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

///Crea un arbol del sistema virtual de ficheros.
class ApfTree {
	var $tree;

	///Constructor
	///@param vals Array de valores (id,parent,name)
	function ApfTree($vals) {
		
		$cnt=count($vals);
		
		$tree["id"]=1;
		$tree["name"]="Media";
		$tree["parent"]=null;
		$tnodes[]=&$tree;
		
		$e=0;
		while(!empty($tnodes[$e]["name"])) {
			$cnode=&$tnodes[$e++];
			for($i=0; $i<$cnt; $i++) {
				if($vals[$i][1]==$cnode["id"]) {
					$node["id"]=$vals[$i][0];
					$node["name"]=$vals[$i][2];
					$node["parent"]=&$cnode;
					$cnode["childs"][]=&$node;
					$tnodes[]=&$node;
					unset($node);
					//echo($i . " " . $vals[$i][0] . $vals[$i][2] . "<br>\n");
				}
			}
		}
		//print_r($tree);
		$this->tree=&$tree;
	}
	
	/// Escribe las opciones del nodo selecionado.
	/// @param id El identificador del nodo.
	function writeOptions($id) {
		$stack[]=&$this->tree;
		$this->tree["j"]=0;
		
		$close[0]=0;
		
		//$this->writeNode(2,"kaki",1,$id);
		
		//$sane=0;

		while(count($stack)>0) {
		
			/*$sane++;
			if($sane>100) die(".");*/
			
			//print_r($stack);
		
			$cnt=count($stack);
			$cnode=&$stack[$cnt-1];
			if($cnode["j"]==0) {
				$this->writeNode($cnode["id"],$cnode["name"],$cnt-1,$id,$close);
			}
			if($cnode["j"]>=count($cnode["childs"])) {
				//echo("deleting from stack\n");
				unset($stack[$cnt-1]); //delete from stack
			} else {
				//add unwalked child to the stack
				//echo("inserting into the stack\n");
				$cnode["childs"][$cnode["j"]]["j"]=0;
				$stack[$cnt]=&$cnode["childs"][$cnode["j"]++];
				if($cnode["j"]>=count($cnode["childs"])) {
					$close[$cnt-1]=1;
				} else {
					$close[$cnt-1]=0;
				}
			}
		}
	
		/*
			<option value="1">* bla</option>
		<option value="2">|- bla2</option>
		<option value="4">&nbsp; |- bla6</option>*/
	}

	///Busca un nodo
	///@param id Identificador
	///@return Devuelve una referencia al nodo (o null)
	function findNode($id) {
		$stack[]=&$this->tree;
		$this->tree["j"]=0;
		while(count($stack)>0) {
			$cnt=count($stack);
			$cnode=&$stack[$cnt-1];
			if($cnode["j"]==0) {
				//$this->writeNode($cnode["id"],$cnode["name"],$cnt-1,$id,$close);
				if($cnode["id"]==$id) {
					return $cnode;
				}
			}
			if($cnode["j"]>=count($cnode["childs"])) {
				//echo("deleting from stack\n");
				unset($stack[$cnt-1]); //delete from stack
			} else {
				//add unwalked child to the stack
				//echo("inserting into the stack\n");
				$cnode["childs"][$cnode["j"]]["j"]=0;
				$stack[$cnt]=&$cnode["childs"][$cnode["j"]++];
				if($cnode["j"]>=count($cnode["childs"])) {
					$close[$cnt-1]=1;
				} else {
					$close[$cnt-1]=0;
				}
			}
		}
		return null;
	}
	
	///Escribe un Nodo.
	///@param id Identificador
	///@param name Nombre del nodo
	///@param level Nivel dentro del arbol
	///@param sel_id Identificador del nodo actualmente selecionado
	///@param close_table Tabla que identifica que niveles del arbol deben cerrarse
	function writeNode($id,$name,$level,$sel_id,$close) {
		echo('<option value="' . $id . '"');
		if($id==$sel_id) {
			echo(' selected');
		}
		echo('>');
		if($level==0) {
			echo("*");
		} else {
			for($i=0; $i<$level-1; $i++) {
				if($close[$i]==1) {
					echo("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
				} else {
					echo("|&nbsp;&nbsp;&nbsp;&nbsp;");
				}
			}
			if($close[$i]==1) {
				echo("`--");
			} else {
				echo("|--");
			}
		}
		echo(" " . $name . "</option>" . "\n");
	}
	
}


?>