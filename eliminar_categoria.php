<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Este script gestiona las peticiones de eliminación
	 * de categorías por parte de usuarios administradores.
	 * Parámetros:
	 * categoria=id_categoria Deberá ser la id de la categoría a eliminar.
	 */
	 require_once 'scripts/usuario.php';
	 require_once 'scripts/categoria.php';
	 require_once 'scripts/sesion.php';
	 
	 try 
	 {
		/* comprobamos que el usuario es administrador */
		$usuario = Sesion::getUsuario();
		if(is_null($usuario) || !$usuario->esAdmin())
		{
		throw new Exception('No eres usuario administrador');
		}

		/* comprobamos los parámetros de la petición */
		$categoria;
		if(empty($_POST['categoria']) || (intval($_POST['categoria']) == 0) || is_null($categoria = Categoria::buscarPorId(intval($_POST['categoria']))))
		{
		 throw new Exception('Categoría no válida');
		}

		/* eliminamos la categoría */
		$categoria->eliminar();
		
		echo 'OK';
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}
?>
