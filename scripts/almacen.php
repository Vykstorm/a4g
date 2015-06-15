<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Este script define una clase que nos permite gestionar
	 * el sistema de archivos (acceder a la información estática (imágenes, ...),
	 * asociada a usuarios, productos y categorías)
	 */
	 
	require_once 'compresor.php';
	 
	/* esta constante define donde deben guardarse los directorios reservados para
	 * guardar los datos de los usuarios */
	define('REPOSITORIO_USUARIOS', 'almacen/usuarios');
	
	/* esta es igual, pero para los productos */
	define('REPOSITORIO_PRODUCTOS', 'almacen/productos');
	
	/* y por último, para las categorías */
	define('REPOSITORIO_CATEGORIAS', 'almacen/categorias');
	 
	 
	class Almacen 
	{
		/* Métodos para obtener las imagenes asociadas a los productos, categorias o 
		 * usuarios 
		 */
		 
		/**
		 * @return Devuelve la url de la imagen asociada a un producto de la web.
		 * (Si tiene varias imágenes asociadas, se devolverá solo una de ellas).
		 * Si no hay ningúna imagen asociada al producto, se lanza una excepción.
		 */
		public static function getImagenDeProdcuto($id) 
		{
			$imagenes = glob(REPOSITORIO_PRODUCTOS . "/$id/imagenes/1.*");
			
			if(sizeof($imagenes) == 0)
			{
				return 'none.png';
				//throw new Exception("No hay imagenes para el producto $id");
			}
			
			return $imagenes[0];
		}
		
		/**
		 * @return Devuelve un array con las urls de todas las imágenes asociadas a este producto,
		 * o lanza una excepción si no tiene ninguna imagen asociada. 
		 */
		public static function getImagenesDeProducto($id)
		{
			$imagenes = glob(REPOSITORIO_PRODUCTOS . "/$id/imagenes/*");
			if(sizeof($imagenes) == 0)
			{
				throw new Exception("No hay imágenes para el producto $id");
			}
			return $imagenes;
		}
		
		/**
		 * Igual que el método anterior, solo que ahora devuelve la imagen asociada a una
		 * categoría de productos
		 */
		public static function getImagenDeCategoria($id)
		{
			$imagenes = glob(REPOSITORIO_CATEGORIAS . "/$id/imagen.*");
			if(sizeof($imagenes) == 0)
			{
				return 'none.png';
				// throw new Exception("No hay imágenes para la categoría $id");
			}
			return $imagenes[0];
		}
		
		/**
		 * @return Devuelve la url de la imágen de la foto de perfil del usuario, o FALSE si no tiene asociado
		 * ninguna foto de perfil. 
		 */
		public static function getFotoPerfilDeUsuario($id)
		{
			$imagenes = glob(REPOSITORIO_USUARIOS . "/$id/foto.*");
			if(sizeof($imagenes) == 0)
			{
				return false;
			}
			$foto = $imagenes[0];
			return $foto;
		}
		
		/**
		 * @return Devuelve la url del archivo que contiene los datos del producto
		 * @param id Es la id del producto
		 */
		public static function getDatosDeProducto($id)
		{
			$archivos = glob(REPOSITORIO_PRODUCTOS . "/$id/producto.*");
			if(sizeof($archivos) == 0)
			{
				throw new Exception('Faltan los datos del producto');
			}
			$archivo = $archivos[0];
			return $archivo;
		}
		
		
		/*
		 * Métodos relacionados con el alta de productos, categorías o usuarios 
		 */
		 
		/**
		 * Almacena un usuario en el sistema de archivos (Reserva un directorio para poder
		 * guardar sus datos, ...)
		 */
		public static function registrarUsuario($usuario) 
		{
			/* reservamos un directorio para él */
			$id = $usuario->getId();
			
			$ruta = REPOSITORIO_USUARIOS . "/$id";
			if(!mkdir($ruta))
			{
				throw new Exception('Fallo al crear el directorio para el usuario');
			}
		}
		
		private static function comprimirImagenProducto($producto, $fuente, $destino)
		{
			/* en función del tipo de producto, tendremos una anchura y altura máxima determinadas */
			$familia = $producto->getDetalles()->getCategoria()->getFamilia();
			$anchura_maxima;
			$altura_maxima;
			if($familia == 'HDRI')
			{
				$anchura_maxima = 512;
				$altura_maxima = 256;
			}
			else 
			{
				$anchura_maxima = $altura_maxima = 256;
			}
			
			/* comprimimos la imágen */
			comprimirImagen($fuente, $destino, $anchura_maxima, $altura_maxima);
		}
		
		/**
		 * Igual que el método anterior, solo que para registrar un producto.
		 * @param producto Es el producto que se quiere registrar.
		 * @param datos Es la entrada de la tabla _FILES que contiene información relativa al fichero asociado
		 * al nuevo producto
		 * @param imagenes Es la entrada de la tabla _FILES que contiene información relativa de las imágenes asociadas
		 * del productos que se quiere almacenar.
		 */
		public static function registrarProducto($producto, $datos, $imagenes)
		{
			$id = $producto->getId();
			
			/* reservamos un directorio para el producto */
			$ruta = REPOSITORIO_PRODUCTOS . "/$id";
			if(!mkdir($ruta))
			{
				throw new Exception('Fallo al crear el directorio para el producto');
			}
			
			/* un subdirectorio para las imágenes */
			$ruta_imagenes = $ruta . '/imagenes';
			if(!mkdir($ruta_imagenes))
			{
				throw new Exception('Fallo al crear el directorio de imagenes para el producto');
			}
			
			/* almacenamos las imagenes del producto (comprimidas) */
			if(is_array($imagenes['name']))
			{
				for($i = 0; $i < sizeof($imagenes['name']); $i++)
				{
					$matches;
					preg_match('/.([^.]+)$/', $imagenes['name'][$i], $matches);
					$extension = $matches[1];
					self::comprimirImagenProducto($producto, $imagenes['tmp_name'][$i], $ruta_imagenes . '/' . ($i+1) . '.' . $extension);
				}
			}
			else 
			{
				$matches; preg_match('/.([^.]+)$/', $imagenes['name'], $matches);
				$extension = $matches[1];
				
				self::comprimirImagenProducto($producto, $imagenes['tmp_name'], $ruta_imagenes . '/1.' . $extension);	
			}
			
			 
			/* obtenemos la extensión del producto */
			$matches;
			preg_match('/.([^.]+)$/', $datos['name'], $matches);
			$extension = $matches[1];
			
			/* obtenemos la ruta del fichero con los datos del producto */
			$ruta_datos = $ruta . '/producto.' . $extension; 
			
			
			/* movemos el archivo temporal a la ruta anteriormente calculada */
			if(!move_uploaded_file($datos['tmp_name'], $ruta_datos))
			{
				throw new Exception('Fallo al copiar el archivo del produto');
			}
			
		}
		
		/**
		 * Igual que el método anterior. 
		 * @param categoria Es la categoria a registrar
		 * @param imagen Es la imagen que estará asociada a la nueva categoría.
		 */
		public static function registrarCategoria($categoria, $imagen)
		{
			$id = $categoria->getId();
			
			/* reservamos un directorio para la categoría */
			$ruta = REPOSITORIO_CATEGORIAS . "/$id";
			if(!mkdir($ruta))
			{
				throw new Exception('Fallo al crear el directorio para la categoría');
			}
			
			/* guardamos la imágen de la categoría comprimida */
			$matches; preg_match('/.([^.]+)$/', $imagen['name'], $matches);
			$extension = $matches[1];			
			$ruta_imagen = $ruta . '/imagen.' . $extension;
			comprimirImagen($imagen['tmp_name'], $ruta_imagen, 256, 256);
		}
		
		
		/*
		 * Métodos que modifican algunos ficheros en el sistema de archivos 
		 */
		
		public static function cambiarFotoPerfilUsuario($id, $foto)
		{
			
		}
	}
	
?>
