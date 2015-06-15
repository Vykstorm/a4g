/**
 * @author Víctor Ruiz Gómez
 * @file \brief
 * Contiene métodos comunes que serán usados comunmente por todas las páginas
 * de la web. (Por ejemplo, para implementar la lógica de los encabezados de las páginas,
 * los paneles de alta y de registro de usuario, ...)
 */

/****** 
 * FUNCIONES PARA CAMBIAR LA VISIBILIDAD DE LOS ELEMENTOS DOM
 * @note Debe existir la clase .hidden en CSS que haga "invisible" a los elementos que sean
 * de esa clase. 
 */

/**
 * Cambia la visibilidad de un elemento.
 * @param elemento El elemento DOM.
 * @param visible Un valor booleano indicando si será visible o no.
 */
function cambiarVisibilidad(elemento, visible)
{
	var clases = elemento.className.replace(/\hidden\b/,'');
	if(visible)
	{
		elemento.className = clases;
	}
	else
	{
		elemento.className = clases + ' hidden';
	}
}


/**
 * Comprueba si es visible un elemento.
 * @param El elemento DOM.
 */
function esVisible(elemento)
{
	return (elemento.className == elemento.className.replace(/\hidden\b/,''));
}


/**
 * Es igual que cambiarVisibilidad, con el parámetro visible a true 
 */
function mostrar(elemento)
{
	cambiarVisibilidad(elemento, true);
}

/**
 * Es igual que cambiarVisibilidad, con el parámetro visible a false.
 */
function esconder(elemento)
{
	cambiarVisibilidad(elemento, false);
}



/***** 
 * MÉTODOS PARA CAMBIAR LA VISIBILIDAD DE LOS PANELES DE ALTA/LOGIN y LOS PANELES
 * DE USUARIO ANÓNIMO O LOGEADO.
 * El panel de login debe tener la id "login", y el panel de registro "alta"
 * El panel de usuarios anónimos debe identificarse por la id "anonimo". Por otra parte,
 * el panel de usuarios logeados, debe ser "logeado"
 * 
 * */

/**
 * Muestra el panel de login y esconde el panel de alta
 */
function mostrarLogin() 
{
	/* reseteamos los campos */ 
	document.getElementById('login_nombre').value = '';
	document.getElementById('login_passwd').value = '';
	esconder(document.getElementById('login_error'));
	mostrar(document.getElementById('login'));
	esconderAlta();
	mostrar(document.getElementById('fondo'));
}

/**
 * Esconde el panel del login
 */
function esconderLogin() 
{
	esconder(document.getElementById('fondo'));
	esconder(document.getElementById('login'));
}


/**
 * Muestra el panel de alta de usuario y esconde el panel de login.
 */
function mostrarAlta() 
{
	/* reseteamos los campos */
	document.getElementById('alta_nombre').value = '';
	document.getElementById('alta_passwd').value = '';
	document.getElementById('alta_repasswd').value = '';
	document.getElementById('alta_error_nombre').innerHTML = '';
	document.getElementById('alta_error_passwd').innerHTML = '';
	document.getElementById('alta_error_repasswd').innerHTML = '';
	document.getElementById('alta_error').innerHTML = '';
	esconder(document.getElementById('alta_error_nombre'));
	esconder(document.getElementById('alta_error_passwd'));
	esconder(document.getElementById('alta_error_repasswd'));
	esconder(document.getElementById('alta_error'));
	
	mostrar(document.getElementById('alta'));
	esconderLogin();
	mostrar(document.getElementById('fondo'));
}

/**
 * Esconde el panel de alta de usuario
 */
function esconderAlta() 
{
	esconder(document.getElementById('fondo'));
	esconder(document.getElementById('alta'));
}

/**
 * Es igual que llamar a esconderLogin y esconderAlta 
 */
function esconderLoginYAlta()
{
	esconder(document.getElementById('fondo'));
	esconder(document.getElementById('alta'));
	esconder(document.getElementById('login'));
}

/**
 * Muestra el panel de usuarios anonimos y esconde el de usuarios logeados. 
 */
function mostrarPanelAnonimo()
{
	mostrar(document.getElementById('anonimo'));
	esconderPanelLogeado();
}

/**
 * Esconde el panel de usuarios anónimos.
 */
function esconderPanelAnonimo()
{
 esconder(document.getElementById('anonimo'));
}
 
 /**
  * Muestra el panel de usuarios logeados y el esconde el de los usuarios anónimos 
  */
function mostrarPanelLogeado()
{
 mostrar(document.getElementById('logeado'));
 esconderPanelAnonimo();
}

/**
 * Esconde el panel de usuarios logeados 
 */
function esconderPanelLogeado()
{
 esconder(document.getElementById('logeado'));
}


/***** 
 * MÉTODOS PARA ACTUALIZAR LA PÁGINA CUANDO EL USUARIO SE LOGEA/REGISTRA O CIERRA 
 * SESIÓN. 
 * */
/**
 * Debe invocarse cuando el usuario se ha registrado o logeado con éxito.
 */
function usuarioLogeado() 
{
	esconderAlta();
	esconderLogin();
	mostrarPanelLogeado();
}

/**
 * Es un método auxiliar. 
 * Este método comprueba si el panel de usuario logeado es visible.
 * (nos dice si el usuario está logeado o no) 
 */ 
function estaUsuarioLogeado() 
{
	return esVisible(document.getElementById('logeado'));
}

/**
 * Debe invocarse cuando el usuario ha cerrado sesión con éxito
 */
function usuarioLoggedOut()
{
	mostrarPanelAnonimo();
}

/**
 * Debe invocarse cuando el usuario quiere cerrar sesión.
 */
function usuarioLogout() 
{
	/* enviar petición ajax al servidor para cerrar sesión. Si la petición no es exitósa(servidor caído), no importa;
	 * El cliente creerá que ha cerrado sesión. Después de esto, podrá registrarse o logearse de nuevo (si el servidor ya no está
	 * caído). 
	 */
	crearPeticionHttpAjax('POST', 'logout.php', 
		function(readyState, status, responseText)
		{
			if((readyState == 4) && (status == 200))
			{
				usuarioLoggedOut();
			}
		});
}


/***** 
 * MÉTODOS PARA IMPLEMENTAR LA LÓGICA DEL FORMULARIO DE REGISTRO 
 * Debe haberse incluido en el html, el script "ajax.js", antes de la inclusión de este
 * script.
 * // Las ids de los campos deben ser las siguientes: "alta_nombre", "alta_passwd" y "alta_repasswd"
 * // Las ids de las etiquetas asociadas a las campos deben ser las siguientes: "etiq_alta_nombre", "etiq_alta_passwd", "etiq_alta_repasswd"
 * Habrá un campo de error cuya id es "alta_error" que se actualizará cuando alguno de los campos se valide y se compruebe
 * que que es incorrecto. Establecer a la clase "error" para indicar que alguno de los campos no es válido.
 * La id del botón que da de alta al usuario es "enviarAlta"
 * 
 * Al principio, los campos no son válidos. Tendrán el atributo "valido" a "no"
 * */
 
/**
 * Actualiza el estado del panel de alta de usuario. Si todos los campos del formulario son
 * válidos, se activa el botón de "Enviar formulario"
 */
function actualizarAlta() 
{
	if((document.getElementById('alta_nombre').getAttribute('valido') == 'si') && (document.getElementById('alta_passwd').getAttribute('valido') == 'si') && 
	(document.getElementById('alta_repasswd').getAttribute('valido') == 'si'))
	{
		// si todos los campos del formulario son válidos...
		document.getElementById('enviarAlta').disabled = false;
	}
	else
	{
		document.getElementById('enviarAlta').disabled = true;
	}
}

/**
 * Marca como erroneo, el campo nombre del formulario de alta. 
 * @param error Es un valor booleano indicando si el nombre es erroneo o no.
 * @param msg Solo es necesario especificarlo si error=false
 */
function nombreErroneo(error, msg) 
{
	if(error)
	{
		document.getElementById('alta_error_nombre').innerHTML = msg;
		mostrar(document.getElementById('alta_error_nombre'));
	}
	else
	{
		esconder(document.getElementById('alta_error_nombre'));
	}
}

/**
 * Es igual que el anterior solo que para el campo contraseña.
 */
function passwdErronea(error, msg)
{
	if(error)
	{
		document.getElementById('alta_error_passwd').innerHTML = msg;
		mostrar(document.getElementById('alta_error_passwd'));
	}
	else
	{
		esconder(document.getElementById('alta_error_passwd'));
	}
}

/**
 * Lo mismo que el anterior, pero para la repetición de la contraseña
 */
function repasswdErronea(error, msg)
{
	if(error)
	{
		document.getElementById('alta_error_repasswd').innerHTML = msg;
		mostrar(document.getElementById('alta_error_repasswd'));
	}
	else
	{
		esconder(document.getElementById('alta_error_repasswd'));
	}
}

function errorAlta(msg)
{
	// error del servidor.
	document.getElementById('alta_error').innerHTML = msg;
	mostrar(document.getElementById('alta_error'));
}

/**
 * Es invocado cuando debe validarse el nombre del formulario de alta de usuario.
 */
function validarNombre()
{
		/* validación (parte del servidor via ajax) */
		var nombre = document.getElementById('alta_nombre').value;
		
		crearPeticionHttpAjax('POST', 'alta.php?accion=validar&campo=nombre', 
			function(readyState, status, responseText)
			{
				if(readyState == 4)
				{
					if(status == 404)
						errorAlta('Servidor no disponible');
					else 
					{
						if(responseText == 'OK') /* el campo es válido */
						{
							document.getElementById('alta_nombre').setAttribute('valido', 'si');
							nombreErroneo(false); /* actualizar el mensaje de la etiqueta del campo */
						}
						else /* el campo es inválido, la respuesta contiene el mensaje de error */
						{
							document.getElementById('alta_nombre').setAttribute('valido', 'no');
							nombreErroneo(true, responseText);
						}
						
						actualizarAlta();
					}
				}
			}, 'nombre=' + nombre);
}

/**
 * Es invocado cuando debe validarse la password del formulario de alta de usuario.
 */
function validarPasswd() 
{
		/* validación (parte del servidor via ajax) */
		var passwd = document.getElementById('alta_passwd').value;
		
		crearPeticionHttpAjax('POST', 'alta.php?accion=validar&campo=passwd', 
			function(readyState, status, responseText)
			{
				if(readyState == 4)
				{
					if(status == 404)
						errorAlta('Servidor no disponible');
					else 
					{						
						if(responseText == 'OK')
						{
							document.getElementById('alta_passwd').setAttribute('valido', 'si');
							passwdErronea(false);						
							
						}
						else /* la respuesta contiene el mensaje de error */
						{
							document.getElementById('alta_passwd').setAttribute('valido', 'no');
							passwdErronea(true, responseText);
						}
						
						validarRepasswd();
						actualizarAlta();
					}
				}
			}, 'passwd=' + passwd);
}

function validarRepasswd() 
{
	var repasswd = document.getElementById('alta_repasswd').value;
	var passwd = document.getElementById('alta_passwd').value;
	if(passwd == repasswd) /* contraseñas coinciden */
	{
		document.getElementById('alta_repasswd').setAttribute('valido', 'si');
		repasswdErronea(false);
	}
	else  /* contraseñas no coinciden */
	{
		document.getElementById('alta_repasswd').setAttribute('valido', 'no');
		repasswdErronea(true, 'Las contraseñas no coinciden');
	}
	
	actualizarAlta();
}


/**
 * Esta función debe ser invocada si el usuario inserta o elimina algún caracter del campo nombre
 * del formulario de alta.
*/
var tmout_nombre = false;
function nombreModificado() 
{
	var nombre = document.getElementById('alta_nombre').value;
	if(tmout_nombre !== false)
		clearTimeout(tmout_nombre);
	tmout_nombre = setTimeout(function() { tmout_nombre=false; validarNombre(); }, (nombre.length < 5) ? 500 : 50);
}

/**
 * Igual que el método anterior, solo que para el campo contraseña 
 */
 var tmout_passwd = false;
function passwdModificada()
{
	if(tmout_passwd !== false)
		clearTimeout(tmout_passwd);
	tmout_passwd = setTimeout(function() { tmout_paswd=false; validarPasswd(); }, 100);	
}

/** 
 * Igual que el método anterior, solo que para el campo de reptición de la contraseña 
 */
var tmout_repasswd = false;
function repasswdModificada()
{
	if(tmout_repasswd !== false)
		clearTimeout(tmout_repasswd);
	tmout_repasswd = setTimeout(function() { tmout_repaswd=false; validarRepasswd(); }, 100);	
}


/**
 * Es invocado cuando el usuario se da de alta (todos los campos del formulario han sido
 * válidados previamente, pero aún así, el registro puede no ser exitóso) */
function enviaAlta()
{
	var nombre = document.getElementById('alta_nombre').value;
	var passwd = document.getElementById('alta_passwd').value;
	crearPeticionHttpAjax('POST', 'alta.php?accion=alta', 
		function(readyState, status, responseText)
		{
			if(readyState == 4)
			{
				if(status == 404)
					errorAlta('Servidor no disponible');
				else 
				{
					if(responseText == 'OK')
					{
						/* registrado y logeado correctamente */
						usuarioLogeado();
					}
					else /* la respuesta contiene el mensaje de error */
					{
						errorAlta(responseText);
					}
				}
			}
		}, 'nombre=' + nombre + '&passwd=' + passwd);
}




/***** 
 * MÉTODOS PARA IMPLEMENTAR LA LÓGICA DEL FORMULARIO DE INICIO DE SESIÓN:
 * Los campos deben tener las siguientes ids: "login_nombre", "login_passwd", "recordar_nombre"
 * Debe existir una etiqueta que muestre el error en caso de que el usuario introduzca
 * algún campo no válido, cuya id será "login_error"
 */
 
 
/**
 * Se invoca cuando se produjo un error en el inicio de sesión del usuario (normalmente 
 * porque el nombre o la contraseña son incorrectos)
 */
function errorLogin(msg)
{
	document.getElementById('login_error').innerHTML = msg;
	mostrar(document.getElementById('login_error'));
}
 
/**
 * Debe invocarse cuando el usuario intente iniciar sesión 
 */
function enviaLogin()
{
	var nombre = document.getElementById('login_nombre').value;
	var passwd = document.getElementById('login_passwd').value;
	var recordar = document.getElementById('recordar_nombre').checked ? 'si' : 'no';
	
	crearPeticionHttpAjax('POST', 'login.php', 
		function(readyState, status, responseText)
		{
			if(readyState == 4)
			{
				if(status == 404)
					errorAlta('Servidor no disponible');
				else 
				{
					if(responseText == 'OK')
					{
						/* logeado correctamente */
						usuarioLogeado();
					}
					else /* la respuesta contiene el mensaje de error */
						errorLogin(responseText);
				}
			}
		}, 'nombre=' + nombre + '&passwd=' + passwd + '&recordar=' + recordar);
}



