<?php
	/**
	 * 
	 * @author Víctor Ruiz Gómez.
	 * @file \brief Este archivo contiene la definición de la clase Usuario.
	 */

	 /* importaciones */
	 require_once 'hash.php';
	 require_once 'cookies.php';
	 require_once 'DBMySQLQueryManager.php';
	 require_once 'sesion.php';
	 require_once 'almacen.php';
	 require_once 'carrito.php';
	  	  		
	 
	 class Usuario 
	 {	  
		  /**
		   * Registra un nuevo usuario permanentemente en la web. 
		   * @param nombre El nombre del nuevo usuario (no debe coincidir con el nombre de otro usuario ya existente)
		   * @param passwd Es la contraseña (no es un hash). 
		   * @note El nuevo usuario tendrá una id de usuario aleatoria, pero única. 
		   * @return Devuelve el usuario creado.
		   */
		  public static function registrar($nombre, $passwd)
		  {
			  /* hacer una query, insert nuevo usuario en bd, la id la autoincrementamos */
			  $usuario = DBMySQLQueryManager::registrarUsuario($nombre, crear_hash($passwd));
			  
			  /* reservamos un directorio para guardar los datos del usuario */
			  Almacen::registrarUsuario($usuario);
			  
			  /* logeamos usuario con la nueva cuenta */
			  $usuario->logear(true);
			  
			  return $usuario;
		  }
		  
		  /**
		   * Busca a un usuario por su id.
		   * @return Devuelve una instancia de esta clase si se ha encontrado a un usuario con la id especificada,
		   * o null si no existe. 
		   */
		  public static function buscarPorId($id)
		  {
			  return DBMySQLQueryManager::buscarUsuarioPorId($id);
		  }
		  
		  /**
		   * Buscar a un usuario por su nombre.
		   * @return Devuelve una instancia de esta clase si se ha encontrado a un usuario con la id especificada, 
		   * o null si no hay ningún usuario con ese nombre
		   */
		  public static function buscarPorNombre($nombre)
		  {
			 return DBMySQLQueryManager::buscarUsuarioPorNombre($nombre); 
		  }
		  
		  /* Constructores */
		  public function __construct($id, $nombre, $passwd, $admin, $fecha_registro)
		  {
			  $this->id = $id;
			  $this->nombre = $nombre;
			  $this->passwd = $passwd;
			  $this->admin = $admin;
			  $this->fecha_registro = $fecha_registro;
		  }
		  
		  
		  /* Consultores */
		  /**
		   * @return Devuelve la ID de este usuario 
		   */
		  public function getId() 
		  {
			  return $this->id;
		  }
		  
		  /**
		   * @return Devuelve el nombre de este usuario 
		   */
		  public function getNombre() 
		  {
			  return $this->nombre;
		  }
		  
		  /**
		   * @return Devuelve el hash asociado a la contraseña
		   * de este usuario
		   */
		  public function getPasswd() 
		  {
			  return $this->passwd;
		  }
		  
		  /**
		   * @return Devuelve un valor booleano indicando si este usuario
		   * es administrador o no.
		   */
		  public function esAdmin()
		  {
			  return $this->admin;
		  }
		  
		  /**
		   * @return Devuelve la fecha de registro de este usuario
		   */
		  public function getFechaRegistro() 
		  {
			  return $this->fecha_registro;
		  }  
		  
		  /**
		   * @return Devuelve los productos publicados por el usuario
		   */
		  public function getProductosPublicados() 
		  {
			  if(is_null($this->productos_publicados))
			  {
				  $this->productos_publicados = DBMySQLQueryManager::getProductosPublicadosPorUsuario($this);
			  }
			  return $this->productos_publicados;
		  }
		  
		  /**
		   * @return Devuelve los productos adquiridos por el usuario
		   */
		   public function getProductosAdquiridos() 
		   {
			   if(is_null($this->productos_adquiridos))
			   {
				   $this->productos_adquiridos = DBMySQLQueryManager::getProductosAdquiridosPorUsuario($this);
			   }
			   return $this->productos_adquiridos;
		   }
		   
		   /**
		    * @return Devuelve la valoración que tiene este usuario de un producto.
		    * Si el usuario todavía no ha valorado el producto, devuelve 0.
		    * @param producto Es el producto.
		    */
		   public function getValoracionDeProducto($producto) 
		   {
			   $valoracion;
			   if(is_null($valoracion = DBMySQLQueryManager::getValoracionProductoDeUsuario($this->id, $producto->getId())))
			   {
				   return 0;
			   }  
			   return $valoracion;
		   }
		   
		   /**
		    * Actualiza la valoración de este usuario sobre un producto. 
		    * @param producto Es el producto
		    * @param valoracion Es la nueva valoracion
		    */
		   public function setValoracionDeProducto($producto, $valoracion)
		   {
			   DBMySQLQueryManager::actualizarValoracionProductoDeUsuario($this->id, $producto->getId(), $valoracion); 
		   }
		   
		  /**
		   * @return Devuelve el número de publicaciones del usuario
		   * @note Este método es igual que hacer sizeof(getProductosPublicados())
		   * 
		   */
		  public function getNumPublicaciones() 
		  {
			  return sizeof($this->getProductosPublicados());
		  }
		  
		  /**
		   * Comprueba si la contraseña es la misma que la del usuario.
		   * @return Devuelve true si la contraseña pasada como parámetro
		   * es la misma que la del usuario, falso en caso contrario.
		   * @param passwd Es la contraseña que se quiere checkear (no es un hash)
		   */
		  public function passwdCorrecta($passwd)
		  {
			  return comparar_hash($this->getPasswd(), crear_hash($passwd));
		  }
		  
		  /**
		   * @return Devuelve la url de la imágen asociada a este usuario. 
		   */
		  public function getFotoPerfil() 
		  {
			  $foto;
			  if(!($foto = Almacen::getFotoPerfilDeUsuario($this->getId())))
			  {
				  /* usamos la foto de perfil por defecto */
				  return 'imagenes/perfil.png';
			  }
			  return $foto;
		  }
		  
		  /* Acciones */
		  /**
		   * Logea a este usuario; Lo almacena en la sesión y en los cookies del
		   * cliente (si el parámetro recordar está establecido a true)
		   * @param recordar Es un valor booleano indicando si se guardará el nombre de 
		   * usuario en los cookies del cliente. Si esta establecido a false y el nombre de usuario
		   * está guardado en los cookies del cliente, este se eliminará.
		   */
		  public function logear($recordar)
		  {
			  /* guardar el nombre en los cookies del cliente */
			  if($recordar)
			  {
				  Cookies::setNombreUsuario($this->getNombre());
			  }
			  else 
			  {
				  Cookies::eliminarNombreUsuario();
			  }
			  
			  /* logeamos automáticamente al usuario con la nueva cuenta */
			  Sesion::setUsuario($this);
		  }
		  
		  /**
		   * El usuario compra todos los productos que están dentro del carrito de la compra. 
		   * @throws Lanza una excepción si el carrito de la compra esta vacío.
		   */
		  public function checkout()
		  {
			  $carrito = Sesion::getCarrito();
			  if($carrito->getNumProductos() == 0)
			  {
				  throw new Exception('No hay ningún producto en el carrito');
			  }
			  DBMySQLQueryManager::registrarCompraProductos($this->getId(), $carrito->items); /* actualizo la BD */
			
			  /* vacío el carrito */
			  $carrito->vaciar();
			  
			  /* almacenamos el carrito vacío en la sesión */
			  Sesion::setCarrito($carrito);
			  
		  }
		  
		  /**
		   * El usuario envía comenta un producto de la web. 
		   * @param producto Es el producto que comenta este usuario.
		   * @param comentario Es el texto del comentario realizado por este usuario.
		   */
		  public function postear($producto, $comentario)
		  {
			  DBMySQLQueryManager::registrarPost($this->id, $producto->getId(), $comentario);
		  }
		  
		  /* atributos */
		  private $id, $nombre, $passwd; /* la passwd está encriptada */
		  private $admin, $fecha_registro;
		  private $productos_publicados = NULL;
		  private $productos_adquiridos = NULL;
	 }
?>
