<?php
/**
	@author Víctor Ruiz Gómez
	@file \brief Este script define excepciones que serán lanzadas cuando 
	el usuario accede a una página que es solo para aquellos que están logeados,
	y el no lo esta, o cuando quiere acceder por ejemplo al perfil de otro usuario,
	pero el usuario a introducido el nombre del otro de forma incorrecta (el otro no existe,
	...)
*/


/**
Es lanzada cuando el usuario intenta acceder a una página que es solo para usuarios logeados,
sin logearse
*/
class UsuarioNoValidoException extends RuntimeException 
{
	public function __construct() 
	{
		parent::__construct('El usuario no está logeado');
	}
}

/**
Es lanzada cuando el usuario intenta acceder al perfil de otro usuario, pero este usuario no
existe
*/ 
class UsuarioNoEncontradoException extends RuntimeException 
{
	/**
	 * Constructor.
	 * @param usuario_buscado Es el nombre del usuario buscado que no existe
	 */
	public function __construct($usuario_buscado)
	{
		parent::__construct('El usuario "' . $usuario_buscado . '" no existe');
	}
}
?>
