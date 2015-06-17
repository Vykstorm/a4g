<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief
	 * Este script define una serie de clases para obtener información de los productos
	 * publicados en la web.
	 */
	
	require_once 'DBMySQLQueryManager.php';
	require_once 'categoria.php';
	require_once 'almacen.php';
	
	/**
	* Las instancias de esta clase representan productos publicados en la web 
	*/
	class Producto 
	{
		/**
		 * @return Devuelve el producto cuya id es la especificada o NULL
		 * si no hay ningún producto con esa id
		 */
		public static function buscarPorId($id)
		{
			return DBMySQLQueryManager::buscarProductoPorId($id);
		}
		
		/**
		 * Crea un nuevo producto
		  * @param nombre Es el nombre del nuevo producto.
		  * @param descripcion Es su descripcion
		  * @param precio Es el precio
		  * @param id_autor La id del usuario que publica el producto
		  * @param categoria La categoría del producto.
		  * @param datos Es la entrada de la tabla _FILES que contiene información relativa al fichero asociado
		  * al nuevo producto
		  * @param imagenes Es la entrada de la tabla _FILES que contiene información relativa de las imágenes asociadas
		  * al nuevo producto.
		  * @return Devuelve el producto creado. 
		  */
		public static function registrar($nombre, $descripcion, $precio, $id_autor, $categoria, $datos, $imagenes)
		{
			$producto = DBMySQLQueryManager::registrarProducto($nombre, $descripcion, $precio, $id_autor, $categoria, $datos, $imagenes);
			try 
			{
				Almacen::registrarProducto($producto, $datos, $imagenes);
			}
			catch(Exception $e)
			{
				/* si hay un error al guardar el producto en el disco, queremos que los cambios realizados en la 
				 * bd no se produczcan */
				
				DBMySQL::instancia()->rollback();
				throw $e;
			}
			/* hacer que los cambios en la bd sean permanentes */
			DBMySQL::instancia()->commit();
			
			return $producto;
		}
		
		/**
		 * @return Devuelve una lista de productos destacados que pertenecen a la familia especificada
		 * como parámetro.
		 */
		public static function getDestacados($familia)
		{
			return DBMySQLQueryManager::getProductosDestacados($familia, ($familia != 'HDRI') ? 3 : 1);
		}
		 
		/**
		* Constructor 
		*/
		public function __construct($id, $nombre, $autor, $detalles = NULL) 
		{
		 $this->id = $id;
		 $this->nombre = $nombre;
		 $this->autor = $autor;
		 $this->detalles = $detalles;
		}
		
		/* Acciones */
		/**
		 * Elimina este producto.
		 */
		public function eliminar()
		{
			DBMySQLQueryManager::eliminarProducto($this->getId());
		}
		
		/**
		 * Cambiar la categoría de este producto
		 */
		public function cambiarCategoria($categoria)
		{
			DBMySQLQueryManager::cambiarCategoriaProducto($this->getId(), $categoria->getId());
			$this->detalles = NULL;
		}

		/* Consultores */
		/**
		* @return Devuelve el nombre del producto 
		*/
		public function getNombre() 
		{
			return $this->nombre;
		}

		/**
		* @return Devuelve la id del producto 
		*/
		public function getId()
		{
			return $this->id;
		}

		/**
		* @return Devuelve el nombre del autor del producto 
		*/
		public function getAutor()
		{
			return $this->autor; 
		}

		/**
		* @return Devuelve los detalles del producto 
		*/
		public function getDetalles() 
		{
			if(is_null($this->detalles))
			{
				$this->detalles = DBMySQLQueryManager::getDetallesProducto($this->id);
			} 
			return $this->detalles;
		}
		
		/**
		 * @return Devuelve un array con los comentarios realizados de este producto por parte de 
		 * los usuarios. Están ordenados en función de la fecha de publicación de los mismos (del más
		 * reciente al menos reciente 
		 */
		public function getComentarios()
		{
			if(is_null($this->comentarios))
			{
				$this->comentarios = DBMySQLQueryManager::getComentariosProducto($this->id);
			}
			return $this->comentarios;
		}
		
		/**
		 * @return Devuelve la url de una imágen asociada a este producto.
		 */
		public function getImagen()
		{
			return Almacen::getImagenDeProdcuto($this->getId());
		}
		
		/**
		 * @return Devuelve un array con las urls de todas las imágenes asociadas a este
		 * producto
		 */
		public function getImagenes()
		{
			return Almacen::getImagenesDeProducto($this->getId());
		}
		
		/**
		 * @return Devuelve la ruta del archivo que contiene los datos del producto.
		 */
		public function getArchivo()
		{
			return Almacen::getDatosDeProducto($this->getId());
		}
		
		/**
		  * Comprueba si este producto esta disponible para el usuario; Es decir, si se cumple que es el autor del mismo,
		  * el producto es gratuito o ha sido comprado por el usuario previamente.
		  * (En estas condiciones el usuario puede descargar el producto)
		  * @return Devuelve true si el producto está disponible para el usuario. false si el usuario no existe, el producto
		  * no existe o el producto no está disponible para el usuario
		  * @param usuario Es la id del usuario
		 */
		public function estaDisponibleParaUsuario($id_usuario)
		{
			return DBMySQLQueryManager::esProductoDisponibleParaUsuario($id_usuario, $this->getId());
		}


		/* Atributos */
		private $id;
		private $nombre;
		private $autor;
		private $detalles;
		private $comentarios = NULL;
	}

	/**
	* Esta clase representa los detalles de un producto (descripción, precio, ...)
	*/
	class DetallesProducto
	{		
		 /* Constructor */
		 /**
		  * @note La fecha de adquisición se especificará cuando queremos obtener un listado de productos comprados por el usuario.
		  * En tal caso, getFechaAdquisicion() devolverá un valor no nulo que indicará la fecha en la cual el usuario x adquirió el 
		  * producto 
		  */
		 public function __construct($descripcion, $precio, $fecha_publicacion, $valoracion, $categoria, $fecha_adquisicion = NULL)
		 {
			 $this->descripcion = $descripcion;
			 $this->precio = $precio;
			 $this->fecha_publicacion = $fecha_publicacion;
			 $this->valoracion = $valoracion;
			 $this->categoria = $categoria;
			 $this->fecha_adquisicion = $fecha_adquisicion;
		 }
		 
		 /* Consultores */
		 /**
		  * @return Devuelve la descripción del producto 
		  */
		 public function getDescripcion()
		 {
			 return $this->descripcion;
		 }
		 
		 /**
		  * @return Devuelve el precio del producto 
		  */
		 public function getPrecio()
		 {
			 return $this->precio;
		 }
		 
		 /**
		  * @return Devuelve la fecha de publicación del producto
		  */
		 public function getFechaPublicacion()
		 {
			 return $this->fecha_publicacion;
		 }
		 
		 /**
		  * @return Devuelve la valoración del producto.
		  */
		 public function getValoracion()
		 {
			 return $this->valoracion;
		 }
		 
		 /**
		  * @return Devuelve la categoría del producto 
		  */
		 public function getCategoria() 
		 {
			return $this->categoria; 
		 }
		 
		 /**
		  * @return Devuelve la fecha de adquisición de este producto por el usuario
		  */
		 public function getFechaAdquisicion()
		 {
			 return $this->fecha_adquisicion;
		 }

		/* Atributos */
		private $descripcion, $precio, $fecha_publicacion, $valoracion, $categoria, $fecha_adquisicion;
	}
?>
