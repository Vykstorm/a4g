<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Script con utilidades para trabajar con los cookies del usuario
	 */
	 
	/**
	 * Esta clase proporciona métodos para acceder y/o modificar los cookies del cliente.  
	 */
	class Cookies 
	{
		/**
		 * @return Devuelve el nombre de usuario guardado en los cookies del cliente, o
		 * null si el nombre de usuario no está en los cookies 
		 */
		public static function getNombreUsuario() 
		{
			if(isset($_COOKIE['nombre_usuario']))
				return $_COOKIE['nombre_usuario'];
		}
		
		/**
		 * Guarda el nombre de usuario en los cookies del cliente.
		 * @param nombre Es el nombre de usuario.
		 */
		 public static function setNombreUsuario($nombre)
		 {
			 setcookie('nombre_usuario', $nombre, time() + self::$expire_time); 
		 }
		 
		 /**
		  * Elimina el nombre de usuario de los cookies del cliente. 
		  */
		 public static function eliminarNombreUsuario() 
		 {
			 setcookie('nombre_usuario','', time() - 3600);
		 }
		 
		 /* Atributos */
		 public static $expire_time; /* tiempo en segundos de expiración de un cookie(7 días) */
	} 
	
	Cookies::$expire_time = 7 * 24 * 60 * 60;
?>
