<?php
	/**
	 * Script para dar de alta a los usuarios y/o validar campos del formularo de registro. 
	 */
	 
	/** por _POST, se pasan los parámetros del formulario. e.j. nombre=victor
	* por _GET, nos indican la acción a realizar accion=alta, accion=validar.
	* Si la acción es validar, también se deberá pasar por _GET, campo=?. Indicando el campo que 
	* se desea validar.
	* 
	* Si la acción es alta, por _POST deben pasarse todos los campos del formulario. En cambio si estamos
	* válidando un campo en concreto, solo hacer falta pasar dicho campo.
	* 
	* Si accion=alta y los campos son válidos, el usuario se registra y se logea automáticamente en la nueva
	* cuenta. 
	* 
	* Si la acción no se indica o es inválida, se devuelve un mensaje de error.
	**/
	require_once 'scripts/usuario.php';
	  
	/* funciones auxiliares */
	function validar_nombre($nombre) 
	{
		/* comprobar que satisface la política de nombres de la web */
		if(preg_match('/[^a-zA-Z0-9_]+/', $nombre))
			throw new Exception('El nombre solo puede tener letras, dígitos o barras bajas');
		if(strlen($nombre) < 5)
			throw new Exception('El nombre tiene pocos caracteres');
		if(strlen($nombre) > 26)
			throw new Exception('El nombre no puede contener tantos caracteres');
		
		/* verificar que no existe ningún usuario con ese nombre */
		if(!is_null(Usuario::buscarPorNombre($nombre)))
		{
			throw new Exception('El nombre de usuario ya existe');
		}
	}

	function validar_passwd($passwd)
	{
		/* comprobar que la passwd satisface la política de contraseñas de la web */
		if(strlen($passwd) < 5)
			throw new Exception('La contraseña tiene pocos caracteres');
		if(strlen($passwd) > 40)
			throw new Exception('La contraseña no puede contener tantos caracteres');
		if(preg_match('/[^a-zA-Z0-9_]+/', $passwd))
			throw new Exception('La contraseña solo puede tener letras, dígitos o barras bajas');
		if(!preg_match('/[0-9]+/', $passwd) || !preg_match('/[a-zA-Z]+/',$passwd))
			throw new Exception('La contraseña debe contener al menos 1 digito y 1 letra');
	}

	function validar_campo($campo, $valor)
	{
	  if($campo == 'nombre')
	  {
		  validar_nombre($valor);
	  }
	  else if($campo == 'passwd')
	  {
		  validar_passwd($valor);
	  }
	}

	function validar_campos($nombre, $passwd)
	{
	  validar_nombre($nombre);
	  validar_passwd($passwd);
	}


	try 
	{
	  /* validar la petición */
	  if(!isset($_GET['accion']) || (($_GET['accion'] != 'validar') && ($_GET['accion'] != 'alta')))
	  {
		  // acción no válida.
		  throw new Exception('Acción no valida');
	  }
	  
	  $accion = $_GET['accion'];
	  
	  if($accion == 'validar') 
	  {
		  if((!isset($_GET['campo'])) || (($_GET['campo'] != 'nombre') && ($_GET['campo'] != 'passwd')))
		  {
			  throw new Exception('Campo no válido');
		  }
		  
		  if(!isset($_POST[$_GET['campo']]))
		  {
			  throw new Exception('Valor del campo no especificado');
		  }	 
	  }
	  else  
	  {
		  if(!isset($_POST['nombre']) || !isset($_POST['passwd']))
		  {
			  throw new Exception('No se han especificado todos los campos');
		  }
	  }
	 
	  
	  /* ejecutar la petición */ 
	  
	  if($accion == 'validar') /* validar un campo del formulario */
	  {
		  $campo = $_GET['campo'];
		  validar_campo($campo, $_POST[$campo]);
	  }
	  else /* validar todos los campos, registrar al usuario y luego logearlo */ 
	  {
		  $nombre = $_POST['nombre'];
		  $passwd = $_POST['passwd'];
		  validar_campos($nombre, $passwd);

		  /* registrar la nueva cuenta & logear al usuario en esta */
		  Usuario::registrar($nombre, $passwd);
	  }
	  
	  echo 'OK';
	}
	catch(Exception $e)
	{
	  /* algo fue mal, se falló en la validación de algún campo, o la acción es inválida */
	  echo $e->getMessage();
	}
?>
