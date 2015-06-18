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

 
/**
 * Es llamado cuando el usuario quiere dar de alta una nueva categoría (el usuario es
 * administrador 
 */
function altaCategoria()
{
	/* mostramos el panel de alta de categoria */
	mostrar(document.getElementById('alta_categoria'));
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
						/* actualizamos la página */
						location.reload(true);
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



/**
 * Funciones para implementar la lógica del diálogo de confirmación de eliminación de un producto 
 */
 
// variable auxiliar que guardará la id de la categoría que se desea eliminar.
var id_categoria_a_eliminar;
 
/**
 * Es invocada cuando el usuario administrador quiere eliminar una categoría
 * @param id_categoria Es la id de la categoria que quiere eliminar
 * @param nombre_categoria Es el nombre de la misma
 */
function abrirDialogoEliminarCategoria(id_categoria, nombre_categoria)
{
	document.getElementById('categoria_a_eliminar').innerHTML = nombre_categoria;
	id_categoria_a_eliminar = id_categoria;
	
	/* mostramos el panel de confirmación de eliminación de la categoría */
	mostrar(document.getElementById('eliminar_categoria'));
	/* mostramos el fondo */
	mostrar(document.getElementById('fondo'));
}

/**
 * Es invocada cuando el usuario cancela la eliminación de una categoría
 */
function cerrarDialogoEliminarCategoria()
{
	esconder(document.getElementById('eliminar_categoria'));
	esconder(document.getElementById('fondo'));
}

/**
 * Es invocada cuando el usuario administrador confirma la eliminación de la categoría 
 */
function eliminarCategoria()
{
	/* enviamos la petición al servidor */
	crearPeticionHttpAjax('POST', 'eliminar_categoria.php', 
		function(readyState, status, responseText)
		{
			if(readyState == 4)
			{
				if(status == 200)
				{
					if(responseText == 'OK')
					{
						/* actualizamos la página */
						location.reload(true);
					}
					else
					{ 
						/* error al eliminar la categoría */
						errorEliminacionCategoria(responseText);
					}
				}
				else
				{
					/* servidor no disponible */
					errorEliminacionCategoria('El servidor no está disponible');
				}
			}
		}, 'categoria=' + id_categoria_a_eliminar);
}

/**
 * Se invoca cuando se ha producido un error en la eliminación de la categoría
 */
function errorEliminacionCategoria(msg)
{
	esconder(document.getElementById('eliminar_categoria'));
	document.getElementById('eliminar_categoria_error_descripcion').innerHTML = msg;
	mostrar(document.getElementById('eliminar_categoria_error'));
}

/**
 * Es invocada cuando el usuario administrador cierra la ventana que le indicaba que se había producido
 * un error al eliminar la categoría
 */
function cerrarDialogoEliminarCategoriaError()
{
	esconder(document.getElementById('eliminar_categoria_error'));
	esconder(document.getElementById('fondo'));
}
