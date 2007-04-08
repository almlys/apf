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
	else alert("parece funcionar");
}

ajax_init();

