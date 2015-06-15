<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Este script gestiona las siguientes peticiones:
	 * - actualizar valoración de producto de un usuario
	 * - descargar un producto
	 * Parámetros que deben ser indicados vía _GET:
	 * -producto=id_producto
	 * -accion=valorar|descargar
	 * -valoracion=valoracion_usuario (este solo debe indicarse si accion=valorar)
	 * Para realizar este tipo de acciones, el cliente que realiza la petición,
	 * debe estar logeado.
	 * De forma adicional, para descargar un producto, debe ocurir una de las siguientes condiciones:
	 * - El cliente es el autor del producto.
	 * - El cliente ha adquirido el producto previamente.
	 * - El producto es gratuito.
	 * Por último, para poder valorar el producto, el usuario no debe ser el autor del producto.
	 */
	 require_once 'scripts/sesion.php';
	 require_once 'scripts/producto.php';
	 
	 try 
	 {
		 /* comprobamos que el usuario es logeado */
		 if(!Sesion::estaUsuario())
		 {
			 throw new Exception('Usuario no válido');
		 }
		 
		 /* validamos los parámetros de la petición */
		 if(!isset($_GET['accion']) || (($_GET['accion'] != 'valorar') && ($_GET['accion'] != 'descargar')))
		 {
			 throw new Exception('Acción no válida');
		 }
		 $accion = $_GET['accion'];
		 
		 $producto;
		 $id_producto = intval($_GET['producto']);
		 if(!isset($_GET['producto']) || empty($id_producto) || is_null($producto = Producto::buscarPorId(intval($_GET['producto']))))
		 {
			 throw new Exception('Producto no válido');
		 }
		 
		 $valoracion;
		 if($accion == 'valorar')
		 {
			 $valoracion = intval($_GET['valoracion']);
			 if(!isset($_GET['valoracion']) || (($_GET['valoracion'] != '0') && empty($valoracion) ))
			 {
				 throw new Exception('Parámetros de la petición no válidos');
			 }
			 else 
			 {
				 if($_GET['valoracion'] != '0')
				 {
					 $valoracion = round(intval($_GET['valoracion']));
					 if(($valoracion < 0) || ($valoracion > 10))
					 {
						 throw new Exception('Parámetros de la petición no válidos');
					 }
				 }
				 else 
				 {
					 $valoracion = 0;
				 }
			 }
		 }
		 
		 $usuario = Sesion::getUsuario();
		 
		 /* el autor no puede valorar el producto */
		 if($accion == 'valorar')
		 {
			 if($producto->getAutor() == $usuario->getNombre())
			 {
				 throw new Exception('No puedes valorar el producto porque eres el autor');
			 }
		 }
		 else 
		 { 
			 /* producto no puede ser descargado si no está
			  * disponible para el usuario */
			 if(!$producto->estaDisponibleParaUsuario($usuario->getId()))
			 {
				 throw new Exception('No puedes descargar este producto');
			 }
		 }
		 
		 
		 
		 /* realizamos la operación indicada */
		 if($accion == 'valorar')
		 {
			 /* actualizamos la valoración del usuario sobre el producto */
			 $usuario->setValoracionDeProducto($producto, $valoracion);
		 }
		 elseif($accion == 'descargar')
		 {
			 /* establecer la cabecera del documento */
			 $archivo = $producto->getArchivo();
			 $tipo_mime = mime_content_type($archivo);
			 $matches;
			 preg_match('/\\/([^\\/]+)$/', $tipo_mime, $matches);
			 $extension = $matches[1];
			 header('Content-Type: ' . $tipo_mime);
			 header('Content-Disposition: attachment; filename=' . $producto->getNombre() . ".$extension");
			 readfile($archivo); 
		 }
		 
		 echo 'OK';
	 }
	 catch(Exception $e)
	 {
		 echo $e->getMessage();
	 }
?>
