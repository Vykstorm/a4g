<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Gestiona las peticiones de alta/eliminación/renombre de 
	 * categorías de productos.
	 * 
	 * Parámetros pasados por GET:
	 * accion = alta|eliminar|renombrar
	 * 
	 * Parámetros pasados por POST: 
	 * familia = familia de la categoria (solo en el alta)
	 * nombre = nombre de la categoría (si accion = alta o accion = renombrar)
	 * imagen = Es la imagen de la categoría (si accion = alta)
	 * categoria = id_categoria (si accion = alta o accion = renombrar)
	 * 
	 * En cualquier caso, el usuario que realiza la petición, debe ser un usuario
	 * administrador. Si no lo es, se devolverá un error.
	 */
	require_once 'scripts/sesion.php';
	require_once 'scripts/usuario.php';
	require_once 'scripts/categoria.php';
	try
	{
		/* comprobar que el usuario es administrador */
		$usuario = Sesion::getUsuario();
		if(is_null($usuario) || !$usuario->esAdmin())
		{
			throw new Exception('No eres usuario administrador');
		}
		
		/* comprobar que la acción es válida */
		$acciones = array('eliminar', 'alta', 'renombrar');
		if(!isset($_GET['accion']) || !in_array($_GET['accion'], $acciones))
		{
			throw new Exception('Acción no válida');
		}
		$accion = $_GET['accion'];
		
		/* comprobar los parámetros de la petición */
		/* comprobamos el nombre */
		if((($accion == 'alta') || ($accion == 'renombrar')) && empty($_POST['nombre']))
		{
			throw new Exception('Nombre no válido');
		}

		/* comprobamos la familia y la imágen */
		if($accion == 'alta')
		{
			$familias = array('Modelos 3D', 'Texturas', 'HDRI');
			if(empty($_POST['familia']) || !in_array($_POST['familia'], $familias))
			{
				throw new Exception('Familia no válida');
			}
			
			if(!isset($_FILES['imagen']) || is_array($_FILES['imagen']['error']) || !empty($FILES['imagen']['error']) )
			{
				throw new Exception('Imágen no válida');
			}
		}
		
		/* comprobamos el campo "categoría" */
		$categoria;
		if(($accion == 'renombrar') || ($accion == 'eliminar'))
		{
			if(empty($_POST['categoria']) || (intval($_POST['categoria']) == 0) || is_null($categoria = Categoria::buscarPorId(intval($_POST['categoria']))))
			{
				throw new Exception('Categoría no válida');
			}
		}
		
		/* realizamos la acción */
		switch($accion)
		{
			case 'alta':
			$nombre = $_POST['nombre'];
			$familia = $_POST['familia'];
			$imagen = $_FILES['imagen'];
			$categoria = Categoria::registrar($nombre, $familia, $imagen);
			break;
			case 'renombrar':
			$nombre = $_POST['nombre'];
			$categoria->renombrar($nombre);
			break;
			case 'eliminar':
			$categoria->eliminar();
			break;
		}
		
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
