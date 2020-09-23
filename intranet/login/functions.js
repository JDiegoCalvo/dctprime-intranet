document.querySelector( '#login' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	fetch( 'scripts/login.php', 
	{
		method : 'POST',
		body   : new FormData( this )
	})
	.then( res => res.json() )
	.then( r   => 
	{
		if ( r.responde == 'El usuario no existe' )
		{
			document.querySelector( '#login input[name=usuario]' ).classList.add( 'is-invalid' );
			document.querySelector( '#login input[name=contraseña]' ).classList.remove( 'is-invalid' );

		}else if ( r.responde == 'La contrasena es incorrecta' )
		{
			document.querySelector( '#login input[name=usuario]' ).classList.remove( 'is-invalid' );
			document.querySelector( '#login input[name=contraseña]' ).classList.add( 'is-invalid' );

		}else if ( r.responde == 'El usuario y la contrasena son correctos' )
		{
			set_cookie ( 'sesion', r.sesion, 365 )

			document.querySelector( '#login input[name=usuario]' ).classList.remove( 'is-invalid' );
			document.querySelector( '#login input[name=contraseña]' ).classList.remove( 'is-invalid' );

			window.location.href = '../';
		}else
		{
			document.querySelector( '#login input[name=usuario]' ).classList.remove( 'is-invalid' );
			document.querySelector( '#login input[name=contraseña]' ).classList.remove( 'is-invalid' );

			alert( 'Error inesperado, intente más tarde.' );
		}
	});
});

function set_cookie ( cname, cvalue, exdays ) 
{
	var d = new Date();
	d.setTime( d.getTime() + ( exdays * 24 * 60 * 60 * 1000 ) );
	var expires = 'expires=' + d.toGMTString();

	document.cookie = cname + '=' + cvalue + ';' + expires + ';path=/';
}

function get_cookie ( cname ) 
{
	var name = cname + '=';
	var decodedCookie = decodeURIComponent( document.cookie );
	var ca = decodedCookie.split( ';' );
	for ( var i = 0; i < ca.length; i++ ) 
	{
		var c = ca[i];
		while ( c.charAt( 0 ) == ' ' ) 
		{
			c = c.substring( 1 );
		}
		if ( c.indexOf( name ) == 0 ) 
		{
			return c.substring( name.length, c.length );
		}
	}

	return '';
}

function check_cookie() 
{
	var sesion = get_cookie( 'sesion' );

	if ( sesion != '' ) 
	{
		var data = new FormData();
			data.append( 'sesion', sesion );

		fetch( 'scripts/check_cookie.php', 
		{
			method : 'POST',
			body   : data
 		})
 		.then( res => res.json() )
 		.then( no_cookie => 
 		{
 			if ( no_cookie )
 			{
 				window.location.href = 'https://dctprime.com/intranet/login';
 			}
 		})
	}else
	{
		deleteCookie( 'sesion' );
	}
}

function delete_cookie ( cname ) 
{
    return document.cookie = cname + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
}
