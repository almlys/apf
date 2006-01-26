<?php
/*
  Copyright (c) 2005 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.
*/

class ApfTree {
	var $tree;

	function ApfTree($vals) {
		
		$cnt=count($vals);
		
		$tree["id"]=1;
		$tree["name"]="Media";
		$tnodes[]=&$tree;
		
		$e=0;
		while(!empty($tnodes[$e]["name"])) {
			$cnode=&$tnodes[$e++];
			for($i=0; $i<$cnt; $i++) {
				if($vals[$i][1]==$cnode["id"]) {
					$node["id"]=$vals[$i][0];
					$node["name"]=$vals[$i][2];
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
	
	function writeOptions($id) {
		$stack[]=&$this->tree;
		$this->tree["j"]=0;
		
		//$this->writeNode(2,"kaki",1,$id);
		
		//$sane=0;

		while(count($stack)>0) {
		
			/*$sane++;
			if($sane>100) die(".");*/
			
			//print_r($stack);
		
			$cnt=count($stack);
			$cnode=&$stack[$cnt-1];
			if($cnode["j"]==0) {
				$this->writeNode($cnode["id"],$cnode["name"],$cnt-1,$id);
			}
			if($cnode["j"]>=count($cnode["childs"])) {
				//echo("deleting from stack\n");
				unset($stack[$cnt-1]); //delete from stack
			} else {
				//add unwalked child to the stack
				//echo("inserting into the stack\n");
				$cnode["childs"][$cnode["j"]]["j"]=0;
				$stack[$cnt]=&$cnode["childs"][$cnode["j"]++];
			}
		}
	
		/*
			<option value="1">* bla</option>
		<option value="2">|- bla2</option>
		<option value="4">&nbsp; |- bla6</option>*/
	}
	
	function writeNode($id,$name,$level,$sel_id) {
		echo('<option value="' . $id . '"');
		if($id==$sel_id) {
			echo(' selected');
		}
		echo('>');
		if($level==0) {
			echo("*");
		} else {
			for($i=0; $i<$level-1; $i++) {
				echo("|&nbsp;&nbsp;&nbsp;&nbsp;");
			}
			echo("|--");
		}
		echo(" " . $name . "</option>" . "\n");
	}
	
}


?>