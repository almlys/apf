<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head><title>Ajax Client</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<script language="javaScript" type="text/javascript">
//Variable global contenedora del objecto
var http=false;

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
}

</script>
</head>
<body onload="trabaja();">

<div id="container">
<!-- empty -->
</div>

</body>
</html>