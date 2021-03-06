<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

///Clase base para AJAX (Asynchronous JavaScript And XML)
class ApfAjax {
	private static $send=False;

	function __construct() {
		throw new Exception("This class cannot be instanciated");
	}

	///Escribe el código base necesario para cualquier aplicación Ajax
	static function write() {
		if(!self::$send) {
			echo(self::get());
		}
	}

	///Escribe el código base necesario para cualquier aplicación Ajax
	static function get() {
		if(self::$send) return;
		self::$send=True;
$out=<<<EOF
//Variable global contenedora del objecto
//var http=false;

/** Devuelve el object XMLHttpRequest según el navegador
    devuleve falso si no se puede crear el objeto.
*/
function getXMLHttpRequest() {
	var cli=false;
	//Acceso nativo en la mayoria de los navegadores
	if(window.XMLHttpRequest) {
		try {
			cli=new XMLHttpRequest();
		} catch(e) {
			cli=false;
		}
	} else if(window.ActiveXObject) {
	//Esto es sola para el IE
		try {
			cli=new ActiveXObject("Msxml2.XMLHTTP");
		} catch(e) {
			try {
				cli=new ActiveXObject("Microsoft.XMLHTTP");
			} catch(e) {
				cli=false;
			}
		}
	}
	return cli;
}

function get_ajax() {
	var http=getXMLHttpRequest();
	if(!http) alert("Imposible inicializar el HTTPRequest");
	return http;
}
EOF;

/*

function ajax_init() {
	http=getXMLHttpRequest();

	if(!http) alert("Imposible inicializar el HTTPRequest");
	//else alert("parece funcionar");
}

ajax_init();

//test

function callback() {
	var out=document.getElementById("container");
	out.innerHTML+="Processando... estado: ";
	out.innerHTML+=http.readyState;
	out.innerHTML+="<br>";
	if (http.readyState == 4) {
		out.innerHTML+="Finito datos: ";
		out.innerHTML+=http.status;
		out.innerHTML+=http.responseText;
		out.innerHTML+="<br>";
	}
}

function trabaja() {
	http.onreadystatechange=callback;
	http.open("GET", "info.php", true);
	http.send("hola");
} */
		return $out;
	}

} //End Class ApfAjax


?>