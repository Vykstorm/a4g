/**
 * 
 * @author Víctor Ruiz Gómez
 * @file \brief Es un script con funciones especificas para la página
 * de perfil del usuario.
 * @note Este script debe ser incluido antes de la inclusión de comun.js
 */
 
/**
 * Es una redefinición de la función usuarioLogeado creada por el script comun.js
 * Con esto definimos la acción a realizar cuando el usaurio haya cerrado sesión
 * con éxito. 
 */
usuarioLoggedOut = function() {
	/* nos vamos al índice */
	window.open('index.php', '_self');
}
