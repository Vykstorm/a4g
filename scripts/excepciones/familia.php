<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Define excepción que será lanzada cuando el usuario
	 * intente acceder al catálogo de categorías de productos de una 
	 * familia no existente 
	 */
	 
	class FamiliaNoValidaException extends RuntimeException 
	{
		/**
		 * Constructor.
		 * @param familia Es la familia que el usuario intento buscar, 
		 * pero que no existe
		 */
		public function __construct($familia = NULL)
		{
			parent::__construct(!is_null($familia) ? ('La familia de productos "' . $familia . '" no existe') : 'La familia de productos no existe');
		}

	}
?>
