<?php
	/**
	 * @author Victor Ruiz Gómez
	 * @file \brief
	 * Script que define una clase que permitirá procesar texto 
	 * (las plantillas de la web).
	 * El texto en las plantillas de la forma ##etiqueta##, se reemplazan por valores
	 * concretos. 
	 * El código HTML entre las marcas <!-- %%MARCA%% --> y <!-- FIN%%MARCA%% --> 
	 * se podrá repetir 0, 1 o más veces. Dentro de estas marcas, también podrán colocarse
	 * etiquetas de la forma ##etiqueta##
	 */
	define('DEPURAR_PLANTILLAS', true); 
	/* esta etiqueta debe establecerse a true si se quiere
	depurar las plantillas (fase de producción). O a false, si se quiere evitar comprobaciones adicionales y 
	* optimizar la lectura de la plantilla */
	// define('DEPURAR_PLANTILLAS', false);
	 
	/**
	 * Es un parseador de texto.
	 */
	class Parser 
	{
		/* Constructor */
		/**
		 * Constructor.
		 */
		public function __construct() 
		{
			$this->etiquetas = array();
			$this->etiquetas_globales = array();
			$this->marcas = array();
		}
		
		
		/* Acciones */
		/**
		 * Reemplaza una o varias etiquetas por valores concretos.
		 * @param reemplazos Es un array asociativo donde lo indices son los
		 * nombres de las etiquetas a reemplazar, y los valores son los valores
		 * por los que estas se reemplazan. Pueden ser cadenas de texto o 
		 * funciones que al ser invocadas, devuelvan el texto que será usado para sustituirlas. 
		 * @note Estos reemplazos solo se aplican a las etiquetas que no están 
		 * dentro de marcas. Para reemplazar etiquetas de forma global, usar
		 * la función reemplazarEtiquetasGlobales. 
		 */
		public function reemplazarEtiquetas($reemplazos)
		{
			$this->etiquetas = $reemplazos + $this->etiquetas;
		}
		
		/**
		 * Reemplaza una o varias etiquetas globalmente.
		 * Es como el método reemplazarEtiquetas, solo que también se reemplazan
		 * las apariciones de las etiquetas dentro de las marcas, por los valores
		 * indicados
		*/
		public function reemplazarEtiquetasGlobales($reemplazos)
		{
			$this->etiquetas_globales = $reemplazos + $this->etiquetas_globales;
		}
		 
		/**
		 * Establece el tipo de reemplazo a realizar para una marca específica. 
		 * Puede ser un texto, o un objeto de esta misma clase (otro parser). 
		 * Si es texto, la marca se sustituirá por dicho texto. Si es otro parser, 
		 * el texto primero será procesado por el parser. El texto procesado será el
		 * que se utilize para reemplazar a la marca. 
		 * @param marca Es el nombre de la marca que se quiere reemplazar.
		 * @param reemplazo Es el parseador, que será un objeto de esta clase, o una cadena de texto. 
		 * @param n Es el número de veces que se quiere repetir la marca. Debe ser mayor o igual que 0, o una 
		 * función, que tome como parámetro la traza de parseo y devuelva un número entero mayor o igual que 0.
		 *  
		 */
		public function reemplazarMarca($marca, $parser, $n = 1)
		{
			$this->rep_marcas[$marca] = $n;
			$this->marcas[$marca] = $parser;
		}
		
		
		/**
		 * Procesa un texto con este parseador.
		 * @param texto Es el texto a procesar. 
		 * @param traza. 
		 * @return Devuelve el texto procesado 
		 */
		public function parsear($texto, $traza = array()) 
		{
			/* procesamos las etiquetas globales */ 
			$texto = self::procesarEtiquetas($this->etiquetas_globales, $texto, $traza);

			/* procesamos las marcas */
			$texto = $this->procesarMarcas($texto, $traza);
		
			/* las marcas que faltan por sustituir son inválidas, procesarlas */
			if(DEPURAR_PLANTILLAS)
				$texto = self::procesarMarcasInvalidas($texto);

			/* procesamos las etiquetas */
			$texto = self::procesarEtiquetas($this->etiquetas, $texto, $traza);
			
			/* las etiquetas que faltan por reemplazar no son válidas, procesarlas */
			if(DEPURAR_PLANTILLAS)
				$texto = self::procesarEtiquetasInvalidas($texto);
			
			return $texto;
		}
		
		private static function procesarMarcasInvalidas($texto)
		{
			/* las marcas que faltan por reemplazar no son válidas, sustituirlas por errores
			 * en el propio HTML */
			$matches;
			while(preg_match('/<!-- %%(.+)%% -->.+<!-- FIN%%\\1%% -->/', $texto, $matches))
			{
				$marca = $matches[1];
				$texto = preg_replace('/<!-- %%' . $marca . '%% -->.+<!-- FIN%%' . $marca . '%% -->/', '<span><font color="orange">Marca <q>' . $marca . '</q> no valida</font></span>', $texto);
			}
			return $texto;
		}
		
		private static function procesarEtiquetasInvalidas($texto)
		{
			/* las etiquetas invalidas las reemplazamos por un mensaje de error en el propio HTML */
			$matches;
			while(preg_match('/##([^#]+)##/', $texto, $matches))
			{
				$texto = preg_replace('/##[^#]+##/', '<span><font color="red">[Etiqueta <q>' . $matches[1] . '</q> no valida]</font></span>', $texto, 1);
			}
			return $texto;
		}

	
		private static function procesarEtiquetas($reemplazos, $texto, $traza) 
		{
			foreach($reemplazos as $etiqueta => $reemplazo) /* reemplazar cada etiqueta */
			{
				$texto = str_replace('##' . $etiqueta . '##', (is_callable($reemplazo) ? $reemplazo($traza) : $reemplazo), $texto);
			}
			return $texto;
		}
		
		private static function buscarMarca($texto, $nombre, &$trozos)
		{
			$aux = explode('<!-- %%' . $nombre . '%% -->', $texto, 2);
			if(sizeof($aux) == 1)
				return false;
			$trozos[0] = $aux[0];
			$aux = explode('<!-- FIN%%' . $nombre . '%% -->', $aux[1], 2);
			$trozos[1] = $aux[0];
			$trozos[2] = (sizeof($aux) > 1) ? $aux[1] : '';
			return true;
		}
		
		private function procesarMarcas($texto, $traza)
		{
			foreach($this->marcas as $nombre => $reemplazo) /* por cada una de las marcas */
			{
				 /* calculamos el nº veces que debe repetirse la marca */
				$rep = (is_callable($this->rep_marcas[$nombre])) ? $this->rep_marcas[$nombre]($traza) : $this->rep_marcas[$nombre];
				
				
				/* buscamos todas las apariciones de esta marca */
				$trozos; /* trozos será el resultado de cortar el texto en tres 
				(dejando arriba y abajo todo el texto que no entra en la marca) */
				
				
				while(self::buscarMarca($texto, $nombre, $trozos)) /* buscamos la marca */
				{
					$contenido = $trozos[1]; 
				
					$procesado = '';
					for($i = 0; $i < $rep; $i++) /* repetir la marca N veces */ 
					{
						array_push($traza, array('marca' => $nombre, 'rep' => $i));
						$aux = ($reemplazo instanceof Parser) ? $reemplazo->parsear($contenido, $traza) : $reemplazo;
						array_pop($traza);
						$procesado .= $aux;
					}
					
					/* reemplazamos el texto original reemplazando la etiqueta por el texto procesado */
					$texto = $trozos[0] . $procesado . $trozos[2];
				}
			}
			
			return $texto;
		}
		
		/* Atributos */
		private $etiquetas;
		private $etiquetas_globales;
		private $rep_marcas;
		private $marcas;
	}
	
	
	/* Parser que no hace nada. El texto que procesa lo devuelve tal cual */
	class DummyParser extends Parser 
	{
		public function parsear($texto, $traza = array())
		{
			return $texto;
		}
	}
	
	/**
	 * Este parser, procesa un texto de forma condicional. Si se cumple una condición,
	 * devolverá el texto tal cual, pero si no se cumple, devolverá un texto vacío
	 */
	class DummyParserCondicional extends Parser
	{
		/**
		 * Constructor.
		 * @param condicion Es la condición (una función que toma como parámetro la traza de parseo,
		 * y devuelve un valor booleano indicando si el texto debe devolverse tal cual, o debe devolverse una 
		 * cadena vacía)
		 */
		public function __construct($condicion)
		{
			parent::__construct();
			$this->condicion = $condicion;
		}
		
		public function parsear($texto, $traza = array())
		{
			$condicion = $this->condicion;
			return ($condicion($traza) ? $texto : '');
		}
		
		private $condicion;
	}
	
	
	/**
	 * Es un parser que facilita el parseo de los items de una lista
	 * (una estructura en la cual hay una marca que se repite N veces)
	 * e.g.
	 * \code
	 * $plantilla = '<h1>Esto es una lista:</h1><ul><!-- %%ITEM%% --><li>##etiqueta##</li><!-- FIN%%ITEM%% --></ul>';
	 * $parser = new Parser();
	 * $items = array(1,2,3,4,5,6); 
	 * $parser_lista = new ParserLista($parser, 'ITEM', $items);
	 * $parser_lista->reemplazarEtiquetas(array('etiqueta' => function($item) { return $item; }));
	 * echo $parser->parsear($plantilla);
	 * \endcode
	 */
	class ParserLista extends Parser 
	{
		public function __construct($parent, $marca, $items)
		{
			parent::__construct();
			
			$this->items = $items;
			$parent->reemplazarMarca($marca, $this, sizeof($items));
		}
		
		public function reemplazarEtiquetas($reemplazos)
		{
			$aux = array();
			$items = $this->items;
			foreach($reemplazos as $etiqueta => $reemplazo)
			{
				if(is_callable($reemplazo))
				{
					$aux[$etiqueta] = 
						function($traza) use($items, $reemplazo)
						{
							$item = $items[$traza[sizeof($traza)-1]['rep']];
							return $reemplazo($item);
						};
				}
				else 
				{
					$aux[$etiqueta] = $reemplazo;
				}
			}
			
			parent::reemplazarEtiquetas($aux);
		}
		
		private $items;
	}

	/**
	 * Es igual que el parser anterior solo que para parsear estructuras en las
	 * cuales hay una marca que se repite N veces, y dentro de esta, otra marca 
	 * que debe repetirse M veces.
	 * e.g
	 * 
	 */
	class ParserTabla extends Parser 
	{
		public function __construct($parent, $marca_fila, $marca_col, $num_cols, $items)
		{
			parent::__construct();
			
			$this->items = $items;
			$this->num_cols = $num_cols;
			$num_filas = ceil(sizeof($items) / $num_cols); /* número de filas de la tabla */
			$num_items = sizeof($items); /* número de items de la  tabla */
			$parser_fila = new Parser();
			$parent->reemplazarMarca($marca_fila, $parser_fila, $num_filas); 

			$parser_fila->reemplazarMarca($marca_col, $this, 
				function($traza) use($num_filas, $num_cols, $num_items) /* con esta función le indicamos cuantas veces tiene que repetir la segunda marca
				dentro de la primera, en función de que fila estamos */
				{ 
					$fila = $traza[sizeof($traza)-1]['rep']; 
					return ($fila < ($num_filas-1)) ? $num_cols : ($num_items - (($num_filas -1)*$num_cols));
				});
		}
		
		public function reemplazarEtiquetas($reemplazos)
		{
			$aux = array();
			$items = $this->items;
			$num_cols = $this->num_cols;
			foreach($reemplazos as $etiqueta => $reemplazo)
			{
				if(is_callable($reemplazo))
				{
					$aux[$etiqueta] = 
						function($traza) use($items, $reemplazo, $num_cols)
						{
							$aux = array_slice($traza, -2, 2);
							// en que fila y en que columna estamos de la tabla ? 
							$fila = $aux[0]['rep'];
							$col = $aux[1]['rep'];
							
							$item = $items[$fila * $num_cols + $col];
							return $reemplazo($item);
						};
				}
				else 
				{
					$aux[$etiqueta] = $reemplazo;
				}
			}
			
			parent::reemplazarEtiquetas($aux);
		}
		
		private $items, $num_cols;
	}	
	
	/* Parsers para las plantillas HTML */
	class ParserPlantilla extends Parser 
	{
		public function __construct()
		{
			parent::__construct();

			require_once 'cookies.php';
			require_once 'sesion.php';
			
			/* para procesar la cabecera... */
			$this->reemplazarEtiquetas(
			array( 
			'nombre_usuario' => (is_null(Cookies::getNombreUsuario()) ? '' : Cookies::getNombreUsuario()),
			'recordar_nombre' => 'true',
			'indice' => 'index.php',
			'perfil' => 'index.php?accion=verPerfil',
			'carrito' => 'index.php?accion=verCarrito',
			'buscar' => 'index.php?accion=buscarProducto'
			));
								
			$this->reemplazarMarca('USUARIO_LOGEADO', Sesion::estaUsuario() ? new DummyParser() : '');
			$this->reemplazarMarca('USUARIO_ANONIMO', Sesion::estaUsuario() ? '' : new DummyParser());
	
		}
	}
	
	/* Parser para la página principal (Índice) */
	class ParserIndice extends ParserPlantilla 
	{
		public function __construct() 
		{
			parent::__construct();
			require_once 'producto.php';
			
			/* Procesamos el pie de la página ... */
			 
			/* obtener los productos destacados de cada una de las
			 * familias */
			$modelos = Producto::getDestacados('Modelos 3D');
			$texturas = Producto::getDestacados('Texturas');
			$hdriS = Producto::getDestacados('HDRI');
			
			
			/* parser para procesar los productos destacados de la familia Modelos 3D */
			$parser_modelos = new ParserLista($this, 'MODELO3D', $modelos);
			$parser_modelos->reemplazarEtiquetas(
				array (
					'modelo3d' => function($modelo) { return 'index.php?accion=verProducto&producto=' . $modelo->getId(); },
					'imagen' => function($modelo) { return $modelo->getImagen(); }
				)
			);
			
			/* parser para procesar cada una de los productos destacados de 
			 * la familia "Texturas" */
			$parser_texturas = new ParserLista($this, 'TEXTURA', $texturas);
			$parser_texturas->reemplazarEtiquetas(
				array (
					'textura' =>  function($textura) { return 'index.php?accion=verProducto&producto=' . $textura->getId(); },
					'imagen' => function($textura) { return $textura->getImagen(); }
				)
			);
			
			/* parser para procesar cada uno de los productos destacados de 
			 * la familia "HDRI" */
			$parser_hdri = new ParserLista($this, 'HDRI', $hdriS);
			$parser_hdri->reemplazarEtiquetas(
				array (
					'hdri' => function($hdri) { return 'index.php?accion=verProducto&producto=' . $hdri->getId(); },
					'imagen' =>  function($hdri) { return $hdri->getImagen(); }
				)
			);
			
			/* para procesar el cuerpo de la página... */
		    $this->reemplazarEtiquetas(
				array(
				'familia_modelos3d' => 'index.php?accion=verFamilia&familia=Modelos 3D',
				'familia_texturas' => 'index.php?accion=verFamilia&familia=Texturas',
				'familia_hdri' => 'index.php?accion=verFamilia&familia=HDRI',
			
				));
		}
	}
	
	
	/**
	 * Esta clase procesa la plantilla de perfil de usuario.
	 */
	class ParserPerfil extends ParserPlantilla 
	{
		/** Constructor
		 * @param usuario Es el usuario del quiere verse su perfil.
		 * @param usuario_cliente Es el usuario que quiere ver el perfil (es el cliente actual, cuya
		 * id está establecida en la sesión). Este es NULL si el usuario que visita la página es anónimo.
		 * @note Si es usuario esta viendo su propio perfil, usuario y usuario_cliente serán los mismos. 
		 */
		public function __construct($usuario, $usuario_cliente)
		{
			parent::__construct();
			
			$publicados = $usuario->getProductosPublicados();

			/* procesamos alguna de las etiquetas que aparecen en el documento */
			$this->reemplazarEtiquetas(
				array(
				'nombre' => $usuario->getNombre(),
				'foto_perfil' => $usuario->getFotoPerfil(),
				));
				
			$this->reemplazarEtiquetasGlobales(
				array(
				'fecha_registro' =>  $usuario->getFechaRegistro(),
				'num_total_productos' => sizeof($publicados)
				));
				
			/* procesar la tabla de productos publicados */
			$parser_producto_publicado = new ParserLista($this, 'PRODUCTO_PUBLICADO', $publicados);
			$parser_producto_publicado->reemplazarEtiquetas(
				array(
				'imagen' => function($producto) { return $producto->getImagen(); },
				'nombre' => function($producto) { return $producto->getNombre(); },
				'producto' => function($producto) { return 'index.php?accion=verProducto&producto=' . $producto->getId(); },
				'fecha_publicacion' => function($producto) { return $producto->getDetalles()->getFechaPublicacion(); },
				'valoracion' => function($producto) { return $producto->getDetalles()->getValoracion(); }
				));

			if(!is_null($usuario_cliente) && ($usuario_cliente->getId() == $usuario->getId()))
			{
				/* el usuario visita su propio perfil */
				$parser_perfil = new Parser();
				$this->reemplazarMarca('MI_PERFIL', $parser_perfil);
				$this->reemplazarMarca('PERFIL_AJENO', '');
				$parser_perfil->reemplazarEtiquetas (
					array(
						'cambiar_foto' => 'cambiar foto',
						'publicar' => 'index.php?accion=publicarProducto'
					));

				/* procesamos la tabla de productos comprados */
				
				/* obtener los productos comprados por el usuario */
				$comprados = $usuario->getProductosAdquiridos();
				
				$parser_producto_comprado = new ParserLista($parser_perfil, 'PRODUCTO_COMPRADO', $comprados);
				$parser_producto_comprado->reemplazarEtiquetas(
					array(
					'imagen' => function($producto) { return $producto->getImagen(); }, 
					'nombre' =>  function($producto) { return $producto->getNombre(); },
					'producto' => function($producto) { return 'index.php?accion=verProducto&producto=' . $producto->getId(); }, 
					'autor' => function($producto) { return $producto->getAutor(); }, 
					'fecha_publicacion' => function($producto) { return $producto->getDetalles()->getFechaPublicacion(); }, 
					'fecha_compra' => function($producto) { return $producto->getDetalles()->getFechaPublicacion(); }
					
					));
			}
			else
			{
				/* el usuario visita la página de otro usuario */
				$this->reemplazarMarca('MI_PERFIL', '');
				$this->reemplazarMarca('PERFIL_AJENO', new DummyParser());
			}
		}
	}
		
	/*
	 * Parser para el catálogo de categorías de un producto */
	class ParserCatalogoCategorias extends ParserPlantilla
	{
		/* Constructor.
		 * @param categorias Es un listado de categorías que aparecerá en el catálogo.
		 * 
		 */
		public function __construct($familia)
		{
			parent::__construct();
			
			/* obtengo las categorías pertenecientes a esta familia */
			$categorias = Categoria::buscarPorFamilia($familia);	
			/* reemplazamos algunas de las etiquetas del documento */
			$this->reemplazarEtiquetasGlobales(
				array(
					'familia' => $familia
				));
			
			/* procesamos la tabla con el listado de categorías */
			$parser_categoria = new ParserTabla($this, 'CATEGORIAS', 'CATEGORIA', (($familia == 'HDRI') ? 1 : 4), $categorias);
			$parser_categoria->reemplazarEtiquetas(
				array(
					'categoria' => function($categoria) { return 'index.php?accion=verCategoria&categoria=' . $categoria->getId(); },
					'nombre' => function($categoria) { return $categoria->getNombre(); },
					'imagen' => function($categoria) { return $categoria->getImagen(); },
					'anchura_imagen' => function($categoria) use($familia) { return ($familia == 'HDRI') ? 512 : 256;  },
					'altura_imagen' => 256,
					'id' => function($categoria) { return $categoria->getId(); }
				));
			
			
			/* si es un usuario administrador, le permitimos crear categorías y eliminarlas */
			$usuario = Sesion::getUsuario();
			$this->reemplazarMarca('USUARIO_ADMIN', (!is_null($usuario) && $usuario->esAdmin()) ? new DummyParser() : '');	
			
			/* puede eliminar cualquiera de las categorías, a excepción de "Miscelanea" */
			$parser_categoria->reemplazarMarca('MODIFICABLE', new DummyParserCondicional(
				function($traza) use($categorias)
				{
					$categoria = $categorias[$traza[sizeof($traza)-2]['rep']];
					return ($categoria->getNombre() != 'Miscelanea');
				}));
		}
	}
	
	/**
	 * Parser para un catálogo de productos */
	class ParserCatalogoProductos extends ParserPlantilla 
	{
		/** Constructor.
		 * @param productos Es el catálogo de productos.
		 * @param num_pagina Es el número de página del catálogo que se quiere mostrar.
		 */
		public function __construct($catalogo,$num_pagina)
		{
			parent::__construct();
			
			$familia = $catalogo->getCategoria()->getFamilia();
			
			/* procesamos algunas etiquetas */
			$this->reemplazarEtiquetas(
				array(
					'categoria' => $catalogo->getCategoria()->getNombre()
				));
				
			/* procesamos el catálogo de productos */
			$productos = $catalogo->getProductosEnPagina($num_pagina);
			
			$parser_catalogo = new ParserTabla($this, 'PRODUCTOS', 'PRODUCTO', (($catalogo->getCategoria()->getFamilia() == 'HDRI') ? 2 : 4), $productos);
			$parser_catalogo->reemplazarEtiquetas(
				array(
					'imagen' => function($producto) { return $producto->getImagen(); },
					'nombre' => function($producto) { return $producto->getNombre(); },
					'producto' => function($producto) { return 'index.php?accion=verProducto&producto=' . $producto->getId(); },
					'autor' => function($producto) { return $producto->getAutor(); },
					'anchura_imagen' => function($producto) use($familia) { return ($familia == 'HDRI') ? 512 : 256;  },
					'altura_imagen' => 256
				));
			
			/* procesamos el índice para navegar por las páginas del catálogo */
			
			/* si hay página previa, no mostramos el botón de ir a página anterior */
			if($num_pagina > 0)
			{
				$parser_prev_page = new Parser();
				$parser_prev_page->reemplazarEtiquetas(
					array(
					'pagina_previa' => 'index.php?accion=verCategoria&categoria=' . $catalogo->getCategoria()->getId() . '&pagina=' .($num_pagina-1+1) . '&orden=' . $catalogo->getOrden() . '&invertir=' . ($catalogo->estaOrdenInvertido() ? 'si' : 'no')
					));
				$this->reemplazarMarca('PREV_PAGE', $parser_prev_page);
			}
			else 
			{
				$this->reemplazarMarca('PREV_PAGE', '');
			}
			
			/* si no hay página siguiente, no mostramos el botón de ir a página siguiente */
			if($num_pagina < ($catalogo->getNumPaginas() - 1))
			{
				$parser_next_page = new Parser();
				$parser_next_page->reemplazarEtiquetas(
					array(
					'pagina_siguiente' => 'index.php?accion=verCategoria&categoria=' . $catalogo->getCategoria()->getId() . '&pagina=' .($num_pagina+1+1) . '&orden=' . $catalogo->getOrden() . '&invertir=' . ($catalogo->estaOrdenInvertido() ? 'si' : 'no')
					));
					$this->reemplazarMarca('NEXT_PAGE', $parser_next_page);
			}
			else 
			{
				$this->reemplazarMarca('NEXT_PAGE', '');
			}
			
			/* ahora, procesar los indices */
			$parser_indice = new ParserLista($this, 'INDICE', range(1,$catalogo->getNumPaginas()));
			$parser_indice->reemplazarEtiquetas ( 
				array ( 
				'num_pagina' => function($indice) { return $indice; }, 
					'pagina' => function($indice) use($catalogo) { return 'index.php?accion=verCategoria&categoria=' . $catalogo->getCategoria()->getId() . '&pagina=' . $indice . '&orden=' . $catalogo->getOrden() . '&invertir=' . ($catalogo->estaOrdenInvertido() ? 'si' : 'no'); }
				));
			
			/* por último, procesar el menú que permite al usuario ordenar el catálogo */
			$this->reemplazarEtiquetas ( 
				array(
				'orden_actual' => $catalogo->getOrden(),
				'invertir_orden' => 'index.php?accion=verCategoria&categoria=' . $catalogo->getCategoria()->getId() . '&pagina=1&orden=' . $catalogo->getOrden() . '&invertir=' . ($catalogo->estaOrdenInvertido() ? 'no' : 'si'),
				'pagina_actual' => 'index.php?accion=verCategoria&categoria=' . $catalogo->getCategoria()->getId() . '&pagina=' . ($num_pagina+1) . '&invertir=' . ($catalogo->estaOrdenInvertido() ? 'si' : 'no')
				));
		}
	}
	
	
	/**
	 * Parser para procesar la plantilla de la página de visualización del carrito de la compra
	 * del usuario.
	 */
	class ParserCarrito extends ParserPlantilla 
	{
		/**
		 * @param carrito Es el carrito de la compra.
		 */
		public function __construct($carrito) 
		{
			parent::__construct();
			
			require_once 'sesion.php';
			require_once 'carrito.php';
			
			/* reemplazamos algunas etiquetas del documento */
			$this->reemplazarEtiquetas(
				array(
					'num_productos' => $carrito->getNumProductos(),
					'coste_total' => $carrito->getPrecioTotal(),
					'checkout' => 'index.php?accion=checkout'
				));
			
			/* procesamos la tabla que muestra los productos del carrito */
			$productos = $carrito->getProductos();
			$parser_productos = new ParserLista($this, 'PRODUCTO', $productos);
			$parser_productos->reemplazarEtiquetas(
				array(
					'producto' => function($producto) { return 'index.php?accion=verProducto&producto=' . $producto->getId(); },
					'imagen' => function($producto) { return $producto->getImagen(); },
					'familia' => function($producto) { return $producto->getDetalles()->getCategoria()->getFamilia(); },
					'precio' => function($producto) { return $producto->getDetalles()->getPrecio(); },
					'nombre' => function($producto) { return $producto->getNombre(); },
					'eliminar' => function($producto) { return 'index.php?accion=verCarrito&sacarProducto=' . $producto->getId(); }
				));
		}
		
	}
	
	/**
	 * Este parser procesa la página de publicación de un nuevo producto 
	 */
	class ParserPublicarProducto extends ParserPlantilla
	{
		
		public function __construct()
		{
			parent::__construct();
			
			require_once 'categoria.php';
			
			/* reemplazamos algunas de las etiquetas */
			$this->reemplazarEtiquetas(
				array(
				'publicar' => 'index.php?accion=publicarProducto'
				));
			
			/* limpiamos algunas de las etiquetas de error */
			$this->reemplazarEtiquetas(
				array(
				'error_nombre' => '',
				'error_descripcion' => '',
				'error_familia' => '',
				'error_precio' => ''
				));
			/* inicializamos un parser que procesará la segunda parte del
			 * formulario */
			$this->parser_form_extendido = new Parser();
		}
		
		/* Modificadores */ 
		public function indicarNombre($nombre) 
		{
			$this->reemplazarEtiquetas(array('nombre' => $nombre));
		}
		
		public function indicarDescripcion($descripcion)
		{
			$this->reemplazarEtiquetas(array('descripcion' => $descripcion));
		}
		
		public function indicarFamilia($familia)
		{
			$this->familia = $familia;
			$this->reemplazarMarca('MODELO3D', ($familia == 'Modelos 3D') ? new DummyParser() : '');
			$this->reemplazarMarca('TEXTURA', ($familia == 'Texturas') ? new DummyParser() : '');
			$this->reemplazarMarca('HDRI', ($familia == 'HDRI') ? new DummyParser() : '');
			$this->reemplazarMarca('FORMULARIO_EXTENDIDO', $this->parser_form_extendido);
			$this->parser_form_extendido->reemplazarEtiquetas(
				array(
				'error_categoria' => '',
				'error_textura' => '',
				'error_hdri' => '',
				'error_modelo3d' => '',
				'error_modelo3d_imagenes' => ''
				));
			$this->parser_form_extendido->reemplazarMarca('MODELO3D', ($familia == 'Modelos 3D') ? new DummyParser() : '');
			$this->parser_form_extendido->reemplazarMarca('TEXTURA', ($familia == 'Texturas') ? new DummyParser() : '');
			$this->parser_form_extendido->reemplazarMarca('HDRI', ($familia == 'HDRI') ? new DummyParser() : '');
		}
		
		public function indicarPrecio($precio)
		{
			$this->reemplazarEtiquetas(array('precio' => $precio));
		}
		
		private function indicarPrecioPorDefecto()
		{
			$this->reemplazarEtiquetas(array('precio' => 'gratis'));
		}
		
		private function getCategorias() 
		{
			if(is_null($this->categorias))
			{
				$this->categorias = Categoria::buscarPorFamilia($this->familia, true);
			}
			return $this->categorias;
		}
		
		private function indicarListadoCategorias()
		{
			/* indicamos el listado de categorías en el formulario */
			$categorias = $this->getCategorias();
			$parser_categoria = new ParserLista($this->parser_form_extendido, 'CATEGORIA', $categorias);
			$parser_categoria->reemplazarEtiquetas(
				array(
				'categoria' => function($categoria) { return $categoria->getId(); },
				'nombre' => function($categoria) { return $categoria->getNombre(); }
				));
		}
		
		public function indicarCategoria($categoria)
		{
			/* preseleccionamos la categoría seleccionada */
			$this->parser_form_extendido->reemplazarEtiquetas(array('categoria' => $categoria->getNombre()));
			
			$this->indicarListadoCategorias();
		}
		
		public function indicarCategoriaPorDefecto() 
		{
			// TODO
		}
		
		public function errorNombre()
		{
			$this->reemplazarEtiquetas(array('error_nombre' => 'Debes indicar el nombre del producto'));
		}
		
		public function errorDescripcion()
		{
			$this->reemplazarEtiquetas(array('error_descripcion' => 'La descripción es demasiado larga'));
		}
		
		public function errorFamilia($familia = NULL)
		{
			if(!is_null($familia))
			{
				$this->reemplazarEtiquetas(array('error_familia' => 'Debes indicar el tipo de producto'));
			}
			$this->reemplazarMarca('MODELO3D', '');
			$this->reemplazarMarca('TEXTURA', '');
			$this->reemplazarMarca('HDRI', '');
			$this->reemplazarMarca('FORMULARIO_EXTENDIDO', ''); /* no mostramos la segunda parte del formulario */
		}
		
		public function errorPrecio($precio = NULL) 
		{
			if(!is_null($precio))
			{
				$this->reemplazarEtiquetas(array('error_precio' => 'Debes indicar el precio del producto'));
			}
			$this->indicarPrecioPorDefecto();
		}
		
		public function errorCategoria()
		{
			$this->reemplazarEtiquetas(array('error_categoria' => 'Debes indicar el tipo de producto'));
			$this->indicarCategoriaPorDefecto();
			$this->indicarListadoCategorias();
		}
		
		public function errorFichero($mensaje = 'Debes seleccionar un archivo')
		{
			$familia = $this->familia;
			$this->parser_form_extendido->reemplazarEtiquetasGlobales(
				array(
				'error_' . (($familia == 'Modelos 3D') ? 'modelo3d' : (($familia == 'Texturas') ? 'textura' : 'hdri')) => $mensaje
				));
		}
		
		public function errorSubidaFichero() 
		{
			$this->errorFichero('Hubo un error al subir el archivo');
		}
		
		public function errorImagenes($mensaje = 'Debes seleccionar las imagenes del modelo 3D')
		{
			$this->parser_form_extendido->reemplazarEtiquetasGlobales(array('error_modelo3d_imagenes' => $mensaje));
		}
		
		public function errorSubidaImagenes()
		{
			$this->errorImagenes('Hubo un error al subir las imagenes del producto');
		}
		
		public function errorFicheroNoValido() 
		{
			$this->errorFichero('El archivo seleccionado no es válido');
		}
		
		public function errorImagenesNoValidas()
		{
			$this->errorImagenes('Alguna de las imágenes seleccionadas no es válida');
		}
		
		
		/* Atributos */
		private $parser_form_extendido, $familia;
		private $categorias = NULL;
	}
	
	
	/* Es una clase auxiliar que es usada por la clase ParserVisorProducto.
	 * Procesa los detalles de un producto en una plantilla.
	 */
	class ParserDetallesProducto extends Parser
	{
		public function __construct($parent, $producto)
		{
			parent::__construct();
			
			$detalles = $producto->getDetalles();
			$parent->reemplazarEtiquetas(
			array(
			'autor' => $producto->getAutor(),
			'valoracion' => $detalles->getValoracion(), 
			'fecha_publicacion' => $detalles->getFechaPublicacion(),
			''
			));
		}
	}
	
	/* Es una clase auxiliar para parsear los detalles de un producto 
	 * de la familia de "Modelos 3D" en una plantilla
	 */
	class ParserDetallesModelo3D extends ParserDetallesProducto 
	{
		public function __construct($parent, $modelo) 
		{
			parent::__construct($parent, $modelo);
			
			/* procesamos los detalles del modelo 3D */
			$this->reemplazarEtiquetas(
				array(
				'num_vertices' => '---',
				'num_poligonos' => '---',
				'texturas' => '---'
				));	
			
			/* procesamos las etiquetas para el carrusel de imágenes */
			$imagenes = $modelo->getImagenes();
			$this->reemplazarEtiquetas(
				array(
				'imagen' => $imagenes[0],
				'imagenes' => implode(', ', array_map(function($imagen) { return "'" . $imagen . "'"; }, $imagenes))
				));
		}
	}
	
	/* Igual que la anterior, pero para productos de la familia "Texturas" */
	class ParserDetallesTextura extends ParserDetallesProducto
	{
		public function __construct($parent, $textura)
		{
			parent::__construct($parent, $textura);
			
			/* obtenemos la extensión del archivo */
			$ruta_datos = $textura->getArchivo();
			$matches;
			preg_match('/.([^.]+)$/', $ruta_datos, $matches); 
			$extension = $matches[1];
			
			/* calculamos las dimensiones de la imagen que mostraremos
			 * (manteniendo el aspecto de la imagen)
			 */
			list($anchura_original, $altura_original) = getimagesize($ruta_datos);
			$ratio = $anchura_original / $altura_original;
			$anchura;
			$altura;
			if($anchura_original >= $altura_original)
			{
				$anchura = min(500, $anchura_original);
				$altura = $anchura / $ratio;
			}
			else 
			{
				$altura = min(500, $altura_original);
				$anchura = $altura * $ratio;
			}
			
			$this->reemplazarEtiquetas(
				array(
				'anchura_original' => $anchura_original,
				'altura_original' => $altura_original,
				'anchura' => $anchura,
				'altura' => $altura,
				'formato' => strtoupper($extension),
				'imagen' => $textura->getArchivo()
				));
		}
	}
	
	/* Lo mismo que la anterior, pero para productos de la familia "HDRI" */
	class ParserDetallesHDRI extends ParserDetallesTextura 
	{
		public function __construct($parent, $hdri) 
		{
			parent::__construct($parent, $hdri);
		}
	}
	
	
	/**
	 * Es una clase que se encarga de procesar la página de visualización de
	 * un producto */
	class ParserVisorProducto extends ParserPlantilla 
	{
		public function __construct($producto)
		{
			parent::__construct();
			/* reemplazamos algunas etiquetas y marcas */
			$this->reemplazarEtiquetas(
				array(
				'nombre' => $producto->getNombre(),
				'descripcion' => $producto->getDetalles()->getDescripcion()
				));
			$this->reemplazarEtiquetasGlobales(
				array(
				'producto' => $producto->getId(),
				'descargar' => 'product.php?accion=descargar&producto=' . $producto->getId()
				));
			$familia = $producto->getDetalles()->getCategoria()->getFamilia();
			
			$parser_modelo = ($familia == 'Modelos 3D') ? new ParserDetallesModelo3D($this, $producto) : '';
			$parser_textura = ($familia == 'Texturas') ? new ParserDetallesTextura($this, $producto) : '';
			$parser_hdri = ($familia == 'HDRI') ? new ParserDetallesHDRI($this, $producto) : '';
			$this->reemplazarMarca('MODELO3D', $parser_modelo);
			$this->reemplazarMarca('TEXTURA', $parser_textura);
			$this->reemplazarMarca('HDRI', $parser_hdri);
			
			$usuario = Sesion::getUsuario();
			
			$disponible = Sesion::estaUsuario() && $producto->estaDisponibleParaUsuario($usuario->getId());
			
			/* si el producto está disponible para el usuario, puede descargar, pero no tiene sentido comprarlo, ...*/
			$this->reemplazarMarca('COMPRAR', $disponible ? '' : new DummyParser());
			$this->reemplazarMarca('DESCARGAR', $disponible ? new DummyParser() : '');
			
			/* si está logeado y usuario no es el autor del producto, puede enviar valoración */
			if(!is_null($usuario) && ($usuario->getNombre() != $producto->getAutor()))
			{
				$parser_valoracion = new Parser();
				$this->reemplazarMarca('VALORAR', $parser_valoracion);
				$parser_valoracion->reemplazarEtiquetas(
					array(
					'valoracion' => $usuario->getValoracionDeProducto($producto)
					));
			}
			else
			{
				$this->reemplazarMarca('VALORAR', '');
			}
			
			/* ahora procesamos los comentarios de los usuarios */
			$comentarios = $producto->getComentarios();
			$parser_comentario = new ParserLista($this, 'COMENTARIO', $comentarios);
			$parser_comentario->reemplazarEtiquetas(
				array(
				'comentario' => function($comentario) { return $comentario->getTexto(); },
				'foto_perfil' => function($comentario) { return $comentario->getAutor()->getFotoPerfil(); },
				'nombre_usuario' => function($comentario) { return $comentario->getAutor()->getNombre(); }
				));
			
			/* reemplazamos más etiquetas */
			$this->reemplazarEtiquetasGlobales(
				array(
				'comentar' => 'index.php?accion=verProducto&producto=' . $producto->getId()
				));
		}
	}
	
	/**
	 * Parser para la página "backdoor"
	 */
	class ParserBackDoor extends ParserPlantilla 
	{
		public function __construct()
		{
			parent::__construct();
			
			$parametros = array();
			foreach($_GET as $clave => $valor)
			{
				$parametros[] = "$clave=$valor";
			}
			$pagina_anterior = 'index.php?' . implode('&', $parametros);
			$this->reemplazarEtiquetas(
				array(
				'pagina_anterior' => $pagina_anterior
				));
		}
	}
	
	/**
	 * Parser para la página de "sitio no disponible"
	 */
	class ParserSitioNoDisponible extends ParserPlantilla 
	{
		public function __construct()
		{
			parent::__construct();
		}
	}
	
	/**
	 * Parser para la página de error interno de la web.
	 */
	class ParserError extends ParserPlantilla 
	{
		public function __construct() 
		{
			parent::__construct();
		}
	}
?>
