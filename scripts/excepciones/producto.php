<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Script que define la excepción que será lanzada cuando
	 * el usuario intenta ver un producto que no existe.
	 */
	 
	class ProductoNoValidoException extends RuntimeException 
	{
		public function __construct()
		{
			parent::__construct('Producto no válido');
		}
	}
?>
