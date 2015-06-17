<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Este es un script que define un método que permite comprimir 
	 * una imágen y guardarla en el sistema de archivos
	 */
	
	/**
	 * Comprime una imagen y la guarda en el sistema de archivos 
	 * @param fuente Es la url de la imágen a comprimir.
	 * @param destino Es la url de destino de la imágen comprimida. 
	 * @param max_anchura Es la máxima anchura que permitiremos tener a la 
	 * imagen
	 * @param max_altura Es la máxima altura que permitiremos tener a la imagen.
	 * @note Esta función preserva el formato de la imagen (jpeg, png, ...)
	 * @note La imagen solo se comprime si tiene el formato jpeg o png, en cualquier
	 * otro caso, este método simplemente copia la imagen fuente y la guarda en el
	 * destino.
	 */
	function comprimirImagen($fuente, $destino, $max_anchura, $max_altura)
	{
		$mime = mime_content_type($fuente);
		if(($mime != 'image/png') && ($mime != 'image/jpeg'))
		{
			/* no comprimimos la imagen */
			if(!copy($fuente,$destino))
			{
				throw new Exception('Fallo al comprimir la imagen');
			}	
		}
		else
		{
			$imagen_fuente;
			if($mime == 'image/png')
			{
				$imagen_fuente = imagecreatefrompng($fuente);
			}
			else
			{
				$imagen_fuente = imagecreatefromjpeg($fuente);
			}
			if(!$imagen_fuente)
			{
				throw new Exception('Fallo al leer la imagen');
			}
			
			/* obtenemos las dimensiones de la imagen original */
			list($anchura_original, $altura_original) = getimagesize($fuente);
			
			/* calculamos el ratio de compresión */
			$ratio;
			if(($anchura_original <= $max_anchura) && ($altura_original <= $max_altura))
			{
				$ratio = 1;
			}
			else if($anchura_original >= $altura_original)
			{
				$ratio = $max_anchura / $anchura_original;
			}
			else 
			{
				$ratio = $max_altura / $altura_original;
			}
			
			/* calculamos las dimensiones de la imagen nueva */
			$anchura = round($anchura_original * $ratio);
			$altura = round($altura_original * $ratio);
			
			$imagen_final = imagecreatetruecolor($anchura, $altura);
			if(!$imagen_final)
			{
				throw new Exception('Fallo al crear la imagen comprimida');
			}
			
			/* copiar la imagen original a la comprimida, redimensionada */
			if(!imagecopyresampled($imagen_final, $imagen_fuente, 0, 0, 0, 0, $anchura, $altura, $anchura_original, $altura_original ))
			{
				throw new Exception('Fallo al crear la imagen comprimida');
			}
			
			
			/* guardamos la imágen */
			if($mime == 'image/jpeg')
			{
				/* además indicamos un nivel de compresión para la imagen, para preservar algo 
				 * de calidad 
				 */
				imagejpeg($imagen_final, $destino, 70);
			}
			else 
			{
				imagepng($imagen_final, $destino, 4);
			}
			
			/* destruimos las imagenes (no ficheros) */
			imagedestroy($imagen_final);
			imagedestroy($imagen_fuente);
			
		}
		
	}
?>
