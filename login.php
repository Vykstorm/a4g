<?php
	/**
	 * 
	 * @author Víctor Ruiz Gömez
	 * @file \brief Este script permite logear a los usuarios con su cuenta.
	 * 
	 * Por _POST se pasa el nombre y la contraseña. Se devuelve "OK" si existe una
	 * cuenta con el nombre indicado y la contraseña introducida encaja. 
	 * En caso contrario, si no existe ninguna cuenta con ese nombre, o por el contrario, 
	 * existe, pero la contraseña introducida no encaja, devuelve "ERROR"
	 */
	require_once 'scripts/usuario.php'; 
	
	try
	{
		/* comprobar la petición */
		
		if(!isset($_POST['nombre']) || !isset($_POST['passwd']))
		{
			throw new Exception('No se han especificado los valores de los campos');
		}
		$nombre = $_POST['nombre'];
		$passwd = $_POST['passwd'];
		$recordar = isset($_POST['recordar']) ? ($_POST['recordar'] == 'si') : false;
		
		/* ejecutar la petición */


		/* nombre y contraseña válidos ? */
		if(empty($nombre) || empty($passwd))
		{
			throw new Exception('El nombre de usuario o la contraseña son incorrectos');
		}
		
		/* acceder a la base de datos, obtener el usuario cuyo nombre es el
		 * indicado en el campo y verificar la contraseña */
		$usuario;
		if(is_null($usuario = Usuario::buscarPorNombre($nombre)) || !$usuario->passwdCorrecta($passwd)) 
		{
			throw new Exception('El nombre de usuario o contraseña son incorrectos');
		}
		
		/* logear al usuario */
		$usuario->logear($recordar);
		echo 'OK';
	}
	catch(Exception $e) /* login incorrecto */
	{
		echo $e->getMessage(); 
	}
?>
