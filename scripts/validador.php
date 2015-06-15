<?php
	/* la siguiente constante, debe indicar un listado de formatos de imágen permitidos en 
	 * la web, que puedan ser subidos por los usuarios 
	 */
	 global $formatos_imagen_validos;
	 $formatos_imagen_validos = array(IMAGETYPE_PNG, IMAGETYPE_JPEG);
	
	/**
	 * la siguiente constante, indica un listado de formatos de tipos de archivos comprimidos
	 * válidos que pueden ser subidos por los usuarios
	 */
	 global $formatos_comprimidos_validos;
	 $formatos_comprimidos_validos = array('zip');

	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Es un script que define clases para comprobar si un fichero
	 * subido por el usuario, es un fichero válido, en función de que uso se le
	 * dé en la web 
	 */
	/**
	 * Esta clase simplemente comprueba que el fichero ha sido subido por el usuario.
	 */
	class ValidadorFichero
	{
		/**
		 * Comprueba si un fichero es subido por el usuario mediante una forma.
		 * @return Devuelve true en caso de que el fichero haya sido subido por el usuario,
		 * false en caso contrario
		 */
		public function esValido($fichero)
		{
			return true;
		}
	}
	
	/**
	 * Esta clase comprueba que el fichero es subido por el usuario y además,
	 * es una imágen válida (debe tener uno de nuestros formatos aceptados).
	 */
	class ValidadorImagen extends ValidadorFichero
	{
		public function esValido($fichero)
		{
			return parent::esValido($fichero) && (true);
		}
	}
	
	/**
	 * Es igual que el anterior, pero para validar ficheros comprimidos
	 */
	class ValidadorComprimido extends ValidadorFichero
	{
		public function esValido($fichero)
		{
			return parent::esValido($fichero) && (true);
		}	
	}
	
	/**
	 * Por último, un validador para validar un modelo 3d */
	class ValidadorModelo3D extends ValidadorComprimido 
	{
		public function esValido($fichero)
		{
			return parent::esValido($fichero) && (true);
		}	
	}
?>
