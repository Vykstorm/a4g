/**
 * @author Víctor Ruiz Gómez.
 * @file \brief 
 * Este script implementa la lógica de la página de catálogo de 
 * productos. Debe ser incluido después de comun.js y ajax.js
 */


/** si el usuario se logea, o se deslogea, actualizar la página */
usuarioLoggedOut = function() { 
	location.reload(true);
}
usuarioLogeado = function() { 
	location.reload(true);
}


/** Funciones que implementan la lógica de eliminar productos */
var id_producto_a_eliminar; 
/**
 * Abre el dialogo de confirmación de eliminación de un producto.
 * @param id_categoria Es la id del producto a eliminar.
 * @param nombre_producto Es el nombre del producto.
 */
function abrirDialogoEliminarProducto(id_producto, nombre_producto)
{
	document.getElementById('producto_a_eliminar').innerHTML = nombre_producto;
	id_producto_a_eliminar = id_producto;
	
	/* mostramos el panel de confirmación de eliminación de la categoría */
	mostrarPopup(document.getElementById('eliminar_producto'));
}

function eliminarProducto()
{
	esconderPopup(document.getElementById('eliminar_producto'));
	/* enviamos la petición al servidor */
	crearPeticionHttpAjax('POST', 'product.php?accion=eliminar&producto=' + id_producto_a_eliminar, 
		function(readyState, status, responseText)
		{
			if(readyState == 4)
			{
				if((status == 200) && (responseText == 'OK'))
				{
					/* producto eliminado correctamente. Actualizamos la página */
					location.reload(true);
				}
				else 
				{
					/* hubo un error */
					if(status == 404)
					{
						document.getElementById('eliminar_producto_error_descripcion').innerHTML = 'Servidor no disponible';
					}
					else
					{
						document.getElementById('eliminar_producto_error_descripcion').innerHTML = responseText;
					}
					mostrarPopup(document.getElementById('eliminar_producto_error'));
				}
			}
		});
}
