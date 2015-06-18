<?php
	/**
	 * @author Víctor Ruiz Gómez.
	 * @file \brief Proporciona una serie de métodos para modificar y acceder de
	 * forma sencilla a la base de datos.
	 */
	 
	 require_once 'DBMySQL.php';
	 require_once 'DBMySQLStatement.php';
	 require_once 'hash.php';
	 require_once 'usuario.php';
	 require_once 'producto.php';
	 require_once 'categoria.php';
	 require_once 'comentario.php';
	 
	 class DBMySQLQueryManager
	 {
		 
		/* queries relacionadas con usuarios */
		/**
		 * Busca un usuario por id en la BD.
		 * @return Devuelve el usuario cuya id es la indicada, o NULL si no hay ningún usuario 
		 * con esa ID
		 * @param id Es la id
		 */
		public static function buscarUsuarioPorId($id) 
		{
			$query = 'SELECT nombre, passwd, admin, fecha_registro FROM final_usuario WHERE id = :1';
			$resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($id);
			$fila;
			if(is_null($fila = $resultado->fetch_assoc()))
			{
				return NULL;
			}
			$usuario = new Usuario($id, $fila['nombre'], $fila['passwd'], intval($fila['admin']), $fila['fecha_registro']);
			return $usuario;
		}

		/**
		 * Busca un usuario por nombre en la BD.
		 * @return Devuelve el usuario cuya id es la indicada, o NULL si no hay ningún usuario
		 * con ese nombre.
		 */
		public static function buscarUsuarioPorNombre($nombre)
		{
			$query = 'SELECT id, passwd, admin, fecha_registro FROM final_usuario WHERE nombre = :1';
			$resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($nombre); 
			if(is_null($fila = $resultado->fetch_assoc()))
			{
				return NULL;
			}
			$usuario = new Usuario(intval($fila['id']), $nombre, $fila['passwd'], intval($fila['admin']), $fila['fecha_registro']);
			return $usuario;
		}	

		/**
		 * Registra un nuevo usuario en la BD.
		 * @param $nombre Es el nombre del nuevo usuario (Debe ser único, por tanto, no debe coincidir con el nombre 
		 * de otro usuario existente)
		 * @param $passwd Es la contraseña del usuario encriptada. 
		 * @return Devuelve el usuario registrado
		 */
		public static function registrarUsuario($nombre, $passwd) 
		{
			$query = 'INSERT INTO final_usuario (nombre, passwd, fecha_registro) VALUES(:1, :2, NOW())';
			$resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($nombre, $passwd);
			$id = DBMySQL::instancia()->getUltimaId();
			$resultado = DBMySQL::instancia()->prepararQuery('SELECT admin, fecha_registro FROM final_usuario WHERE id = :1')->ejecutar($id);
			$fila = $resultado->fetch_assoc();
			return new Usuario($id, $nombre, $passwd, intval($fila['admin']), $fila['fecha_registro']);
		}
		
		
		/* queries relacionadas con productos */
		/**
		 * Busca un producto por id. Este método se usa cuando el usuario
		 * visita la página de visualización de un producto. 
		 * @param id_producto Es la id del producto
		 * @param id_usuario Es la id del usuario que quiere visualizar el producto, o null
		 * si el usuario es anónimo.  El producto se buscará entre los productos no eliminados 
		 * por los usuarios administradores. Si además se indica este parámetro, se buscará también
		 * en los productos adquiridos por el usuario. 
		 */
		 public static function buscarProductoPorId($id_producto, $id_usuario = NULL)
		 {
			 $query = 
			 'SELECT P.nombre, U.nombre autor FROM (SELECT id, nombre, id_autor FROM 
			 final_producto WHERE (id = :1) AND (?)) P INNER JOIN final_usuario U ON P.id_autor = U.id';
			 
			 if(is_null($id_usuario))
			 {
				 $query = str_replace('?', 'eliminado = false', $query);	 
			 }
			 else 
			 {
				 $query = str_replace('?', '(eliminado = false) OR (id IN (SELECT id_producto FROM final_adquiere WHERE id_usuario = :2))', $query);
			 }
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($id_producto, $id_usuario);
			 $fila;
			 if(is_null($fila = $resultado->fetch_assoc()))
			 {
				 return NULL;
			 }
			 
			 $producto = new Producto($id_producto, $fila['nombre'], $fila['autor']);
			 return $producto;
		 }
		 
		 /**
		  * Registra un nuevo producto en la base de datos.
		  * @param nombre Es el nombre del nuevo producto.
		  * @param descripcion Es su descripcion
		  * @param precio Es el precio
		  * @param id_autor La id del usuario que publica el producto
		  * @param categoria La categoría del producto. 
		  */
		 public static function registrarProducto($nombre, $descripcion, $precio, $id_autor, $categoria)
		 {
			 $query = 'INSERT INTO final_producto (nombre, descripcion, precio, id_autor, id_categoria, fecha_publicacion) VALUES(:1, :2, :3, :4, :5, NOW())';
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($nombre, $descripcion, $precio, $id_autor, $categoria->getId() );
			 $id = DBMySQL::instancia()->getUltimaId();
			 return self::buscarProductoPorId($id);
		 }
		 
		 /**
		  * Este método registra la compra de un listado de productos, realizada por
		  * un usuario determinado.
		  * @param id_usuario Es la id del usuario que ha comprado los productos.
		  * @param productos Es un listado de productos que ha comprado el usuario, cuya adquisción
		  * se quiere registrar en la bd.
		  */
		 public static function registrarCompraProductos($id_usuario, $ids_productos)
		 {
			 $datos = array();
			 foreach($ids_productos as $id_producto)
			 {
				 $datos[] = "($id_usuario, $id_producto, NOW())";
			 }
			 $query = 'INSERT INTO final_adquiere (id_usuario, id_producto, fecha_compra) VALUES ' . implode(', ', $datos);
			 $resultado = DBMySQL::instancia()->ejecutarQuery($query);
			 DBMySQL::instancia()->commit();
		 }
		 
		 /**
		  * Comprueba si un producto esta disponible para el usuario; Es decir, si se cumple que es el autor del mismo,
		  * el producto es gratuito o ha sido comprado por el usuario previamente.
		  * (En estas condiciones el usuario puede descargar el producto)
		  * @return Devuelve true si el producto está disponible para el usuario. false si el usuario no existe, el producto
		  * no existe o el producto no está disponible para el usuario
		  * @param id_usuario Es la id del usuario
		  * @param id_producto Es la id del producto.
		  */
		 public static function esProductoDisponibleParaUsuario($id_usuario, $id_producto)
		 {
			 $query = 
			 'SELECT id FROM (SELECT * FROM _final_ids_productos) P WHERE (id = :2) AND (
			 (id IN (SELECT id FROM (SELECT id, id_autor, precio FROM final_producto) Q WHERE (Q.id = P.id) AND ((id_autor = :1) OR (precio = 0)) )) OR
			 (id IN (SELECT id_producto FROM (SELECT id_usuario, id_producto FROM final_adquiere) Q WHERE (Q.id_producto = P.id) AND (id_usuario = :1) )) )';
			 
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($id_usuario, $id_producto);
			 return !is_null($resultado->fetch_assoc()); 
		 }
		 
		 public static function getDetallesProducto($id)
		 {
			 $query = 
			 'SELECT descripcion, precio, DATE(fecha_publicacion) fecha_publicacion, valoracion, num_descargas, C.id id_categoria, C.nombre nombre_categoria, familia
			  FROM (SELECT id, descripcion, precio, fecha_publicacion, id_categoria FROM final_producto WHERE (id = :1)) P
			  INNER JOIN _final_valoracion_producto V ON P.id = V.id INNER JOIN final_categoria C ON P.id_categoria = C.id
			  INNER JOIN _final_num_descargas_producto D ON P.id = D.id';
			 
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($id);
			 $fila;
			 if(is_null($fila = $resultado->fetch_assoc()))
			 {
				 return NULL;
			 }
			 $categoria = new Categoria(intval($fila['id_categoria']), $fila['nombre_categoria'], $fila['familia']);
			 $detalles = new DetallesProducto($fila['descripcion'], intval($fila['precio']), $fila['fecha_publicacion'], intval($fila['valoracion']), $categoria);
			 return $detalles;
		 } 
		 
		 /**
		  * @param usuario Es el usuario del que se quiere obtener sus productos publicados. 
		  * @return Devuelve un array de productos que han sido adquiridos por el usuario indicado.
		  * @note Si el usuario no existe o bien, este no ha adquirido ningún producto, devuelve un array vacío.
		  */
		 public static function getProductosAdquiridosPorUsuario($usuario) 
		 {
			 $query = 
			 'SELECT P.id, P.nombre, U.nombre autor, descripcion, precio, DATE(fecha_publicacion) fecha_publicacion, valoracion, num_descargas, C.id id_categoria, C.nombre nombre_categoria, familia, fecha_compra 
			 FROM (SELECT id, nombre, descripcion, precio, fecha_publicacion, id_categoria, id_autor FROM final_producto) P
			 INNER JOIN (SELECT * FROM final_adquiere WHERE id_usuario = :1) A ON A.id_producto = P.id INNER JOIN _final_valoracion_producto V ON P.id = V.id
			 INNER JOIN final_categoria C ON P.id_categoria = C.id INNER JOIN _final_num_descargas_producto D ON P.id = D.id INNER JOIN final_usuario U ON U.id = P.id_autor';
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($usuario->getId());
			 $productos = array();
			 $fila;
			 while(!is_null($fila = $resultado->fetch_assoc()))
			 {
				 $categoria = new Categoria(intval($fila['id_categoria']), $fila['nombre_categoria'], $fila['familia']);
				 $detalles = new DetallesProducto($fila['descripcion'], intval($fila['precio']), $fila['fecha_publicacion'], intval($fila['valoracion']), $categoria, $fila['fecha_compra']);
				 $producto = new Producto(intval($fila['id']), $fila['nombre'], $fila['autor'], $detalles);
				 $productos[] = $producto;
			 }
			 
			 return $productos;
		 }
		 
		 /**
		  * @return Devuelve un listado de productos metidos en el carrito de la compra.
		  * @param items Es un array con las ids de los productos que están en el carrito de la compra
		  */
		 public static function getProductosEnCarrito($items) 
		 {
			 if(empty($items))
			 {
				 return array(); /* el carrito está vacio, no hay ningún producto (no hace falta acceder a la bd */
			 }
			 
			 $query = 
			 'SELECT P.id, P.nombre, U.nombre autor, descripcion, precio, DATE(fecha_publicacion) fecha_publicacion, valoracion, C.id id_categoria, C.nombre nombre_categoria, familia 
			 FROM (SELECT id, nombre, descripcion, precio, fecha_publicacion, id_categoria, id_autor FROM final_producto WHERE id IN (?)) P
			 INNER JOIN _final_valoracion_producto V ON P.id = V.id INNER JOIN final_categoria C ON P.id_categoria = C.id INNER JOIN (SELECT id, nombre FROM final_usuario) U ON P.id_autor = U.id ORDER BY P.id ASC';
			 $query = str_replace('?', implode(', ', $items), $query);

			 $resultado = DBMySQL::instancia()->ejecutarQuery($query);
			 $productos = array();
			 $fila;
			 while(!is_null($fila = $resultado->fetch_assoc()))
			 {
				 $categoria = new Categoria(intval($fila['id_categoria']), $fila['nombre_categoria'], $fila['familia']);
				 $detalles = new DetallesProducto($fila['descripcion'], intval($fila['precio']), $fila['fecha_publicacion'], intval($fila['valoracion']), $categoria);
				 $producto = new Producto(intval($fila['id']), $fila['nombre'], $fila['autor'], $detalles);
				 $productos[] = $producto;
			 }
			 return $productos; 
		 }
		 
		 /**
		  * Igual que el método getProductosAdquiridosPorUsuario, solo que devuelve los productos publicados por el usuario 
		  */
		 public static function getProductosPublicadosPorUsuario($usuario)
		 {
			 $query = 
			 'SELECT P.id, P.nombre, descripcion, precio, DATE(fecha_publicacion) fecha_publicacion, valoracion, num_descargas, C.id id_categoria, C.nombre nombre_categoria, familia 
			 FROM (SELECT id, nombre, descripcion, precio, fecha_publicacion, id_categoria, id_autor FROM final_producto WHERE id_autor = :1) P
			 INNER JOIN _final_valoracion_producto V ON P.id = V.id INNER JOIN final_categoria C ON P.id_categoria = C.id INNER JOIN 
			 _final_num_descargas_producto D ON P.id = D.id';
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($usuario->getId());
			 $productos = array();
			 $fila;
			 while(!is_null($fila = $resultado->fetch_assoc()))
			 {
				 $categoria = new Categoria(intval($fila['id_categoria']), $fila['nombre_categoria'], $fila['familia']);
				 $detalles = new DetallesProducto($fila['descripcion'], intval($fila['precio']), $fila['fecha_publicacion'], intval($fila['valoracion']), $categoria);
				 $producto = new Producto(intval($fila['id']), $fila['nombre'], $usuario->getNombre(), $detalles);
				 $productos[] = $producto;
			 }
			 
			 return $productos;
		 }
		 
		 /**
		  * Esta consulta va a devolver un listado de productos destacados, pertenecientes a una familia en concreto.
		  * @return Devuelve un listado de productos, que como máximo tendrá "num_productos" productos. El listado contendrá
		  * menos productos que el máximo, si hay menos de "num_productos" productos que pertenecen a la familia indicada.
		  * @note Solo se buscará entre los productos no eliminados por los usuarios administradores.
		  */
		 public static function getProductosDestacados($familia, $num_productos)
		 {
			 $query = 
			 'SELECT P.id, P.nombre nombre, U.nombre autor FROM (SELECT id, nombre, id_autor, id_categoria FROM final_producto WHERE eliminado = FALSE) P
			 INNER JOIN (SELECT id FROM final_categoria WHERE familia = :1) C ON P.id_categoria = C.id INNER JOIN _final_valoracion_producto V ON P.id = V.id
			 INNER JOIN (SELECT id, nombre FROM final_usuario) U ON P.id_autor = U.id ORDER BY valoracion DESC, P.id ASC ';
			 $query .= 'LIMIT ' . $num_productos . ' OFFSET 0';
			 
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($familia);
			 $productos = array();
			 $fila;
			 while(!is_null($fila = $resultado->fetch_assoc()))
			 {
				 $productos[] = new Producto(intval($fila['id']), $fila['nombre'], $fila['autor']);
			 } 
			 return $productos;
		 }
		 
		 /**
		  * Obtiene la valoración del usuario de un producto en concreto.
		  * @param id_usuario Es la id del usuario
		  * @param id_producto Es la id del producto.
		  * @return Devuelve la valoración sobre el producto, del usaurio, o NULL si el usuario todavía no ha valorado el
		  * producto.
		  */
		 public static function getValoracionProductoDeUsuario($id_usuario, $id_producto) 
		 {
			$query = 
			'SELECT valoracion FROM final_valora WHERE (id_usuario = :1) AND (id_producto = :2)';
			$resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($id_usuario, $id_producto);
			$fila;
			if(is_null($fila = $resultado->fetch_assoc()))
			{
				return NULL;
			} 
			return intval($fila['valoracion']);
		 }
		 
		 /**
		  * Actualiza la valoración del usuario de un producto en concreto.
		  * @param id_usuario Es la id del usuario
		  * @param id_producto Es la id del producto
		  * @param valoracion Es la nueva valoración del producto.
		  */
		 public static function actualizarValoracionProductoDeUsuario($id_usuario, $id_producto, $valoracion)
		 {
			 $query = 
			 'INSERT INTO final_valora (id_usuario, id_producto, valoracion) VALUES (:1, :2, :3) ON DUPLICATE KEY UPDATE valoracion = :3';
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($id_usuario, $id_producto, $valoracion);
			 DBMySQL::instancia()->commit();
		 }
		 
		 /**
		  * @param id_producto Es la id del producto a eliminar
		  */ 
		 public static function eliminarProducto($id_producto)
		 {
			 $query = 'UPDATE final_producto SET eliminado = TRUE WHERE id = :1';
			 DBMySQL::instancia()->prepararQuery($query)->ejecutar($id_producto);
			 DBMySQL::instancia()->commit();
		 }
		 
		 /**
		  * Cambiar el producto de una categoría a otra 
		  * @param id_producto Es la id del producto que queire catalogarse de forma distinta.
		  * @param id_categoria Es la id de la nueva categoría del producto.
		  */
		 public static function cambiarCategoriaProducto($id_producto, $id_categoria)
		 {
			 $query = 'UPDATE final_producto SET id_categoria = :2 WHERE id = :1';
			 DBMySQL::instancia()->prepararQuery($query)->ejecutar($id_producto, $id_categoria);
			 DBMySQL::instancia()->commit();
		 }
		 
		 /**
		  * @return Devuelve los comentarios realizados por los usuarios de un producto en concreto, ordenados por fecha
		  * de publicación (del más reciente al menos reciente)
		  * @param id_producto Es la id del producto
		  * @param num_comentarios Es el número máximo de comentarios que se quiere obtener. Establecer a NULL para obtener
		  * todos los comentarios
		  */
		 public static function getComentariosProducto($id_producto, $num_comentarios = NULL)
		 {
			 $query = 
			 'SELECT C.id, texto, fecha_post, U.id id_autor, nombre nombre_autor, passwd, admin, fecha_registro FROM  (SELECT id_comentario, id_usuario FROM final_comenta WHERE id_producto = :1) Q
			 INNER JOIN final_comentario C ON (Q.id_comentario = C.id) INNER JOIN (SELECT * FROM final_usuario) U ON (Q.id_usuario = U.id)
			 ORDER BY fecha_post DESC, C.id ASC';
			 if(!is_null($num_comentarios))
			 {
				 $query .= "LIMIT $num_comentarios OFFSET 0";
			 }
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($id_producto);
			 $comentarios = array();
			 $fila;
			 while(!is_null($fila = $resultado->fetch_assoc()))
			 {
				 $usuario = new Usuario(intval($fila['id_autor']), $fila['nombre_autor'], $fila['passwd'], $fila['admin'], $fila['fecha_registro']);
				 $comentarios[] = new Comentario(intval($fila['id']), $fila['texto'], $fila['fecha_post'], $usuario);
			 }
			 
			 return $comentarios;
		 }
		 
		 
		 /**
		  * Registra un nuevo comentario realizado por un usuario de un producto en concreto.
		  * @param id_usuario Es la id del usuario que realiza el comentario.
		  * @param id_producto Es la id del producto que es comentado.
		  * @param comentario Es el texto del comentario.
		  */
		 public static function registrarPost($id_usuario, $id_producto, $comentario)
		 {
			 /* primero creamos un nuevo comentario */
			 DBMySQL::instancia()->prepararQuery('INSERT INTO final_comentario (fecha_post, texto) VALUES(NOW(), :1)')->ejecutar($comentario);
			 
			 /* vinculamos el comentario con el usuario y el producto */
			 $id_comentario = DBMySQL::instancia()->getUltimaId();
			 DBMySQL::instancia()->prepararQuery('INSERT INTO final_comenta (id_usuario, id_producto, id_comentario) VALUES(:1, :2, :3)')->ejecutar($id_usuario, $id_producto, $id_comentario);
			 
			 DBMySQL::instancia()->commit();
		 }
		 
		 
		 /* queries relacionadas con categorías de productos */
		 /**
		  * @return Devuelve la categoría cuya id es la especificada o NULL si no existe ninguna
		  * categoría con esa id 
		  */
		 public static function buscarCategoriaPorId($id)
		 {
			 $query = 'SELECT nombre, familia FROM final_categoria WHERE id = :1';
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($id);
			 $fila;
			 if(is_null($fila = $resultado->fetch_assoc()))
			 {
				return NULL;
			 }
			 $usuario = new Categoria($id, $fila['nombre'], $fila['familia']);
			 return $usuario;
		 }
		 
		 /**
		  * Registra una nueva categoría en la base de datos
		  * @param nombre Es el nombre de la nueva categoría
		  * @param familia Es la familia a la que pertenece la nueva categoría
		  */
		 public static function registrarCategoria($nombre, $familia)
		 {
			 $query = 'INSERT INTO final_categoria (nombre, familia) VALUES(:1, :2)';
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($nombre, $familia);
			 $id = DBMySQL::instancia()->getUltimaId();
			 return new Categoria($id, $nombre, $familia);
		 }
		 
		 /**
		  * Obtiene los productos que están en una página especificada,
		  * dentro del catálogo de productos asociado a la categoría indicada como parámetro
		  * @note Los productos están ordenados en función del parámetro orden. Este parámetro y el parámetro
		  * invertir, determinan la dispocición de los productos del catálogo en las diferentes páginas del mismo.
		  * @param categoria Es la categoria.
		  * @param orden Es el tipo de ordenación del catálogo, que puede ser por "fecha_publicacion", "coste", 
		  * "valoracion" o "nombre".
		  * @param invertir Un valor booleano indicando si el orden debe invertirse. 
		  * @param primero Es el cardinal del primer producto a devolver en el resultado (usando el orden indicado).
		  * @param ultimo Es el cardinal del último producto a devolver en el resultado (usando el orden indicado)
		  */
		 public static function getCatalogoProductos($categoria, $orden, $invertir, $primero, $ultimo)
		 {
			 /* la consulta dependerá de el tipo de ordenación del catálogo... */
			 $query;
			 if($orden != 'valoracion')
			 {
				 $query = 'SELECT P.id, P.nombre, U.nombre autor FROM (SELECT id, nombre, id_autor';
				 if($orden == 'coste')
				 {
					 $query .= ', precio';
				 }
				 elseif($orden == 'fecha_publicacion')
				 {
					 $query .= ', fecha_publicacion';
				 } 
				 $query .= ' FROM final_producto WHERE (id_categoria = :1) AND (eliminado = FALSE)) P INNER JOIN final_usuario U ON P.id_autor = U.id ORDER BY ';
				 if($orden == 'coste')
				 {
					 $query .= 'precio ' . ($invertir ? 'DESC' : 'ASC');
				 }
				 elseif($orden == 'fecha_publicacion')
				 {
					 $query .= 'fecha_publicacion ' . ($invertir ? 'ASC' : 'DESC');
				 }
				 else
				 {
					 $query .= 'nombre ' . ($invertir ? 'DESC' : 'ASC'); 
				 }
				 $query .= ', P.id ASC'; /* ordenar por id en caso de empate */
			 }
			 else 
			 {
				 $query = 
				 'SELECT P.id, P.nombre, U.nombre autor FROM (SELECT id, nombre, id_autor FROM final_producto WHERE (id_categoria = :1) AND (eliminado = FALSE)) P 
				 INNER JOIN final_usuario U ON P.id_autor = U.id INNER JOIN _final_valoracion_producto V ON P.id = V.id ';
				 $query .= 'ORDER BY valoracion ' . ($invertir ? 'ASC' : 'DESC') . ', P.id ASC';
			 }
			 
			 /* añadimos en la query la sentencia LIMIT para acotar el resultado de la misma y obtener solo un subconjunto de los
			  * productos del catálogo 
			  */
			 $query .= ' LIMIT ' . ($ultimo - $primero + 1) . ' OFFSET ' . ($primero - 1);
			 
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($categoria->getId());
			 $productos = array();
			 $fila;
			 while(!is_null($fila = $resultado->fetch_assoc()))
			 {
				 $producto = new Producto(intval($fila['id']), $fila['nombre'], $fila['autor']);
				 $productos[] = $producto;
			 }
			 
			 return $productos;
		 }
		 
		 /**
		  * @param categoria Es la categoría.
		  * @return Devuelve el número de productos que hay en una categoría en concreto 
		  */
		 public static function getNumProductosEnCategoria($categoria)
		 {
			 $query = 'SELECT COUNT(*) num_productos FROM final_producto WHERE id_categoria = :1';
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($categoria->getId());
			 $fila;
			 if(is_null($fila = $resultado->fetch_assoc()))
			 {
				return NULL;
			 }
			 return intval($fila['num_productos']);
		 }
		  
		 
		 /**
		  * @param familia Es la familia de la cual quiere obtener sus categorías. 
		  * @return Devuelve todas las categorías que están dentro de la familia indicada
		  * como parámetro 
		  * @param todas Es un valor booleano que indica si deben devolverse las categorías que no tengan ningún producto
		  * (si esta a false se devolverán aquellas categorías que al menos tengan un producto)
		  * @note El listado se ordenará por número total de productos de forma descendente (las categorías
		  * con más productos aparecerán las primeras)
		  */
		 public static function buscarCategoriasPorFamilia($familia, $todas = false)
		 {
			 $query =
			 'SELECT id, nombre FROM (SELECT id, nombre FROM final_categoria WHERE (familia = :1)) C ' . ($todas ? 'LEFT JOIN' : 'INNER JOIN' ) .
			 ' (SELECT id_categoria, COUNT(*) num_productos FROM final_producto GROUP BY id_categoria) P ON P.id_categoria = C.id 
			 ORDER BY ' . ($todas ? 'COALESCE(num_productos,0)' : 'num_productos') . ' DESC, id ASC';
			 
			 $resultado = DBMySQL::instancia()->prepararQuery($query)->ejecutar($familia);
			 $fila;
			 $categorias = array();
			 while(!is_null($fila = $resultado->fetch_assoc()))
			 {
				 $categoria =  new Categoria(intval($fila['id']), $fila['nombre'], $familia);
				 $categorias[] = $categoria;
			 }
			 return $categorias; 
		 }
		 
		 /**
		  * @param Es la categoría que se quiere eliminar.
		  */
		 public static function eliminarCategoria($id_categoria)
		 {
			 $query = 'DELETE FROM final_categoria WHERE id = :1';
			 DBMySQL::instancia()->prepararQuery($query)->ejecutar($id_categoria);
			 DBMySQL::instancia()->commit();
		 }
		 
		 /**
		  * Cambia el nombre de una categoría.
		  * @param id_categoria Es la id de la categoría que queremos renombrar.
		  * @param nombre Es el nuevo nombre de la categoría 
		  */
		 public static function renombrarCategoria($id_categoria, $nombre)
		 {
			 $query = 'UPDATE final_categoria SET nombre = :2 WHERE id = :1';
			 DBMySQL::instancia()->prepararQuery($query)->ejecutar($id_categoria, $nombre);
			 DBMySQL::instancia()->commit();
		 }
	 }
?>
