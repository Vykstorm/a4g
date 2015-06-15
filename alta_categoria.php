<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Este script gestiona las peticiones de alta de nuevas 
	 * categorías
	 */
	 
	 require_once 'scripts/sesion.php';
	 require_once 'scripts/usuario.php';
	 require_once 'scripts/categoria.php';
	 
	 try 
	 {
		 /* comprobamos que el usuario es administrador */
		 $usuario = Sesion::getUsuario();
		 if(is_null($usuario) || !$usuario->esAdmin())
		 {
			throw new Exception('No eres usuario administrador');
		 }
		 
		 /* comprobamos los parámetros de la petición */
		 $familias = array('Modelos 3D', 'Texturas', 'HDRI');
		 if(empty($_POST['nombre']) || empty($_POST['familia']) || !in_array($_POST['familia'], $familias))
		 {
			 throw new Exception('Parámetros de la petición no válidos');
		 }
		 $nombre = $_POST['nombre'];
		 $familia = $_POST['familia'];
		 
		 if(!isset($_FILES['imagen']) || is_array($_FILES['imagen']['error']) || !empty($FILES['imagen']['error']) )
		 {
			 throw new Exception('Imágen no válida');
		 }
		 
		 $imagen = $_FILES['imagen'];
		 
		 
		 /* registramos la categoría */
		 $categoria = Categoria::registrar($nombre, $familia, $imagen);
		 echo 'OK';
	 }
	 catch(MySQLEntradaDuplicadaException $e)
	 {
		 echo 'Ya existe una categoría con ese nombre';
	 }
	 catch(Exception $e)
	 {
		 echo $e->getMessage();
	 }
?>
