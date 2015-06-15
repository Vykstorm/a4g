<?php
	/**
	 * 
	 * @author Victor Ruiz Gómez.
	 * @file \brief Este script proporciona métodos de generación y comparación de hash (sha256)
	 */
	
	/* comprobar si el algoritmo sha256 esta disponible en el sistema */
	// assert(in_array('sha256', hash_algos()), 'El algoritmo hash sha256 no esta disponible en el sistema');
	
	 
	/**
	* Crea un hash con el algoritmo sha256 para una contraseña. 
	* @Devuelve una cadena de 64 caracteres (256 bytes codificados en hexadecimal)
	*/
	function crear_hash($passwd)
	{
		return hash('sha256', $passwd);
	}
	 
	/**
	* Compara dos hash para comprobar si son iguales (deben haber sido generados por la función crear_hash).
	* @param known_password es el hash de la contraseña real.
	* @param user_password es el hash generado de la contraseña de la que se quiere verificar que coincide con la contraseña
	* real. 
	*/
	function comparar_hash($known_password, $user_password)
	{
		if(function_exists('hash_equals')) /* a partir de la versión 5.6, esta función está disponible */
			return hash_equals($known_password, $user_password); /* esta comparación de hashes nos permite protegernos de
			ataques de temporalización */
		return ($known_password == $user_password);
	}
?>
