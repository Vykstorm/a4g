<?php
	/**
	 * @author Víctor ruiz gómez
	 * @file \brief Este script permite encapsular el manejo de excepciones
	 * de mysql
	 */
	 
	/**
	 * Es lanzada cuando se produce una excepción sql 
	 */
	class MySQLException extends RuntimeException  
	{
		public function __construct($mensaje, $sql_exception)
		{
			parent::__construct($mensaje, 0, $sql_exception);
		}
	}
	
	/**
	 * Es lanzada cuando se produce una excepción sql causada por una sintaxis
	 * erronea de una consulta
	 */
	class MySQLSintaxisException extends MySQLException
	{
		public function __construct($sql_exception)
		{
			parent::__construct('Error de sintaxis MySQL', $sql_exception);
		}
	}
	
	/**
	 * Es lanzada cuando se incumple una restricción (unique, check o de clave
	 * primaria) al hacer una o varias insercciones.
	 */
	class MySQLEntradaDuplicadaException extends MySQLException 
	{
		public function __construct($sql_exception)
		{
			parent::__construct('Entrada duplicada (se incumple una restriccion unique o primary key) en MySQL', $sql_exception);
		}
	}
	
	/**
	 * Esta excepción es lanzada cuando se fallo al realizar la conexión con la 
	 * base de datos
	 */
	class MySQLConexionException extends MySQLException	
	{
		public function __construct($sql_exception)
		{
  			 parent::__construct('Error al conectar con la base de datos MySQL', $sql_exception);   
		}
	}
	
	class MySQLEsquemaNoEncontradoException extends MySQLException
	{
		public function __construct($sql_exception)
		{
			parent::__construct('Esquema de la base de datos MySQL no encontrado', $sql_exception);
		}
	}
?>
