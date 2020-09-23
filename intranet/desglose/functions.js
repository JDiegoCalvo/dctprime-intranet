check_cookie();

function init()
{
	fetch( 'scripts/init.php' )
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

document.querySelector( '#seleccionar' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	var data = new FormData( this );

	fetch( 'scripts/desglose.php', 
	{
		method: 'POST',
		body  : data
	})
	.then( res => res.json() )
	.then( r   => 
	{
		var total_gravado  = 0,
		total_exento       = 0,
		total_ISR_retenido = 0;

		document.querySelector( '#ISR tbody' ).innerHTML = '';

		for ( var i = 0; i < r.CFDIs.length; i++ )
		{
			document.querySelector( '#ISR tbody' ).innerHTML += `
				<tr>
					<td>${ i + 1 }</td>
					<td>${ r.CFDIs[i].fecha }</td>
					<td>${ r.CFDIs[i].folio_fiscal }</td>
					<td>${ r.CFDIs[i].RFC }</td>
					<td class="text-right">${ numeral( r.CFDIs[i].gravado ).format( '0,0' ) }</td>
					<td class="text-right">${ numeral( r.CFDIs[i].exento ).format( '0,0' ) }</td>
					<td class="text-right">${ numeral( r.CFDIs[i].ISR_retenido ).format( '0,0' ) }</td>
				</tr>
			`;

			total_gravado      += r.CFDIs[i].gravado;
			total_exento       += r.CFDIs[i].exento;
			total_ISR_retenido += r.CFDIs[i].ISR_retenido;
		}

		document.querySelector( '#ISR tfoot' ).innerHTML = `
			<tr>
				<th class="text-right" colspan="4">Total de CFDIs</th>
				<th class="text-right">${ numeral( total_gravado ).format( '0,0' ) }</th>
				<th class="text-right">${ numeral( total_exento ).format( '0,0' ) }</th>
				<th class="text-right">${ numeral( total_ISR_retenido ).format( '0,0' ) }</th>
			</tr>
		`;

		document.querySelector( '#general' ).innerHTML = numeral( r.general ).format( '0,0' );
		document.querySelector( '#exento' ).innerHTML  = numeral( r.exento ).format( '0,0' );
		document.querySelector( '#cero' ).innerHTML    = numeral( r.cero ).format( '0,0' );
		document.querySelector( '#total' ).innerHTML   = numeral( r.total ).format( '0,0' );
		document.querySelector( '#proporcion' ).innerHTML   = numeral( r.proporcion ).format( '0,0.0000' );
		document.querySelector( '#IVA_por_acreditar' ).innerHTML = numeral( r.IVA_por_acreditar ).format( '0,0' );
		document.querySelector( '#IVA_acreditable' ).innerHTML   = numeral( r.IVA_acreditable ).format( '0,0' );
		document.querySelector( '#IVA_deducible' ).innerHTML     = numeral( r.IVA_deducible ).format( '0,0' );

		document.querySelector( '#CFDIs' ).innerHTML = numeral( total_gravado ).format( '0,0' );
		document.querySelector( '#IVA_deducible_2' ).innerHTML = numeral( r.IVA_deducible ).format( '0,0' );
		document.querySelector( '#deducciones_autorizadas' ).innerHTML = numeral( total_gravado + r.IVA_deducible ).format( '0,0' );

		document.querySelector( '#cliente_elegido' ).innerHTML = document.querySelector( '#clientes' ).options[ document.querySelector( '#clientes' ).selectedIndex ].text;

		var periodo_elegido = document.querySelector( '#periodo' ).options[ document.querySelector( '#periodo' ).selectedIndex ].text.substr( 5 );
		document.querySelector( '#periodo_y_ejercicio' ).innerHTML = periodo_elegido + ' - ' + document.querySelector( '#ejercicio' ).value;

		document.querySelector( '#elegir_cliente' ).classList.add( 'd-none' );
		document.querySelector( '#desglose' ).classList.remove( 'd-none' );

		var total_general  = 0,
		total_cero         = 0,
		total_exento       = 0,
		total_IVA_retenido = 0;

		document.querySelector( '#IVA tbody' ).innerHTML = '';

		for ( var i = 0; i < r.IVA.length; i++ )
		{
			document.querySelector( '#IVA tbody' ).innerHTML += `
				<tr>
					<td>${ i + 1 }</td>
					<td>${ r.IVA[i].fecha }</td>
					<td>${ r.IVA[i].folio_fiscal }</td>
					<td>${ r.IVA[i].RFC }</td>
					<td class="text-right">${ numeral( r.IVA[i].general ).format( '0,0' ) }</td>
					<td class="text-right">${ numeral( r.IVA[i].cero ).format( '0,0' ) }</td>
					<td class="text-right">${ numeral( r.IVA[i].exento ).format( '0,0' ) }</td>
					<td class="text-right">${ numeral( r.IVA[i].IVA_retenido ).format( '0,0' ) }</td>
				</tr>
			`;

			total_general      += r.IVA[i].general;
			total_cero         += r.IVA[i].cero;
			total_exento       += r.IVA[i].exento;
			total_IVA_retenido += r.IVA[i].IVA_retenido;
		}

		document.querySelector( '#IVA tfoot' ).innerHTML = `
			<tr>
				<th class="text-right" colspan="4">Total de CFDIs</th>
				<th class="text-right">${ numeral( total_general ).format( '0,0' ) }</th>
				<th class="text-right">${ numeral( total_cero ).format( '0,0' ) }</th>
				<th class="text-right">${ numeral( total_exento ).format( '0,0' ) }</th>
				<th class="text-right">${ numeral( total_IVA_retenido ).format( '0,0' ) }</th>
			</tr>
			<tr>
				<th class="text-right" colspan="4">IVA por acreditar</th>
				<th class="text-right">${ numeral( total_general * 0.16 ).format( '0,0' ) }</th>
				<th colspan="3"></th>
			</tr>
		`;
	});
});

document.querySelector( '#regresar_btn' ).addEventListener( 'click', function( e )
{
	document.querySelector( '#elegir_cliente' ).classList.remove( 'd-none' );
	document.querySelector( '#desglose' ).classList.add( 'd-none' );
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
 				init();
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
