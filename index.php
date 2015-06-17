<?php
	/**
	 * @author Victor Ruiz Gómez
	 * @file \brief
	 * Este script recibe las peticiones de los clientes que navegan por
	 * la web (exceptuando las peticiones de inicio de sesión, registro y 
	 * logout, que las llevan a cabo los scripts alta.php, login.php y logout.php)
	 * 
	 * Parámetros que deben indicarse en cualquier petición:
	 * acción -> Indica la acción a realizar. Podrá tomar los siguientes valores:
	 * verProducto, verPerfil, verFamilia, verCategoria, publicarProducto, verCarrito,
	 * buscarProducto.
	 * Si no se especifica la acción, por defecto se mostará el índice principal.
	 * Si se indica, pero la acción no es válida, se muestra una página con un mensaje de error.
	 */
	 require_once 'scripts/parser.php';
	 require_once 'scripts/sesion.php';
	 require_once 'scripts/excepciones/usuario.php';
	 require_once 'scripts/excepciones/categoria.php';
	 require_once 'scripts/excepciones/familia.php';
	 require_once 'scripts/excepciones/producto.php';
	 
	 /**
	  * Esta función es invocada cuando no se ha definido ninguna acción.
	  * Muestra el indice principal de la página
	  */
	 function verIndice()
	 {
		 /* leemos la plantilla del indice principal */
		 $plantilla = file_get_contents('plantillas/index.html');
		 
		 /* procesamos la plantilla */
		 $parser_plantilla = new ParserIndice();
		 $documento = $parser_plantilla->parsear($plantilla);

		 echo $documento;
	 }
	 
	 /**
	  * Muestra el perfil de un usuario (puede ser el propio usuario que ha realizado la petición,
	  * u otro usuario ajeno. Debe indicarse el nombre del usuario como parámetro por la url para ver su perfil.
	  * Si el usuario esta logeado e indica su nombre, verá su propio perfil.
	  * En el caso de que no indique el parámetro, verá su propio perfil (pero tendrá que estar logeado).
	  * e.g
	  * cliente: 'pepe'
	  * index.php?accion=verPerfil&usuario=pepe => verá su perfil
	  * index.php?accion=verPerfil => verá su perfil si está logeado.
	  * index.php?accion=verPerfil&usuario=manolo => verá el perfil de otro usuario.
	  * 
	  */
	 function verPerfil()
	 {
		 require_once 'scripts/usuario.php';
		 if(empty($_GET['usuario']) && !Sesion::estaUsuario())
		 {
			 throw new UsuarioNoValidoException();
		 }
		 
		 $usuario;
		 $usuario_cliente = Sesion::getUsuario();
		 if(!empty($_GET['usuario']))
		 {
			 if(is_null($usuario = Usuario::buscarPorNombre($_GET['usuario'])))
			 {
				 throw new UsuarioNoEncontradoException($_GET['usuario']);
			 }
		 }
		 else
		 {
			 $usuario = $usuario_cliente;
			 if(is_null($usuario))
			 {
				 throw new UsuarioNoValidoException();
			 }
		 }
		 
		 /* usuario es el usuario del que se quiere ver su perfil.
		 usuario_cliente es el usuario que quiere ver el perfil. Estará a null si es anónimo */
		 /* mostramos el perfil del usuario. */
		 
		 $plantilla = file_get_contents('plantillas/perfil.html');
		 $parser_plantilla = new ParserPerfil($usuario, $usuario_cliente);
		 $documento = $parser_plantilla->parsear($plantilla);
		 echo $documento;
	 }
	 
	 /**
	  * Es invocado cuando se quiere ver un catálogo de categorías de una familia
	  * en concreto. Debe indicarse el nombre de la familia como parámetro en el get.
	  */
	 function verCatalogoCategorias() 
	 {
		 require_once 'scripts/categoria.php';
		 
		 $familias = array('Modelos 3D', 'Texturas', 'HDRI');
		 if(empty($_GET['familia']) || !in_array($_GET['familia'], $familias))
		 {
			 if(empty($_GET['familia']))
			 {
				 throw new FamiliaNoValidaException();
			 }
			 else 
			 {
				 throw new FamiliaNoValidaException($_GET['familia']);
			 }
		 }
		 $familia = $_GET['familia'];
		 
		 /* leemos la plantilla */
		 $plantilla = file_get_contents('plantillas/catalogos/categorias.html');
		
		 /* parseamos la plantilla */
		 $parser_plantilla = new ParserCatalogoCategorias($familia);
		 $documento = $parser_plantilla->parsear($plantilla);
		 
		 /* imprimimos el documento */
		 echo $documento;
	 }
	 

	 /**
	  * Muestra el catálogo de productos de una categoría en concreto. 
	  * (Solo se muestra una página del catálogo).
	  * Se debe pasar como parámetro, la id de la categoría, el número de la
	  * página del catálogo que debe mostrarse, como debe ordenarse el catálogo,
	  * o/y si debe invertirse el orden de los productos del catálogo.
	  * parámetros:
	  * categoria=id_categoria,
	  * orden=fecha_publicacion|valoracion|coste|nombre
	  * invertir=si|no 
	  * pagina=1|2 ... n
	  * Si no se especifica el parámetro orden, por defecto, es por fecha de publicación.
	  * Si no se indica el parámetro invertirOrden, su valor por defecto será no. 
	  * Si no se indica el número de la página, por defecto se mostrará la primera (1)
	  * 
	  * También puede pasarse como parámetro, la categoría (no se validará el parámetro categoría)
	  */
	 function verCatalogoProductos() 
	 {
		 require_once 'scripts/categoria.php';
		 
		 /* validamos los parámetros */
		 $id_categoria = intval($_GET['categoria']);
		 if(empty($_GET['categoria']) || empty($id_categoria))
		 {
			 throw new CategoriaNoValidaException();
		 }	
		 $id = intval($_GET['categoria']);
		 
		 if(is_null($categoria = Categoria::buscarPorId($id)))
		 {
			 throw new CategoriaNoValidaException();
		 }
		 
		 $ordenes = array ('fecha_publicacion', 'coste', 'valoracion', 'nombre');
		 $orden;
		 if(empty($_GET['orden']) || !in_array($_GET['orden'], $ordenes))
		 {
			 $orden = 'fecha_publicacion'; /* ordenamos por fecha de publicacion, por defecto */
		 }
		 else 
		 {
			 $orden = $_GET['orden'];
		 }
		 
		 $invertir_orden;
		 if(empty($_GET['invertir']) || (($_GET['invertir'] != 'si') && ($_GET['invertir'] != 'no')))
		 {
			 $invertir_orden = false;
		 }
		 else
		 {
			 $invertir_orden = ($_GET['invertir'] == 'si');
		 }
		 
		 $num_pagina;
		 if(!empty($_GET['pagina']))
		 {
			 $num_pagina = intval($_GET['pagina']);
			 if(empty($num_pagina))
			 {
				 throw new PaginaCatalogoProductosNoValida();
			 }
		 }
		 else 
		 {
			 $num_pagina = 1;
		 }
		 
		 $num_pagina--;
		 
		 /* obtenemos el catálogo asociado a la categoría */
		 $catalogo = $categoria->getCatalogo($orden);
		 
		 /* válidamos la página del catálogo */
		 if(($num_pagina < 0) || ($num_pagina >= $catalogo->getNumPaginas()))
		 {
			 throw new PaginaCatalogoProductosNoValida();
		 }
		 
		 if($invertir_orden)
		 {
			 /* invertimos el orden del catálogo */
			 $catalogo->invertirOrden();
		 }
		 
		 /* leemos la plantilla y la procesamos */
		 $plantilla = file_get_contents('plantillas/catalogos/productos.html');
		 $parser_plantilla = new ParserCatalogoProductos($catalogo, $num_pagina);
		 $documento = $parser_plantilla->parsear($plantilla);
		 /* imprimimos el resultado */
		 echo $documento;
	 }
	 
	 /**
	  * Esta función es invocada cuando el usuario quiere ver la página de visualización
	  * de un producto.
	  * Parámetos que deben pasarse via (_GET):
	  * producto=id_producto 
	  * 
	  * También puede pasarse un parámetro adicional 
	  * comentario=texto
	  * Este parámetro se ignora si el usuario no está logeado o si el comentario es un texto
	  * vacío. Si está logeado, el usuario
	  * añadirá un nuevo comentario sobre el producto. 
	  */
	 function verVisorProducto()
	 {		 
		 /* comprobamos los parámetros de la petición */
		 $producto;
		 if(!isset($_GET['producto']) || (intval($_GET['producto']) == 0) || is_null($producto = Producto::buscarPorId(intval($_GET['producto']))))
		 {
			 throw new ProductoNoValidoException();
		 }
		 
		 if(Sesion::estaUsuario() && !empty($_POST['comentario']))
		 {
			 $comentario = $_POST['comentario'];
			 $usuario = Sesion::getUsuario();
			 $usuario->postear($producto, $comentario);
		 }
		 
		 verProducto($producto);
	 }
	 
	 /**
	  * Esta función es invocada cuando el usuario quere ver un producto.
	  * @param producto Es el producto que quiere visualizar.
	  */
	 function verProducto($producto)
	 {
		 /* leemos la plantilla */
		 $plantilla = file_get_contents('plantillas/visor.html');
		 
		 /* procesamos la plantilla */
		 $parser_plantilla = new ParserVisorProducto($producto);
		 $documento = $parser_plantilla->parsear($plantilla);
		 echo $documento;
	 }
	 
	 /**
	  * Esta función es invocada cuando el usuario ve el carrito de la compra.
	  * Para ello, debe estar logeado.
	  * Se pueden pasar dos parámetros adicionales:
	  * eliminarProducto=id_producto, elimina un producto del carrito de la compra
	  * y/o meterProducto=id_producto, que añade un nuevo producto al carrito.
	  */
	 function verCarrito()
	 {
		 require_once 'scripts/producto.php';
		 
		 /* comprobamos que el usuario no es anónimo */
		 if(!Sesion::estaUsuario())
		 {
			 throw new UsuarioNoValidoException();
		 }
		 
		 /* obtenemos el carrito de la compra */
		 $carrito = Sesion::getCarrito();
		 
		 /* el usuario quiere eliminar algún producto del carrito? 
		  */
		 $id_sacar;
		 $sacado;
		 if(!empty($_GET['sacarProducto']) && (($id_sacar = intval($_GET['sacarProducto'])) > 0))
		 {
			 /* eliminamos el producto del carrito */
			 $sacado = $carrito->sacarProducto($id_sacar); 
		 }		
		 $id_meter;
		 if(!empty($_GET['meterProducto']) && (($id_meter = intval($_GET['meterProducto'])) > 0) && (!isset($sacado) || (($id_meter != $id_sacar) || $sacado) ))
		 {
			 /* comprobamos que el producto existe y que
			  * NO está disponible para el usuario (no ha sido comprado previamente, no es el autor del 
			  * producto, no es gratuito, ...) */
			  $producto;
			 if(is_null($producto = Producto::buscarPorId($id_meter)))
			 {
				 throw new ProductoNoValidoException();
			 }
			 if($producto->estaDisponibleParaUsuario(Sesion::getIdUsuario()))
			 {
				 /* el producto ya lo tiene el usuario, no tiene sentido añadirlo al carrito */
			 }
			 else
			 {
				 /* añadimos el producto al carrito */
				 $carrito->meterProducto($id_meter);
			 }
		 }
		 
		 /* guardamos el carrito en la sesión */
		 Sesion::setCarrito($carrito);
			
		 
		 $plantilla = file_get_contents('plantillas/carrito.html');
		 $parser_plantilla = new ParserCarrito($carrito);
		 $documento = $parser_plantilla->parsear($plantilla);
		 echo $documento;
	 }
	 
	 /**
	  * Este método es invocado cuando el usuario quiere comprar los 
	  * productos que ha añadido al carrito. 
	  * Deberemos mostrar la típica página en la cual el usuario debe
	  * indicar su número de tarjeta, realizar el pago, ...
	  * En vez de eso, el usuario adquirirá directamente los productos 
	  * y se le mostrará la página principal.
	  * @note Después de realizar esta operación, el carrito de la compra
	  * estará vacío.
	  * @note El usuario debe estar logeado para realizar esta operación.
	  */ 
	 function checkout() 
	 {
		  if(!Sesion::estaUsuario())
		  {
			  throw new UsuarioNoValidoException();
		  }
		  
		  $usuario = Sesion::getUsuario();
		  $usuario->checkout();
		  
		  /* mostramos el índice principal */
		  verIndice();
	 }
	 
	 /**
	  * Es invocado cuando el usuario publica un producto.
	  * (en la página de alta de un nuevo producto)
	  */
	 function publicarProducto()
	 { 
		 require_once 'scripts/validador.php';
		 require_once 'scripts/producto.php';
		 require_once 'scripts/categoria.php';		
		 require_once 'scripts/almacen.php';

		 /* comprobamos que esta logeado */
		 if(!Sesion::estaUsuario())
		 {
			 throw new UsuarioNoValidoException();
		 }
		 
		 $parser_plantilla = new ParserPublicarProducto();
		 try
		 {
			 $error = false;
			 /* comprobamos los parámetros de la petición */
			 
			 /* comprobamos el nombre */
			 $nombre;
			 if(empty($_POST['nombre']) || (strlen($_POST['nombre']) > 32))
			 {
				 if(isset($_POST['nombre']))
				 {
					 $parser_plantilla->errorNombre();
				 }
				 $error = true;
			 }
			 else 
			 {
				 $nombre = $_POST['nombre'];
			 }
			 $parser_plantilla->indicarNombre(isset($_POST['nombre']) ? $_POST['nombre'] : '');
			 
			 /* comprobamos la descripción */
			 $descripcion;
			 if(isset($_POST['descripcion']) && (strlen($_POST['nombre']) > 400))
			 {
				 $parser_plantilla->errorDescripcion();
				 $error = true;
			 }
			 else 
			 {
				 $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
			 }
			 
			 $parser_plantilla->indicarDescripcion(isset($_POST['descripcion']) ? $_POST['descripcion'] : '');
			 
			 /* comprobamos el precio */
			 
			 $precio;
			 if(!isset($_POST['precio']) || !(($_POST['precio'] == 'gratis') || ($_POST['precio'] == '0') || preg_match('/([0-9]+)€/', $_POST['precio']) || (intval($_POST['precio']) > 0) ))
			 {
				 if(isset($_POST['precio']))
				 {
					 $parser_plantilla->errorPrecio($_POST['precio']);
				 }
				 else
				 {
					 $parser_plantilla->errorPrecio();
				 }
				 $error = true;
			 }
			 else 
			 {
				 $precio = $_POST['precio'];
				 $matches;
				 $precio = ($_POST['precio'] == 'gratis') ? 0 : (preg_match('/([0-9]+)€/', $_POST['precio'], $matches) ? intval($matches[1]) : intval($_POST['precio']));
				 $parser_plantilla->indicarPrecio(($precio > 0) ? ($precio . '€') : 'gratis');
			 }
			
			 
			 /* comprobamos la familia */
			 $familias = array('Modelos 3D', 'Texturas', 'HDRI');
			 if(empty($_POST['familia']) || !in_array($_POST['familia'],$familias))
			 {
				 if(isset($_POST['familia']))
				 {
					 $parser_plantilla->errorFamilia($_POST['familia']);
				 }
				 else 
				 {
					 $parser_plantilla->errorFamilia();
				 }
				 throw new Exception(); 
			 }
			 
			 $familia = $_POST['familia'];
			 $parser_plantilla->indicarFamilia($familia);
			 
			 /* comprobamos la categoría */
			 $categoria;
			 if(!isset($_POST['categoria']) || (intval($_POST['categoria']) == 0) || is_null($categoria = Categoria::buscarPorId(intval($_POST['categoria']))))
			 {
				 $parser_plantilla->errorCategoria();
				 $error = true;
			 }
			 else 
			 {
				 $parser_plantilla->indicarCategoria($categoria);
			 }
			 
			 /* Comprobación relativa a ficheros */
			 $error_fichero = false; 
			 if(!(($familia != 'HDRI') || (isset($_FILES['hdri']) && !is_array($_FILES['hdri']['error']) )) ||
			 !(($familia != 'Modelos 3D') || (isset($_FILES['modelo3d']) && !is_array($_FILES['modelo3d']['error']))) ||
			 !(($familia != 'Texturas') || (isset($_FILES['textura']) && !is_array($_FILES['textura']['error']) )))
			 {
				 if(isset($_FILES[($familia == 'Modelos 3D') ? 'modelo3d' : ($familia == 'Texturas' ? 'textura' : 'hdri')]))
				 {
					 $parser_plantilla->errorFichero();
				 }
				 $error_fichero = true;
			 }
			 
			 /* Se subió el fichero correctamente? */
			 if ((!$error_fichero) && ((($familia == 'HDRI') && !empty($_FILES['hdri']['error'])) ||
			 (($familia == 'Modelos 3D') && !empty($_FILES['modelo3d']['error'])) ||
			 (($familia == 'Texturas') && !empty($_FILES['textura']['error']))))
			 {
				 $parser_plantilla->errorSubidaFichero();
				 $error_fichero = true;
			 }
			 
			 $error = $error || $error_fichero;
			 			 
			 /* comprobaciones adicionales */
			 $error_imagenes = false;
			 if($familia == 'Modelos 3D')
			 {
				 if(!isset($_FILES['modelo3d_imagenes']))
				 {
					 $error_imagenes = true;
				 }
				 /* comprobar si alguna de las imágenes tiene error */
				 if(!$error_imagenes)
				 {
					 if(is_array($_FILES['modelo3d_imagenes']['error']))
					 {
						 /* hay varios ficheros subidos. Comprobamos que el código de error de todos es 0  */
						 if(sizeof(array_filter($_FILES['modelo3d_imagenes']['error'],function($error) { return ($error != 0); })) > 0)
						 {
							 $parser_plantilla->errorSubidaImagenes();
							 $error_imagenes = true;
						 }
					 }
					 else
					 {
						 if(!empty($_FILES['modelo3d_imagenes']['error'])) /* si solo hay un fichero, comprobamos su código de error */
						 {
							 $parser_plantilla->errorSubidaImagenes();
							 $error_imagenes = true;
						 }
					 }
				 }
				 
				 $error = $error || $error_imagenes;
			 }

			 
			 /* obtenemos el fichero que contiene el producto */
			 $fichero;
			 if(!$error_fichero)
			 {
				 $fichero = $_FILES[($familia == 'Modelos 3D') ? 'modelo3d' : ($familia == 'Texturas' ? 'textura' : 'hdri')];
			 }
			 
			 /* obtenemos las imágenes del producto */
			 /* si el producto es un modelo 3d, las imágenes serán las subidas por el usuario,
			  * en caso contrario, la imagen será el propio producto (hdri o textura) */
			 $imagenes;
			 if((($familia != 'Modelos 3D') && !$error_fichero) || (($familia == 'Modelos 3D') && !$error_imagenes))
			 {
				 $imagenes = $_FILES[($familia == 'Modelos 3D') ? 'modelo3d_imagenes' : (($familia == 'Texturas') ? 'textura' : 'hdri')];
			 }

			 $validador_fichero = ($familia == 'Modelos 3D') ? new ValidadorModelo3D() : new ValidadorImagen();

			 /* validamos el fichero que contiene el producto */
			 if(!$error_fichero && !$validador_fichero->esValido($fichero['tmp_name']))
			 {
				 $parser_plantilla->errorFicheroNoValido();
				 $error = true;
			 }
			 
			 /* validamos las imágenes del producto (si es un modelo 3D) */
			 if(($familia == 'Modelos 3D') && !$error_imagenes)
			 {
				 $validador_imagenes = new ValidadorImagen();
				 if(sizeof(array_filter($imagenes['tmp_name'], function($fichero_imagen) use($validador_imagenes) { return !$validador_imagenes->esValido($fichero_imagen); })) > 0)
				 {
					 /* alguna de las imágenes no es válida */
					 $parser_plantilla->errorImagenesNoValidas();
					 $error = true;
				 }
			 }
			 
			 /* si hubo uno/varios campos del formulario con errores, mostrar
			  * de nuevo la página de publicación del producto */
			 if($error)
			 {
				 throw new Exception();
			 }
		 }
		 catch(Exception $e)
		 {
			 /* si alguno de los parámetros no es válido, mostramos al usuario de nuevo la página
			  * de alta de producto procesada
			  */ 
			 $plantilla = file_get_contents('plantillas/publicar.html');
			 $documento = $parser_plantilla->parsear($plantilla);
			 echo $documento;
			 return;
		 }
		 
		 /* registrar el producto */
		 $producto = Producto::registrar($nombre, $descripcion, $precio, Sesion::getIdUsuario(), $categoria, $fichero, $imagenes); 
		 /* mostramos la página de visualización de producto */
		 verProducto($producto);
	 }
	 
	 function accionInvalida()
	 {
		 /* llevarle a la página principal */
		 verIndice(); 
	 }
	 
	 /**
	  * Muestra la puerta de atrás. (Cuando el usuario va a un sitio sin logearse y que requiere autenticación)
	  * Le mostramos un panel de login. Al logearse correctamente, irá a la página previa (a esta página se enviarán todos
	  * los parámetros anteriores pasados por _GET.
	  */
	 function mostrarBackDoor()
	 {
		 $plantilla = file_get_contents('plantillas/backdoor/login.html');
		 $parser_plantilla = new ParserBackDoor();
		 $documento = $parser_plantilla->parsear($plantilla);
		 echo $documento;
	 }
	 
	 /**
	  * Muestra una página que indica que el sitio al que quiere acceder no está disponible 
	  */
	 function sitioNoDisponible() 
	 {
		  $plantilla = file_get_contents('plantillas/sitionodisponible.html');
		  $parser_plantilla = new ParserSitioNoDisponible();
		  $documento = $parser_plantilla->parsear($plantilla);
		  echo $documento;
	 }
	 
	 /**
	  * Se invoca cuando se ha producido un error en el servidor inesperado (fallo al conectar con la base 
	  * de datos, ...)
	  */
	 function errorInesperado()
	 {
		$plantilla = file_get_contents('plantillas/error.html');
		$parser_plantilla = new ParserError();
		$documento = $parser_plantilla->parsear($plantilla);
		echo $documento;
	 }
	 
	 
	 try
	 {
		 /* validamos los parámetros de la petición */
		 if(!isset($_GET['accion']))
		 {
			 verIndice();
			 return;
		 }
		 
		 /* comprobamos que acción quiere realizar el usuario */
		 $accion = $_GET['accion'];
		 switch($accion)
		 {
			 case 'verProducto':
			 verVisorProducto();
			 break;
			 
			 case 'verPerfil':
			 verPerfil();
			 break;
			 
			 case 'verFamilia':
			 verCatalogoCategorias();
			 break;
			 
			 case 'verCategoria':
			 verCatalogoProductos();
			 break;
			 
			 case 'publicarProducto':
			 publicarProducto();
			 break;
			 
			 case 'verCarrito':
			 verCarrito();
			 break;
			 
			 case 'checkout':
			 checkout();
			 break;
			 
			 default:
			 accionInvalida();
		 }
	 }
	 catch(MySQLException $e)
	 {
		 errorInesperado(); 
	 }
	 catch(UsuarioNoValidoException $e)
	 {
		 mostrarBackDoor(); 
	 }
	 catch(UsuarioNoEncontradoException $e)
	 {
		 sitioNoDisponible();
	 }
	 catch(ProductoNoValidoException $e)
	 {
		 sitioNoDisponible();
	 }
	 catch(CategoriaNoValidaException $e)
	 {
		 sitioNoDisponible();
	 }
	 catch(PaginaCatalogoProductosNoValida $e)
	 {
		 sitioNoDisponible();
	 }
	 catch(FamiliaNoValidaException $e)
	 {
		 sitioNoDisponible();
	 }
	 catch(Exception $e)
	 {
		 errorInesperado();
	 }
?>
