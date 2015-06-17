<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Este script contiene la implementación de la clase DBMySQL
	 * que encapsula el acceso a la base de datos MySQL usando el OO.
	 */
	 
	 /* esta constante debe ser definda a "test" en la fase de desarrollo (usará la base de datos
	 de test). En caso contrario, definirla a "prod" */
	 define('DBMySQL_type', 'test'); 
	 // define('DBMySQL_type', 'prod');
	 
	 mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR); /* esto es para que nos lanze excepciones mysqli_sql_exception, si 
	 se produce algún error en la conexión */
	 
	 require_once 'DBMySQLStatement.php';
	 require_once 'excepciones/mysql.php';
	 
	 class DBMySQL
	 {
		 /**
		  * Constructor. 
		  * @param user Es el usuario con el que nos queremos conectar.
		  * @param passwd Es la contraseña
		  * @param host Es el host
		  * @param dbname Es el nombre de la base de datos.
		  * Se construye una instancia de esta clase, se conecta al host y 
		  * se selecciona la base de datos correspondiente. 
		  */
		  private function __construct($user, $passwd, $host, $dbname)
		  {
			  try
			  {
				  $this->db = mysqli_connect($host, $user, $passwd); /* nos conectamos al host */
			  }
			  catch(mysqli_sql_exception $e)
			  {
				  throw new MySQLConexionException($e);
			  }
			  
			  try
			  {
				  mysqli_select_db($this->db, $dbname); /* seleccionamos la BD */
				  mysqli_autocommit($this->db, false); /* auto commit desactivado */
			  }
			  catch(mysqli_sql_exception $e)
			  {
				  throw new MySQLEsquemaNoEncontradoException($e);
			  }
		  }
		  
		  public function __destruct()
		  {
			  if($this->db)
			  {
				  mysqli_close($this->db); /* cerramos la conexión */
			  }
		  }
		  
		  /* Consultores */
		  /**
		   * @return Devuelve una instancia de esta clase. (para implementar el 
		   * patrón singleton)
		  */
		  public function instancia()
		  {
			  if(!isset(self::$singleton))
				self::$singleton = new DBMySQL(self::$user, self::$passwd, self::$host, self::$dbname);
			
			  return self::$singleton;
		  }
		  
		  /**
		   * Ejecuta una query
		   * @return D el resultado de la query. (Este método debe usarse si 
		   * en la query no hay valores introducidos por el usuario. En ese caso, use la
		   * función prepararQuery, y luego ejecutarla), si es una query del tipo SELECT, DESCRIBE o SHOW,
		   * o TRUE en cualquier otro caso (INSERT, UPDATE, ...). En caso de que la query falle, se lanzará la 
		   * excepción mysqli.
		   */
		  public function ejecutarQuery($query) 
		  {
			  /* es una multi query ? */
			  try
			  {
				  return mysqli_query($this->db, $query);
			  }
			  catch(mysqli_sql_exception $e)
			  {
				  /* comprobamos que error sql se ha producido.
				   * Lanzamos una excepción tipo MySQLException en función del 
				   * tipo de error
				   */
				  switch($this->db->errno)
				  {
					  case 1064:
					  throw new MySQLSintaxisException($e);
					  break;
					  case 1062:
					  throw new MySQLEntradaDuplicadaException($e);
					  break;
					  default:
					  throw new MySQLException('Error MySQL', $e);
				  }
			  }
		  }
		  
		  /**
		   * Preparar una query para su posterior uso.
		   * Pueden usarse huecos "placeholders" en la consulta (:1, :2, :3, ...). Estos huecos se sustituirán por los valores
		   * introducidos por el usuario al ejecutar la consulta.
		   * @return Devuelve la query preparada. 
		   */
		  public function prepararQuery($query)
		  {
			  return new DBMySQLStatement($query);
		  }
		  
		  /**
		   * @return Devuelve la última id autoincrementada utilizada para realizar la insercción de una fila
		   * en alguna de las tablas usando esta conexión, o 0 si no hubo ninguna consulta 
		   * previa de insercción, o si la hubo, pero en la tabla donde se insertaba, no habia
		   * ningún campo autoincrementable. 
		   */
		  public function getUltimaId() 
		  {
			  return mysqli_insert_id($this->db);
		  }
		  
		  
		  /**
		   * Confirma la transacción actual.
		   */
		  public function commit()
		  {
			 mysqli_commit($this->db); 
		  }
		  
		  /**
		   * Revierte la transacción actual.
		   */
		  public function rollback() 
		  {
			  mysqli_rollback($this->db);
		  }
		  
		  /* Atributos */
		  private static $singleton = NULL;
		  public $db;
		  public static $host;
		  public static $user;
		  public static $passwd;
		  public static $dbname;
	 }
	 
	  if(DBMySQL_type == 'test')
	  {
		  /* en test, nos conectaremos a la base de datos de test */
		  DBMySQL::$host = 'localhost';
		  DBMySQL::$user = 'root';
		  DBMySQL::$passwd = '1234';
		  DBMySQL::$dbname = 'a4g_test';
	  }
	  else 
	  {
		  /* en producción, a la base de datos normal */
		  DBMySQL::$host = 'localhost';
		  DBMySQL::$user = 'siw22';
		  DBMySQL::$passwd = 'eirafewaey';
		  DBMySQL::$dbname = 'siw22';
	  }
?>
