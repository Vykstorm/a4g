/**
 * @author Víctor Ruiz Gómez
 * @file \brief Es un script para definir la lógica de la página de visualización
 * de un producto (para implementar el carrusel de imágenes, el visor 3D, ...)
 * Debe incluirse después que el script comun.js.
 * 
 * Requisitos:
 * - La id que permite al usuario descargar un producto es "descargar"
 * - La id de "Añadir producto al carrito" es "comprar"
 * - La id de de la select para indicar la valoración, es "valoracion", pero
 * la id de la división que muestra el panel para indicar la valoración es "valorar"
 */

 
/**
 * Esta función es invocada cuando el usuario quiere ver el producto en 3D
 * (el producto es un modelo 3D)
 * @param id Es la id del producto.
 */
function verEn3D(id)
{
	esconder(document.getElementById('carrusel'));
	mostrar(document.getElementById('visor3D'));
	esconder(document.getElementById('ver3D'));
	/* inicializar el visor 3D aqui */
	// TODO
}

/**
 * Esta función es invocada cuando el cliente quiere añadir el producto al carrito
 * de la compra 
 * @param id Es la id del producto
 */
function comprarProducto(id)
{
	/* si el usuario no está logeado, mostrarle el panel de login */
	if(!estaUsuarioLogeado())
	{
		mostrarLogin();
		return;
	}
	
	/* hacemos una petición de "añadir producto al carrito" al servidor (AJAX) */
	crearPeticionHttpAjax('POST', 'shopcart.php', 
		function(readyState, status, responseText)
		{
			if((readyState == 4) && (status == 200) && (responseText == 'OK'))
			{
				/* una vez finalizada la petición, desactivamos el botón de añadir producto
				 * al carrito */
				document.getElementById('comprar').disabled = true;
				
				window.open('index.php?accion=verCarrito', '_self');
			}
		}, 'accion=meterProducto&producto=' + id);
}

/**
 * Esta función es invocada cuando el usuario cambia la valoración del producto
 * @param id Es la id del producto
 * @param valoracion Es la nueva valoración del producto.
 */
function valorarProducto(id, valoracion)
{
	/* realizamos una petición ajax al servidor para actualizar la valoración del producto */
	crearPeticionHttpAjax('POST', 'product.php?accion=valorar&producto=' + id, 
		function(readyState, status, responseText)
		{
		}, 'valoracion=' + valoracion);	
}

/**
 * Es invocado cuando el usuario envia un comentario sobre el producto.
 * @param id Es la id del producto
 * @param comentario Es el texto del comentario que ha realizado.
 */
function comentarProducto(id, comentario)
{
	document.getElementById('enviar_comentario').disabled = true;
	/* realizamos petición ajax al servidor para enviar el comentario */
	crearPeticionHttpAjax('POST', 'product.php?accion=comentar&producto=' + id,
		function(readyState, status, responseText)
		{
			if(readyState == 4)
			{
				location.reload(true);
			}
		}, 'comentario=' + comentario);
}

/**
 * Es invocado cuando el usuario administrador quiere eliminar el producto.
 * @param id Es la id del producto
 */
function eliminarProducto(id)
{
	esconderPopup(document.getElementById('eliminar_producto'));
	crearPeticionHttpAjax('POST', 'product.php?accion=eliminar&producto=' + id,
		function(readyState, status, responseText)
		{
			if(readyState == 4)
			{
				if((status == 200) && (responseText == 'OK'))
				{
					/* el producto ha sido eliminado, ir al índice */
					window.open('index.php', '_self');
				}
				else
				{
					/* hubo un error en la eliminación del producto */
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

/* reemplazamos la acción a realizar en caso de que el usuario cierre la sesión */
usuarioLoggedOut = function() { 
	location.reload(true);
}

/* reemplazamos la acción a realizar en caso de que el usuario se logee */
usuarioLogeado = function() { 
	location.reload(true);
}

