check_cookie();

function clientes_()
{
	document.getElementById( 'root_div' ).classList.add( 'd-none' );
	document.getElementById( 'loader' ).classList.remove( 'd-none' );
	
	fetch( 'scripts/clientes.php' )
	.then( res => res.json() )
	.then( r   => 
	{
		for ( var i = 0; i < r.length; i++ )
		{
			document.querySelector( '#clientes' ).innerHTML += `
				<option value="${ r[i].RFC }">${ r[i].cliente }</option>
			`;
		}

		document.getElementById( 'root_div' ).classList.remove( 'd-none' );
		document.getElementById( 'loader' ).classList.add( 'd-none' );
	});
}

document.querySelector( '#init_form' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	var data = new FormData( this );

	fetch( 'scripts/init.php', {
		method : 'POST',
		body   : data
	})
	.then( res => res.json() )
	.then( r => 
	{
		var HTML = '';

		HTML += `
			<p class="font-weight-bold text-primary mb-0">${ r.nombre }</p>
			<p class="font-weight-bold text-primary mb-0">Balanza de comprobación</p>
			<p class="font-weight-bold text-primary mb-0">${ r.fecha }</p>
			<p class="font-weight-bold text-primary mb-0">Cantidades monetarias expresadas en Pesos Mexicanos</p>
			<hr class="bg-success hr-custom">
			<table class="table table-sm table-bordered table-hover font-size-sm mb-5">
				<thead>
					<tr>
						<th>Nivel</th>
						<th>Código agrupador</th>
						<th>Nombre de la cuenta y/o subcuenta</th>
						<th>Saldo inicial</th>
						<th>Debe</th>
						<th>Haber</th>
						<th>Saldo final</th>
					</tr>
				</thead>
				<tbody>`;

					var debe  = 0;
					var haber = 0;

					for ( var i = 0; i < r.cuenta.length; i++ )
					{
						if ( r.cuenta[i].nivel == '1'  )
						{
							HTML += `
								<tr>
									<td class="font-weight-bold text-center">${ r.cuenta[i].nivel }</td>
									<td class="font-weight-bold text-center">${ r.cuenta[i].codigo }</td>
									<td class="font-weight-bold">${ r.cuenta[i].nombre }</td>
									<td class="font-weight-bold text-right">${ numeral( r.cuenta[i].saldo_inicial ).format( '0,0.00' ) }</td>
									<td class="font-weight-bold text-right">${ numeral( r.cuenta[i].debe ).format( '0,0.00' ) }</td>
									<td class="font-weight-bold text-right">${ numeral( r.cuenta[i].haber ).format( '0,0.00' ) }</td>
									<td class="font-weight-bold text-right">${ numeral( r.cuenta[i].saldo_final ).format( '0,0.00' ) }</td>
								</tr>
							`;
						}else if ( r.cuenta[i].nivel == '2'  )
						{
							HTML += `
								<tr>
									<td class="text-center">${ r.cuenta[i].nivel }</td>
									<td class="text-center">${ r.cuenta[i].codigo }</td>
									<td class="pl-3 font-italic">${ r.cuenta[i].nombre }</td>
									<td class="text-right">${ numeral( r.cuenta[i].saldo_inicial ).format( '0,0.00' ) }</td>
									<td class="text-right">${ numeral( r.cuenta[i].debe ).format( '0,0.00' ) }</td>
									<td class="text-right">${ numeral( r.cuenta[i].haber ).format( '0,0.00' ) }</td>
									<td class="text-right">${ numeral( r.cuenta[i].saldo_final ).format( '0,0.00' ) }</td>
								</tr>
							`;
						}

						debe  += r.cuenta[i].debe;
						haber += r.cuenta[i].haber;
					}

				HTML += `</tbody>
				<thead>
					<tr>
						<th colspan="3"></th>
						<th class="text-right">${ 0.00 }</th>
						<th class="text-right">${ numeral( debe ).format( '0,0.00' ) }</th>
						<th class="text-right">${ numeral( haber ).format( '0,0.00' ) }</th>
						<th class="text-right">${ 0.00 }</th>
					</tr>
				</thead>
			</table>

		`;

		document.querySelector( '#contenido' ).innerHTML = HTML;
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
 				clientes_();
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
