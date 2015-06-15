<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Este script recibe las peticiones http para añadir/eliminar productos
	 * del carrito de la compra.
	 * 
	 * Deben indicarse varios parámetros vía POST:
	 * accion=meterProducto|sacarProducto 
	 * y
	 * producto=id_producto 
	 */
	 
	 require_once 'scripts/sesion.php';
	 require_once 'scripts/carrito.php';
	 require_once 'scripts/producto.php';
	 
	 try 
	 { 
		 /* comprobar que el usuario no es anónimo */
		 if(!Sesion::estaUsuario())
		 {
			 throw new Exception('Usuario no válido');
		 }
		 
		 /* validar los parámetros de entrada */
		 if(empty($_POST['accion']) || (($_POST['accion'] != 'meterProducto') && ($_POST['accion'] != 'sacarProducto')))
		 {
			 throw new Exception('Accion no válida');
		 }
		 
		 $accion = $_POST['accion'];
		 $id;
		 if(empty($_POST['producto']) || (($id = intval($_POST['producto'])) == 0))
		 {
			 throw new Exception('Producto no válido');
		 }
		 
		 /* si la acción es meter producto, debemos comprobar que el producto existe y que
		  * no esta disponible actualmente para el usuario */
		 if($accion == 'meterProducto')
		 {
			 $producto;
			 if(is_null($producto = Producto::buscarPorId($id)))
			 {
				 throw new Exception('Producto no válido');
			 }
			 if($producto->estaDisponibleParaUsuario(Sesion::getIdUsuario()))
			 {
				 throw new Exception('No puedes comprar este producto porque ya lo has hecho previamente, lo has publicado o es gratuito');
			 }
		 }
		 
		 /* obtenemos el carrito y realizamos la operación indicada */
		 $carrito = Sesion::getCarrito();
		 
		 if($accion == 'meterProducto')
		 {
			 $carrito->meterProducto($id);
		 }
		 elseif($accion == 'sacarProducto')
		 {
			 $carrito->sacarProducto($id);
		 }
		 
		 /* guardamos el carrito */
		 Sesion::setCarrito($carrito);
		 
		 echo 'OK';
	 }
	 catch(Exception $e) /* se produjo un error */
	 {
		 echo $e->getMessage();
	 }
?>
