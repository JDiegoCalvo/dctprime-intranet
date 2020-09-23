check_cookie();

var pago     = 0;
var facturas = 0;
var related = [];

function actualizar_pago()
{
	pago = document.querySelector( '#seleccionar_pago' ).value.split( ',' )[0];

	document.querySelector( '#importe_del_pago' ).innerHTML = numeral( pago ).format( '$0,0.00' );

	actualizar_saldo();
}

function actualizar_facturas( nuevo_saldo, ID, uuid )
{
	if ( document.querySelector( '#cb_saldo_' + ID ).checked )
	{
		facturas += nuevo_saldo;

		related.push({
			uuid         : uuid,
			installment  : 1,
			last_balance : nuevo_saldo,
			amount       : nuevo_saldo
		})
	}else
	{
		facturas -= nuevo_saldo;
	}

	document.querySelector( '#suma_de_facturas' ).innerHTML = numeral( facturas ).format( '$0,0.00' );

	actualizar_saldo();
}

function actualizar_saldo() 
{
	document.querySelector( '#saldo_total' ).innerHTML = numeral( pago - facturas ).format( '$0,0.00' );
}

function actualizar_pantalla()
{
	facturas_pendientes();

	pagos_recibidos();
}

function facturas_pendientes()
{
	if ( document.querySelector( '#customer' ).value !== 'Selecciona' )
	{
		var data = new FormData();
			data.append( 'cliente', document.querySelector( '#customer' ).value );

		fetch( 'scripts/facturas_pendientes.php', {
			method : 'POST',
			body   : data
		})
		.then( res => res.json() )
		.then( r   => 
		{
			document.querySelector( '#table_facturas_pendientes tbody' ).innerHTML = '';

			for ( var i = 0; i < r.length; i++ )
			{
				document.querySelector( '#table_facturas_pendientes tbody' ).innerHTML += `
					<tr>
						<td>${ r[i].fecha }</td>
						<td class="text-right">${ numeral( r[i].importe ).format( '$0,0.00' ) }</td>
						<td>
							<input type="checkbox" onclick="actualizar_facturas( ${ r[i].importe }, '${ i }', '${ r[i].uuid }' );" id="cb_saldo_${ i }">
						</td>
					</tr>
				`;
			}
		});
	}
}

function pagos_recibidos()
{
	var data = new FormData();
		data.append( 'cliente', document.querySelector( '#customer' ).value );

	fetch( 'scripts/pagos_recibidos.php', {
		method : 'POST',
		body   : data
	})
	.then( res => res.json() )
	.then( r   => 
	{
		document.querySelector( '#seleccionar_pago' ).innerHTML = '<option>Selecciona</option>';

		for ( var i = 0; i < r.length; i++ )
		{
			document.querySelector( '#seleccionar_pago' ).innerHTML += `
				<option value="${ r[i].importe },${ r[i].ID }">${ r[i].fecha } - ${ numeral( r[i].importe ).format( '$0,0.00' ) }</option>
			`;
		}
	});
}

document.querySelector( '#generar_pago_btn' ).addEventListener( 'click', function( e )
{
	var value = document.querySelector( '#seleccionar_pago' ).value;
	var ID = value.split( ',' )[1];

	var data = new FormData();
		data.append( 'customer', document.querySelector( '#customer' ).value );
		data.append( 'ID', ID );

		for ( var i = 0; i < related.length; i++ )
		{
			data.append( 'related['+i+'][uuid]', related[i].uuid )
			data.append( 'related['+i+'][installment]', related[i].installment )
			data.append( 'related['+i+'][last_balance]', related[i].last_balance )
			data.append( 'related['+i+'][amount]', related[i].amount )
		}

	fetch( 'scripts/generar_complemento_de_pago.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( r => 
	{
		pago = 0;
		facturas = 0;
		document.querySelector( '#importe_del_pago' ).innerHTML = numeral( pago ).format( '$0,0.00' );
		document.querySelector( '#suma_de_facturas' ).innerHTML = numeral( facturas ).format( '$0,0.00' );
		actualizar_saldo();

		facturas_pendientes();

		pagos_recibidos();

		alert( 'Complemento de pago generado.' );
	});
});

function init()
{
	var a_select = document.querySelectorAll( '.a_select' );

	for ( var i = 0; i < a_select.length; i++ ) 
	{
		a_select[i].addEventListener( 'change', function( event ) 
		{
			contar_XML();

			leer();
		});
	}

	fetch( 'scripts/init.php' )
	.then( res => res.json() )
	.then( r   => 
	{
		document.querySelector( '#clientes' ).innerHTML += `
			<option>Selecciona</option>
		`;

		for ( var i = 0; i < r.length; i++ )
		{
			document.querySelector( '#clientes' ).innerHTML += `
				<option value="${ r[i].RFC }">${ r[i].cliente }</option>
			`;
		}

		document.querySelector( '#customer' ).innerHTML += `
			<option>Selecciona</option>
		`;

		for ( var i = 0; i < r.length; i++ )
		{
			document.querySelector( '#customer' ).innerHTML += `
				<option value="${ r[i].conekta }">${ r[i].cliente }</option>
			`;
		}
	});
}

function ventas()
{
 	document.getElementById( 'root_div' ).classList.add( 'd-none' );
	document.getElementById( 'loader' ).classList.remove( 'd-none' );

	var myHeaders = new Headers();
		myHeaders.append( 'pragma', 'no-cache' );
		myHeaders.append( 'cache-control', 'no-cache' );

	fetch( 'scripts/ventas.php', {
		method: 'GET',
		headers: myHeaders,
	})
	.then( res => res.json() )
	.then( r   => 
	{
		document.querySelector( '#table_ventas tbody' ).innerHTML = '';
		console.log(r)
		for ( var i = 0; i < r.length; i++ )
		{
			document.querySelector( '#table_ventas tbody' ).innerHTML += `
				<tr>
					<td>${ r[i].fecha }</td>
					<td>${ r[i].cliente }</td>
					<td class="text-right">${ numeral( r[i].importe ).format( '$0,0.00' ) }</td>
					<td class="text-center">
						<a href="whatsapp://send?text=${ r[i].tratamiento } dctprime.com/l/${ r[i].hash }.pdf&phone=${ r[i].telefono }" class="btn btn-link"><i class="fab fa-whatsapp"></i></a>
					</td>
					<td class="text-center"><i class="far fa-file-alt" onclick="test();"></i></td>
					<td class="text-center"><i class="fas fa-download"></i></td>
				</tr>
			`;
		}

	 	document.getElementById( 'root_div' ).classList.remove( 'd-none' );
		document.getElementById( 'loader' ).classList.add( 'd-none' );
	});
}

function leer()
{
	var data = new FormData();
		data.append( 'cliente', document.querySelector( '#clientes' ).value );
		data.append( 'ejercicio', document.querySelector( '#ejercicio' ).value );
		data.append( 'periodo', document.querySelector( '#periodo' ).value );

	fetch( 'scripts/leer.php',
	{
		method : 'POST',
		body   : data
	})
	.then( res => res.json() )
	.then( r   => 
	{
		document.querySelector( '#contenido tbody' ).innerHTML = '';

		var total = 0;

		for ( var i = 0; i < r.length; i++ )
		{
			document.querySelector( '#contenido tbody' ).innerHTML += `
				<tr>
					<td><i class="far fa-trash-alt text-danger icon" data-id="${ r[i].ID }"></i></td>
					<td class="text-center">${ r[i].cantidad }</td>
					<td>${ r[i].descripcion }</td>
					<td class="text-right">${ numeral( r[i].precio ).format( '$0,0.00' ) }</td>
				</tr>
			`;

			total += r[i].precio;
		}

		document.querySelector( '#terminar_btn' ).innerHTML = 'Terminar ' + numeral( total ).format( '$0,0.00' );

		var icon = document.querySelectorAll( '.icon' );

		for ( var i = 0; i < icon.length; i++ ) 
		{
			icon[i].addEventListener( 'click', function( event ) 
			{
				const elemento = event.currentTarget;
				const datos    = elemento.dataset;

				if ( datos.id !== undefined )
				{
					eliminar( datos.id );
				}
			});
		}

		if ( document.querySelector( '#contenido tbody' ).childElementCount > 0 )
		{
			document.querySelector( '#terminar_btn' ).removeAttribute( 'disabled' );
		}else
		{
			document.querySelector( '#terminar_btn' ).setAttribute( 'disabled', '' );
		}
	});
}

function contar_XML()
{
	var data = new FormData();
		data.append( 'cliente', document.querySelector( '#clientes' ).value );
		data.append( 'ejercicio', document.querySelector( '#ejercicio' ).value );
		data.append( 'periodo', document.querySelector( '#periodo' ).value );

	fetch( 'scripts/contar_XML.php',
	{
		method : 'POST',
		body   : data
	})
	.then( res => res.text() )
	.then( r   => 
	{
		document.querySelector( '#conteo' ).value = r;
	});
}

function eliminar( ID )
{
	var data = new FormData();
		data.append( 'ID', ID );

	fetch( 'scripts/eliminar.php',
	{
		method : 'POST',
		body   : data
	})
	.then( r   => 
	{
		if ( document.querySelector( '#contenido tbody' ).childElementCount == 0 )
		{
			document.querySelector( '#terminar_btn' ).removeAttribute( 'disabled' );
		}

		leer();
	});
}

document.querySelector( '#agregar_servicio' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	fetch( 'scripts/crear.php', 
	{
		method : 'POST',
		body   : new FormData( this )
	})
	.then( r => 
	{
		document.querySelector( '#terminar_btn' ).removeAttribute( 'disabled' );

		leer();
	});
});

document.querySelector( '#terminar_btn' ).addEventListener( 'click', function()
{
	var data = new FormData();
		data.append( 'cliente', document.querySelector( '#clientes' ).value );
		data.append( 'ejercicio', document.querySelector( '#ejercicio' ).value );
		data.append( 'periodo', document.querySelector( '#periodo' ).value );

	fetch( 'scripts/terminar.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( r => 
	{
		document.querySelector( '#terminar_btn' ).setAttribute( 'disabled', '' );

		facturas_pendientes();

		leer();

		ventas();

		alert( 'Recibo creado.' );
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
				init();

				ventas();
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
