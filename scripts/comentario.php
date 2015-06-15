<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Es un script que define la clase que representa un comentario de un 
	 * usuario sobre un producto.
	 */
	 
	 
	class Comentario 
	{
		/* Constructor */
		public function __construct($id, $texto, $fecha_post, $autor)
		{
			$this->id = $id;
			$this->autor = $autor;
			$this->texto = $texto;
			$this->fecha_post = $fecha_post;
		}
		
		/* Consultores */
		/**
		 * @return Devuelve el usuario que realizó este comentario.
		 */
		public function getAutor() 
		{
			return $this->autor;	
		}
		
		/**
		 * @return Devuelve el texto del comentario 
		 */
		public function getTexto()
		{
			return $this->texto;
		}
		
		/**
		 * @return Devuelve la fecha en la que se realizó este comentario 
		 */
		public function getFechaPost() 
		{
			return $this->fecha_post;
		}
		
		/**
		 * @return Devuelve la id de este comentario
		 */
		public function getId()
		{
			return $this->id;
		}
		
		
		private $autor, $texto, $fecha_post, $id;
	}
?>
