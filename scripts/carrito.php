<?php
	/**
	 * @author Víctor Ruiz Gómez
	 * @file \brief Script que define una clase que permite añadir/eliminar y obtener
	 * los productos que están dentro del carrito actual de la compra
	 */
	 
	require_once 'producto.php';
	require_once 'DBMySQLQueryManager.php';
	
	/* esta constante define el número máximo de productos que puede haber en el carrito
	 */
	define('MaxItemsCarrito', 7); 
	
	class Carrito 
	{	
		/* Acciones */
		/**
		 * Mete un nuevo producto en el carrito.
		 * @return Devuelve true si el producto se metió en el carrito correctamente.
		 * Devuelve falso si el producto ya estaba metido previamente en el carrito 
		 * @throws Lanza una excepción en el caso de que el carrito exceda el número límite
		 * de productos en este. 
		 */
		public function meterProducto($id)
		{
			if(in_array($id, $this->items))
			{
				return false;
			}
			if(MaxItemsCarrito <= $this->getNumProductos())
			{
				throw new Exception('No se pueden meter tantos productos en el carrito');
			}
			$this->items[] = $id;
			$this->productos = NULL;
			return true;
		}
		
		/**
		 * Saca un producto del carrito.
		 * @return Devuelve true si el producto se sacó del carrito correctamente. Devuelve false,
		 * si el producto no estaba en el carrito.
		 */
		public function sacarProducto($id)
		{
			if(!in_array($id, $this->items))
			{
				return false;
			}
			$key = array_search($id, $this->items);
			unset($this->items[$key]);
			$this->productos = NULL;
			return true;
		}
		
		/** 
		 * Elimina todos los productos del carrito de la compra.
		 */
		public function vaciar()
		{
			$this->items = array();
			$this->productos = NULL;
		}
		 
		/* Consultores */
		/**
		 * @return Devuelve una lista con los productos que están en el carrito, o un array vacío
		 * si el carrito está vacío.
		 */
		public function getProductos() 
		{
			if(is_null($this->productos))
			{
				if(empty($this->items))
				{
					/* no hay items */
					$this->productos = array();
				}
				else
				{
					/* accedemos a la bd para obtener la información de los productos */
					$this->productos = DBMySQLQueryManager::getProductosEnCarrito($this->items);
				}
			}
			return $this->productos;
		}
		
		/**
		 * @return Devuelve el número total del productos que están dentro del
		 * carrito
		 */
		public function getNumProductos()
		{
			return sizeof($this->items);
		}
		
		/**
		 * @return Devuelve el precio total del carrito; Suma de los precios de los productos
		 * que están dentro del carro.
		 */
		public function getPrecioTotal()
		{
			$productos = $this->getProductos();
			$total = 0;
			foreach($productos as $producto)
			{
				$total += $producto->getDetalles()->getPrecio();
			}	
			return $total;
		}
		
		/* Función mágica para almacenar solo las ids de los productos al serializar */
		public function __sleep() 
		{
			return array('items');
		}
	
		
		/* Atributos */
		public $items = array();
		public $productos = NULL;
	}
?>
