<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Este script gestiona las siguientes peticiones:
	 * - actualizar valoración de producto de un usuario
	 * - descargar un producto
	 * Parámetros que deben ser indicados vía _GET:
	 * -accion=valorar|descargar|eliminar
	 * -producto=id_producto
	 * Parámetros visa _POST:
	 * -valoracion=valoracion_usuario (este solo debe indicarse si accion=valorar)
	 * Para realizar este tipo de acciones, el cliente que realiza la petición,
	 * debe estar logeado.
	 * De forma adicional, para descargar un producto, debe ocurir una de las siguientes condiciones:
	 * - El cliente es el autor del producto.
	 * - El cliente ha adquirido el producto previamente.
	 * - El producto es gratuito.
	 * Por último, para poder valorar el producto, el usuario no debe ser el autor del producto.
	 * Además, solo los usuarios administradores pueden eliminar un producto.
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
		 if(!isset($_GET['accion']) || (($_GET['accion'] != 'valorar') && ($_GET['accion'] != 'descargar') && ($_GET['accion'] != 'eliminar') ))
		 {
			 throw new Exception('Acción no válida');
		 }
		 $accion = $_GET['accion'];
		 
		 /* comprobamos que el producto existe */
		 $producto;
		 if(!isset($_GET['producto']))
		 {
			 throw new Exception('Producto no válido');
		 }
		 $id_producto = intval($_GET['producto']);
		 if(empty($id_producto) || is_null($producto = Producto::buscarPorId(intval($_GET['producto']))))
		 {
			 throw new Exception('Producto no válido');
		 }
		 
		
		 $valoracion;
		 if($accion == 'valorar')
		 {
			  /* comprobar el parámetro valoración */
			 $valoracion = intval($_POST['valoracion']);
			 if(!isset($_POST['valoracion']) || (($_POST['valoracion'] != '0') && empty($valoracion) ))
			 {
				 throw new Exception('Parámetros de la petición no válidos');
			 }
			 else 
			 {
				 if($_POST['valoracion'] != '0')
				 {
					 $valoracion = round(intval($_POST['valoracion']));
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
		 switch($accion)
		 {
			 case 'valorar':
			 
			 if($producto->getAutor() == $usuario->getNombre())
			 {
				 throw new Exception('No puedes valorar el producto porque eres el autor');
			 }
			 break;
			 
			 case 'descargar':
			 
			 /* producto no puede ser descargado si no está
			  * disponible para el usuario */
			 if(!$producto->estaDisponibleParaUsuario($usuario->getId()))
			 {
				 throw new Exception('No puedes descargar este producto');
			 }
			 break;
			 
			 case 'eliminar':
			
			 if($accion == 'eliminar')
			 {
				 /* comprobamos que el usuario es administador 
				  */
				 if(!$usuario->esAdmin())
				 {
					 throw new Exception('No eres usuario administrador');
				 }
			 }
			 break;
		 }
		 
		 /* realizamos la operación indicada */
		 switch($accion)
		 {
			 case 'valorar':
			 
			 /* actualizamos la valoración del usuario sobre el producto */
			 $usuario->setValoracionDeProducto($producto, $valoracion);		 
			 break;
			 
			 case 'descargar':
			 
			 /* establecer la cabecera del documento */
			 $archivo = $producto->getArchivo();
			 $tipo_mime = mime_content_type($archivo);
			 $matches;
			 preg_match('/\\/([^\\/]+)$/', $tipo_mime, $matches);
			 $extension = $matches[1];
			 header('Content-Type: ' . $tipo_mime);
			 header('Content-Disposition: attachment; filename=' . $producto->getNombre() . ".$extension");
			 readfile($archivo); 	 
			 break;
			 
			 case 'eliminar':
			 
			 $producto->eliminar();
			 break;
		 }
		 
		 echo 'OK';
	 }
	 catch(Exception $e)
	 {
		 echo $e->getMessage();
	 }
?>
