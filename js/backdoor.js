/**
 * @author Víctor Ruiz Gómez
 * @file \brief Implementa lógica adicional para la página del backdoor.
 * 
 */

/**
 * Cuando el usuario se logee correctamente, vamos a la página previa
 */
usuarioLogeado = function() {
	document.getElementById('pagina_previa').submit();
}
