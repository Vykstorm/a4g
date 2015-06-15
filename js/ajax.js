

/**
 * @author Víctor Ruiz Gómez
 * @file \brief
 * Este script crea un wrapper sencillo de la librería ajax de javascript
 */

/**
 * @return Crea una petición http ajax asíncrona. 
 * 
 * @param método Es 'POST' o 'GET'
 * @param url La url 
 * @param callback Es una función que será invocada cuando el progreso de la petición cambie.
 * El callback tomará tres parámetros (readyState, status, responseText). El ultimo de ellos, solo estará disponible si y solo si
 * readyState=4 y status=200
 * @param send Es un parámetro opcional. Si el método es POST, este parámetro servirá para valores
 * y enviarlos al servidor. e.g send="name=pepe&password=1234" 
 */
function crearPeticionHttpAjax(metodo, url, callback, send, requestHeader) 
{
	console.log(metodo + ' url: ' + url + ', parametros de POST: ' + send);
	/* código obtenido de www.w3schools.com */
	var xmlhttp;
	if(window.XMLHttpRequest) // code for IE7+, Firefox, Chrome, Opera, Safari
	{
		xmlhttp=new XMLHttpRequest();
	}
	else // code for IE6, IE5
	{
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = 
		function()
		{
			callback(xmlhttp.readyState, xmlhttp.status, xmlhttp.responseText);
		}
	xmlhttp.open(metodo, url, true);
	xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xmlhttp.send(send);
}


/**
 * Es igual que la anterior, solo que no establecer el request header del objeto xmlhttp 
 * @note Al parecer, las peticiones ajax con ficheros no pueden tener el header "application/x-www-form-urlencoded"
 */
function crearPeticionHttpAjax2(metodo, url, callback, send, requestHeader)
{
	console.log(metodo + ' url: ' + url + ', parametros de POST: ' + send);
	/* código obtenido de www.w3schools.com */
	var xmlhttp;
	if(window.XMLHttpRequest) // code for IE7+, Firefox, Chrome, Opera, Safari
	{
		xmlhttp=new XMLHttpRequest();
	}
	else // code for IE6, IE5
	{
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = 
		function()
		{
			callback(xmlhttp.readyState, xmlhttp.status, xmlhttp.responseText);
		}
	xmlhttp.open(metodo, url, true);
	xmlhttp.send(send);
}
