<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Este scrpt contiene la definición de la clase Sesión.
	 */
	 require_once 'usuario.php';
	 require_once 'DBMySQLQueryManager.php';
	 require_once 'carrito.php';
	  	  
	 
	 /**
	  * Encapsula el tratamiento de las sesiones php
	  */
	  class Sesion
	  {
		  /**
		   * Crea la sesión del cliente 
		   */
		  public static function crear()
		  {
			  if(!self::$sesion_creada)
			  {
				  session_start(); /* crear la sesión si no esta creada todavía */
				  /* eliminar las variables establecidas en la sesión, si esta a expirado */
				  if(!isset($_SESSION['timestamp']) || ((time() - $_SESSION['timestamp']) >= self::$expire_time))
					session_unset();	
				  $_SESSION['timestamp'] = time();
				  
				  self::$sesion_creada = true;
			  }
		  }
		  
		  /**
		   * Elimina la sesión del cliente. 
		   */
		  public static function eliminar() 
		  {
			  session_start();
			  session_unset();
			  if (ini_get("session.use_cookies")) /* esto es para eliminar la sesión del cliente de sus cookies */
			  {
				  $params = session_get_cookie_params();
				  setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
			  }
			  session_destroy();
		  }
		  
		  /* Constultores */
		  /**
		   * @return Devuelve el usuario cuya id está guardada en la sesión actual,
		   * o null, si la sesión esta vacía (usuario no logeado), es decir,
		   * si hay ninguna id de usuario establecida en la sesión actual. 
		   *  */
		  public static function getUsuario() 
		  {
			  if(is_null(self::$usuario))
			  {
				  $id;
				  if(is_null($id = self::getIdUsuario()))
				  {
					  return NULL;
				  }
				  self::$usuario = DBMySQLQueryManager::buscarUsuarioPorId($id);
			  }
			  return self::$usuario;
		  }
		  
		  /**
		   * @return Devuelve la id del usuario establecida en la sesión, o NULL
		   * si no hay ninguna id de usuario establecida. 
		   */
		  public static function getIdUsuario()
		  {
			  self::crear();
			  if(!isset($_SESSION['userid']))
				return NULL;
			  $id = $_SESSION['userid'];
			  return $id;
		  }
		  
		  /**
		   * @return Devuelve un valor booleano indicando si el usuario está
		   * guardado en la sesión. 
		   * @note Si devuelve false, el método getUsuario(), devuelve
		   * NULL
		   */
		  public static function estaUsuario() 
		  {
			  self::crear();
			  return isset($_SESSION['userid']);
		  }
		  
		  /**
		   * @return Devuelve el carrito de la compra actual del usuario.
		   * Guardado en la sesión. Se devuelve un carrito vacío si todavía no
		   * hay ningún carrito guardado en la sesión.
		   */
		  public static function getCarrito()
		  {
			  self::crear();
			  if(!isset($_SESSION['carrito']))
			  {
				  return new Carrito();
			  } 
			  return unserialize($_SESSION['carrito']);
		  }
		   
		  /* modificadores */
		  
		  /**
		   * Guarda el carrito en la sesión actual del usuario
		   */
		  public static function setCarrito($carrito)
		  {
			  self::crear();
			  $_SESSION['carrito'] = serialize($carrito);
		  }
		  
		  /**
		   * Guarda al usuario en la sesión actual.
		   * @note Este método invoca previamente Sesion::crear()
		   */
		   public static function setUsuario($usuario)
		   {
			   self::crear();
			   $_SESSION['userid'] = $usuario->getId(); /* guardamos la id de usuario en la sesión */
		   }
		   
		   
		   /* Atributos */
		   public static $expire_time; /* tiempo de expiración de la sesión del usuario en segundos (15 mins) */
		   private static $sesion_creada = false; /* indica si se inicializó la sesión. (para evitar inicializarla dos veces) */
		   private static $usuario = NULL;
	  }
	  
	  Sesion::$expire_time = 60 * 15;
?>
