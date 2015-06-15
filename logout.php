<?php
	/**
	 * Este script permite recibe peticiones de los clientes para cerrar su sesión
	 * actual
	 */
	 require_once 'scripts/sesion.php';
	 
	 Sesion::eliminar();
	 echo 'OK';
?>
