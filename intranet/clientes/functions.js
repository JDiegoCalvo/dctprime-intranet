check_cookie();

function clientes()
{	
	document.getElementById( 'root_div' ).classList.add( 'd-none' );
	document.getElementById( 'loader' ).classList.remove( 'd-none' );

	var data = new FormData();

	fetch( 'scripts/clientes.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( res => res.json() )
	.then( r   => 
	{
		var HTML = '';

		for ( var i = 0; i < r.length; i++ )
		{
			HTML += `
				<tr>
					<td>${ r[i].num_servicio }</td>
					<td>${ r[i].RFC }</td>
					<td>${ r[i].nombre }</td>`;

					if ( r[i].facturapi !== '' )
					{
						HTML += `
							<td>${ r[i].facturapi }</td>
						`;
					}else
					{
						HTML += `
							<td>
								<button class="btn btn-link font-size-sm" onclick="facturapi( '${ r[i].RFC }' );">Generar Facturapi</button>
							</td>
						`;
					}

					if ( r[i].conekta !== '' )
					{
						HTML += `
							<td>${ r[i].conekta }</td>
							<td>
								<a href="whatsapp://send?text=Titular:%20CC%20Manati,%20S.A.%20De%20C.V.%0ARFC:%20CMA150512PX5%0ABanco:%20STP%0ACLABE:%20${ r[i].spei_recurrente }&phone=${ r[i].telefono }" class="btn btn-link p-0 font-size-sm">${ r[i].spei_recurrente }</a>
							</td>
						`;
					}else
					{
						HTML += `
							<td>
								<button class="btn btn-link font-size-sm" onclick="spei_recurrente( '${ r[i].RFC }' );">Generar Conekta</button>
							</td>
							<td></td>
						`;
					}

					if ( r[i].conekta !== '' )
					{
						if ( r[i].oxxo_recurrente !== '' )
						{
							HTML += `
								<td>${ r[i].oxxo_recurrente }</td>
							`;
						}else
						{
							HTML += `
								<td>
									<button class="btn btn-link font-size-sm" onclick="oxxo_recurrente( '${ r[i].RFC }' );">OXXO referencia</button>
								</td>
							`;
						}
					}else
					{
						HTML += `
							<td></td>
						`;
					}

				HTML += `</tr>
			`;

			document.querySelector( '#table_clientes tbody' ).innerHTML = HTML;

		 	document.getElementById( 'root_div' ).classList.remove( 'd-none' );
			document.getElementById( 'loader' ).classList.add( 'd-none' );
		}
	});	
}

function facturapi( RFC )
{
	var data = new FormData();
		data.append( 'RFC', RFC );

	fetch( 'scripts/facturapi.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( r   => 
	{
		clientes();

		alert( 'ApiKey de Facturapi generada.' );
	});	
}

function spei_recurrente( RFC )
{
	var data = new FormData();
		data.append( 'RFC', RFC );

	fetch( 'scripts/spei_recurrente.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( r   => 
	{
		clientes();

		alert( 'CLABE de referencia generada.' );
	});	
}

function oxxo_recurrente( RFC )
{
	var data = new FormData();
		data.append( 'RFC', RFC );

	fetch( 'scripts/oxxo_recurrente.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( r   => 
	{
		clientes();

		alert( 'Referencia de OXXO generada.' );
	});	
}

document.querySelector( '#RFC' ).addEventListener( 'keyup', function()
{
	var string = document.querySelector( '#RFC' ).value;

	document.querySelector( '#RFC' ).value = string.toUpperCase();
});

document.querySelector( '#guardar_cliente' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	fetch( 'scripts/crear.php', 
	{
		method : 'POST',
		body   : new FormData( this )
	})
	.then( r   => 
	{
		clientes();

		document.querySelector( '#RFC' ).value = '';
		document.querySelector( '#denominacion' ).value = '';
		document.querySelector( '#nombre' ).value = '';
		document.querySelector( '#primer_apellido' ).value = '';
		document.querySelector( '#segundo_apellido' ).value = '';
		document.querySelector( '#telefono' ).value = '';
		document.querySelector( '#email' ).value = '';
		document.querySelector( '#CIEC' ).value = '';
		document.querySelector( '#claveFIEL' ).value = '';

		document.querySelector( '#RFC' ).focus();
		document.querySelector( '#RFC' ).select();
	});
});

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
 				clientes();
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
