<?php
	/**
	 * Define las excepciones que serán lanzadas cuando el usuario intenta
	 * acceder a una categoría no existente, o intenta acceder a un catálogo de
	 * productos de forma errónea (la página a la que intenta acceder no existe)
	 */
	 
	class CategoriaNoValidaException extends RuntimeException 
	{
		/**
		 * Constructor.
		 * @param id_categoria Es la id de la categoría que el usuario
		 * quiere ver y que no existe.
		 */
		public function __construct($id_categoria = NULL)
		{
			parent::__construct(!is_null($id_categoria) ? ('La categoría con la id ' . $id_categoria . ' no existe') : 'La categoria no existe');
		}	
		
	}
	
	class PaginaCatalogoProductosNoValida extends RuntimeException
	{
		public function __construct()
		{
			parent::__construct('Página del catálogo de productos no válida');
		}
	}
?>
