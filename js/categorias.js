/**
 * 
 * @author Víctor Ruiz Gómez
 * @file \brief Es un script que implementa logica adicional de la página del catálogo
 * de categorías de productos.
 * Debe incluirse antes que el script ajax.js y comun.js
 */
 
/** reemplazamos la acción a realizar en caso de que el usuario cierre la sesión */
usuarioLoggedOut = function() { 
	location.reload(true);
}

/** reemplazamos la acción a realizar en caso de que el usuario se logee */
usuarioLogeado = function() { 
	location.reload(true);
}

/** que hacemos cuando estamos en el panel de login/alta/alta de categoría y clickeamos 
 * en el fondo */
function restaurarPagina() 
{
	esconder(document.getElementById('alta_categoria'));
	esconder(document.getElementById('fondo'));
}

 
/**
 * Es llamado cuando el usuario quiere dar de alta una nueva categoría (el usuario es
 * administrador 
 */
function altaCategoria()
{
	/* mostramos el panel de alta de categoria */
	mostrar(document.getElementById('alta_categoria'));
	
	/* escondemos el resto de paneles */
	esconderAlta();
	esconderLogin();
	
	mostrar(document.getElementById('fondo'));
}

/**
 * Es invocado cuando el usuario envía el alta de la categoría 
 */
function enviarAltaCategoria()
{
	/* creamos los datos del formulario */
	var formData = new FormData();
	formData.append('nombre', document.getElementById('categoria_nombre').value);
	var imagen = document.getElementById('categoria_imagen').files[0];
	formData.append('imagen', imagen, imagen.name);
	formData.append('familia', document.getElementById('categoria_familia').value);
	
	/* desactivamos el botón de enviar alta */
	document.getElementById('enviar_alta_categoria').disabled = true;

	/* enviamos la petición al servidor */
	crearPeticionHttpAjax2('POST', 'alta_categoria.php', 
		function(readyState, status, responseText)
		{
			if(readyState == 4)
			{
				if(status == 200)
				{
					/* volvemos a activar el botón de envío */
					document.getElementById('enviar_alta_categoria').disabled = false;
					
					if(responseText == 'OK')
					{
						window.open('index.php?accion=verCategorias', '_self');
						restaurarPagina();
					}
					else
					{ 
						document.getElementById('alta_categoria_error').innerHTML = responseText;
						mostrar(document.getElementById('alta_categoria_error'));
					}
				}
				else
				{
					document.getElementById('alta_categoria_error').innerHTML = 'Servidor no disponible';
					mostrar(document.getElementById('alta_categoria_error'));
				}
			}
		}, formData);
}
