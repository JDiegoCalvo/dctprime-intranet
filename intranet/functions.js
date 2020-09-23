check_cookie();

if ( 'serviceWorker' in navigator ) 
{
	if ( navigator.serviceWorker.controller ) 
	{

	}else 
	{
		// Register the service worker
		navigator.serviceWorker
		.register( 'pwabuilder-sw.js', {
			scope: './'
		})
		.then( function ( reg ) 
		{
			// console.log( '[PWA Builder] Service worker has been registered for scope: ' + reg.scope );
		});
	}
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
 		.then( res => res.text() )
 		.then( no_cookie => 
 		{
 			if ( no_cookie )
 			{
 				window.location.href = 'https://dctprime.com/intranet/login';
 			}else
 			{
			 	document.getElementById( 'root_div' ).classList.remove( 'd-none' );
				document.getElementById( 'loader' ).classList.add( 'd-none' );
 			}
 		})
	}else
	{
		window.location.href = 'https://dctprime.com/intranet/login';
	}
}

function delete_cookie ( cname ) 
{
    return document.cookie = cname + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
}
