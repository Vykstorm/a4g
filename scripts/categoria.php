<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Este script define una clase cuyas instancias representan
	 * categorías de productos
	 */
	 
	 require_once 'DBMySQLQueryManager.php';
	 require_once 'almacen.php';
	 
	 class Categoria
	 {
		 /**
		  * @return Devuelve la categoria cuya id es la indicada como 
		  * parámetro, o null si no existe ninguna categoría con esa id 
		  */
		 public static function buscarPorId($id)
		 {
			 return DBMySQLQueryManager::buscarCategoriaPorId($id);
		 }
		 
		 /**
		  * Crea una nueva categoría.
		  * @param nombre Es el nombre de la categoría 
		  * @param familia Es la familia a la que pertenece la categoría.
		  * @param imagen Es la imagen de la categoría
		  * @return Devuelve la categoría creada.
		  */
		 public static function registrar($nombre, $familia, $imagen)
		 {
			 $categoria = DBMySQLQueryManager::registrarCategoria($nombre, $familia);
			 Almacen::registrarCategoria($categoria, $imagen);
			 return $categoria;
		 }
		 
		 /**
		  * Obtiene un listado de categorías que están dentro de una familia.
		  * @param familia Es la familia de la cual quiere obtener sus categorías. 
		  * @return Devuelve todas las categorías que están dentro de la familia indicada
		  * como parámetro 
		  * @param todas Es un valor booleano que indica si deben devolverse las categorías que no tengan ningún producto
		  * (si esta a false se devolverán aquellas categorías que al menos tengan un producto)
		  * @note El listado se ordenará por número total de productos de forma descendente (las categorías
		  * con más productos aparecerán las primeras)
		  */
		 public static function buscarPorFamilia($familia, $todas = false)
		 {
			 return DBMySQLQueryManager::buscarCategoriasPorFamilia($familia, $todas);
		 }
		 
		 /**
		  * Constructor.
		  */
		 public function __construct($id, $nombre, $familia)
		 {
			 $this->id = $id;
			 $this->nombre = $nombre;
			 $this->familia = $familia;
		 }
		 
		 /* Consultores */
		 /**
		  * @return Devuelve la id de la categoría  
		  */
		 public function getId() 
		 {
			 return $this->id;
		 }
		 
		 /**
		  * @return Devuelve el nombre de la categoría
		  */
		 public function getNombre() 
		 {
			 return $this->nombre;
		 }
		 
		 /**
		  * @return Devuelve la familia de la categoría
		  */
		 public function getFamilia()
		 {
			 return $this->familia;
		 }
		 
		 /**
		  * Obtiene un catálogo de productos asociado con esta categoría.
		  * @param orden Indica como debe estar ordenado el catálogo.
		  * Debe ser uno de los siguientes valores: "fecha_publicacion", "valoracion", "nombre", 
		  * "coste"
		  * @return Devuelve el catálogo de productos de esta categoría.
		  */
		 public function getCatalogo($orden) 
		 {
			 return new CatalogoProductos($this, $orden);
		 }
		 
		 /**
		  * @return Devuelve la url de la imágen asociada a esta categoría
		  */
		 public function getImagen()
		 {
			 return Almacen::getImagenDeCategoria($this->getId()); 
		 }
	
		 
		 /* Atributos */
		 private $id;
		 private $nombre;
		 private $familia;
	 }
	 
	 class CatalogoProductos
	 {
		 /**
		  * Constructor
		  */
		 public function __construct($categoria, $orden)
		 {
			 $this->categoria = $categoria;
			 $this->num_productos = DBMySQLQueryManager::getNumProductosEnCategoria($this->categoria);
			 $this->orden = $orden;
			 $this->invertir_orden = false;
		 }
		  
		 /* Consultores */ 
		 /**
		  * @return Devuelve la categoría de este catálogo 
		  */
		 public function getCategoria() 
		 {
			 return $this->categoria;
		 }
		 
		 /**
		  * @return Devuelve el orden del catálogo de productos 
		  */
		 public function getOrden() 
		 {
			 return $this->orden; 
		 }
		 
		 /**
		  * @return Devuelve un valor booleano indicando si el orden de los
		  * productos del catálogo está invertido o no
		  */
		 public function estaOrdenInvertido() 
		 {
			 return $this->invertir_orden;
		 }
		 
		 /**
		  * @return Devuelve el número total de productos del catálogo 
		  */
		 public function getNumProductos() 
		 {
			 return $this->num_productos; 
		 }
		 
		 /**
		  * @return Devuelve el número de productos por cada una de las
		  * páginas del catálogo. 
		  */
		 public function getNumProductosPorPagina() 
		 {
			 $familia = $this->categoria->getFamilia();
			 if(($familia == 'Modelos 3D') || ($familia == 'Texturas'))
			 {
				 return 12;
			 }
			 return 6;
		 }
		 
		 /**
		  * @return Devuelve el número mínimo de productos que debe tener
		  * una página (si hubiera más de una página)
		  * @note En el caso de que la última página del catálogo tuviera menos
		  * productos que esta cantidad, estos deberían colocarse en la penúltima
		  * página
		  */
		 public function getMinNumProductosPorPagina() 
		 {
			 $familia = $this->categoria->getFamilia();
			 if(($familia == 'Modelos 3D') || ($familia == 'Texturas'))
			 {
				 return 5;
			 } 
			 return 3;
		 }
		 
		 /**
		  * @return Devuelve el número total de páginas de este catálogo
		  * de productos. (Se tiene en cuenta que si la última página tiene 
		  * menos del mínimo número de productos por página, esta no se cuenta)
		  */
		 public function getNumPaginas() 
		 {
			 $num_productos = $this->getNumProductos();
			 $productos_por_pagina = $this->getNumProductosPorPagina();
			 $min_productos_por_pagina = $this->getMinNumProductosPorPagina();
			 $num_paginas = ceil($num_productos / $productos_por_pagina);
			 $r = $num_productos % $productos_por_pagina;
			 if(($num_paginas > 1) && (($r > 0) && ($r < $min_productos_por_pagina))) 
			 {
			
				 /* hay una página al final, con al menos algún producto, pero menos
					productos que el mínimo de productos por página */
				  $num_paginas--;  /* incluimos los productos de la última página en la penúltima */
			 }
			 return $num_paginas;
		 }
		 
		 /**
		  * @param num_pagina Es el número de página del catálogo, de la cual queremos obtener sus productos.
		  * Debe ser un entero entre 0 y getNumPaginas()-1
		  * @return Devuelve el número de productos que hay en la página indicada como parámetro. 
		  */
		 public function getProductosEnPagina($num_pagina)
		 {
			 /* obtener el rango de productos que van en esta página */
			 $productos_por_pagina = $this->getNumProductosPorPagina();
			 $min_productos_por_pagina = $this->getMinNumProductosPorPagina();
			 $primero =  $num_pagina * $productos_por_pagina;
			 $ultimo = ($num_pagina+1) * $productos_por_pagina - 1;
			 if($ultimo > ($this->num_productos - 1))
			 {
				 /* estamos en la última página, y esta tiene un número mínimo de 
				  * productos */
				 $ultimo = $this->num_productos - 1; /* ajustamos el rango */
			 }
			 else 
			 {
				 /* estamos en la penúltima página y la última página tiene menos elementos
				  * que el número de productos por página ?  */
				 if(((($num_pagina+2) * $productos_por_pagina-1) > ($this->num_productos-1)) && (($this->num_productos-1-$ultimo) < $min_productos_por_pagina))
				 {
					 /* incluimos productos en la última, a la penúltima */
					 $ultimo = $this->num_productos - 1;
				 }
			 }
			 
			 
			 /* acceder a la BD y obtener los productos de la página */
			 return DBMySQLQueryManager::getCatalogoProductos($this->categoria, $this->orden, $this->invertir_orden, $primero+1, $ultimo+1);
		 }
		 
		 /* Acciones */
		 /**
		  * Invierte el orden de los productos en el catálogo.
		  * @note Esto afecta a la disposición de los productos en las páginas del catálogo.
		  */
		 public function invertirOrden()
		 {
			 $this->invertir_orden = !$this->invertir_orden;
		 }
		  
		 /* Atributos */
		 private $categoria, $num_productos, $orden, $invertir_orden;
	 }
?>
