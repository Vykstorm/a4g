<?php
	require_once 'DBMySQL.php';

	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Contiene la implementación de la clase DBMySQLStatement, devuelta por el método
	 * prepararQuery en DBMySQL.
	 */
	class DBMySQLStatement 
	{
		/**
		 * Constructor.
		 */
		public function __construct($query)
		{
			$this->db = DBMySQL::instancia()->db;
			$this->query = $query;
		}
		
		/**
		 * Ejecuta la query. Los parámetros que se pasen a este método, se sustituirán por los placeholders (:1, :2, ...)
		 * @return Se devuelve el resultado de la consulta (la consulta preparada se pasa como argumento para executeQuery en la 
		 * calse DBMySQL)
		 */
		public function ejecutar() 
		{
			$binds = func_get_args();
			/* sustituimos los placeholders */
			$query = $this->query;
			foreach($binds as $index => $bind)
				$query = str_replace(':' . ($index+1), "'" . mysqli_real_escape_string($this->db, $bind) . "'", $query);
			$res = DBMySQL::instancia()->ejecutarQuery($query);
			return $res; /* devolvemos el resultado de la consulta */
		}
		
		
		/* Atributos */
		private $db;
		private $query;
	}
?>
