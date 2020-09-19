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
			console.log( '[PWA Builder] Service worker has been registered for scope: ' + reg.scope );
		});
	}
}

checkCookie();

var Numero_de_servicio = '';

var facturas = 0;
var related = [];

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

	document.querySelector( '#suma_de_facturas_btn' ).innerHTML = 'Pagar ' + numeral( facturas ).format( '$0,0.00' );
}

function setCookie ( cname, cvalue, exdays ) 
{
	var d = new Date();
	d.setTime( d.getTime() + ( exdays * 24 * 60 * 60 * 1000 ) );
	var expires = 'expires=' + d.toGMTString();

	document.cookie = cname + '=' + cvalue + ';' + expires + ';path=/';
}

function getCookie ( cname ) 
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

function checkCookie() 
{
	var user = getCookie( 'user' );

	if ( user != '' ) 
	{
		check_num_servicio();
	}else
	{
		deleteCookie( 'user' );

		mostrar( 'login_div' );
	}
}

function deleteCookie ( cname ) 
{
    return document.cookie = cname + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
}

document.querySelector( '#usuario_input' ).addEventListener( 'keyup', function( e )
{
	var string = document.querySelector( '#usuario_input' ).value;

	document.querySelector( '#usuario_input' ).value = string.toUpperCase();

	if ( document.querySelector( '#usuario_input' ).value.length >= 12 && document.querySelector( '#contraseña_input' ).value.length >= 8 )
	{
		document.querySelector( '#login_btn' ).removeAttribute( 'disabled' );
	}else
	{
		document.querySelector( '#login_btn' ).setAttribute( 'disabled', '' );
	}
});

document.querySelector( '#contraseña_input' ).addEventListener( 'keyup', function( e )
{
	if ( document.querySelector( '#usuario_input' ).value.length >= 12 && document.querySelector( '#contraseña_input' ).value.length >= 8 )
	{
		document.querySelector( '#login_btn' ).removeAttribute( 'disabled' );
	}else
	{
		document.querySelector( '#login_btn' ).setAttribute( 'disabled', '' );
	}
});

document.querySelector( '#login_form' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	var data = new FormData( this );

	fetch( 'scripts/login.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( res   => res.json() ) 
	.then( login => 
	{
		if ( login )
		{
			setCookie( 'user', 1, 365 );

			var hoy = new Date();

			cliente   = data.get( 'RFC' );
			ejercicio = hoy.getFullYear();
			periodo   = hoy.getMonth() + 1;

			if ( periodo < 10 )
			{
				periodo = '0' + periodo;
			}

			check_num_servicio();
		}else
		{
			$( '#modal' ).modal( 'show' );
		}
	});
});

function mostrar( ID )
{
	if ( Numero_de_servicio !== '' )
	{
		document.querySelector( '#agregar_servicio_div' ).classList.add( 'd-none' );
		document.querySelector( '#home_div' ).classList.add( 'd-none' );
		document.querySelector( '#facturas_pendientes_div' ).classList.add( 'd-none' );
		document.querySelector( '#recibos_div' ).classList.add( 'd-none' );
		document.querySelector( '#buscar_div' ).classList.add( 'd-none' );

		document.querySelector( '#' + ID ).classList.remove( 'd-none' );
	}else
	{
		document.querySelector( '#modal_content' ).innerHTML = `
			<div class="modal-header">
				<h5 class="modal-title" id="staticBackdropLabel">Sin servicios</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				Primero debes agregar un número de servicio.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Entendido</button>
			</div>
		`;

		$( '#modal' ).modal( 'show' );
	}
}


document.querySelector( '#agregar_servicio_form' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	var data = new FormData( this );
		data.append( 'cliente', getCookie( 'user' ) );

	fetch( 'scripts/agregar_servicio.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( res   => res.json() ) 
	.then( r => 
	{
		if ( r )
		{
			Numero_de_servicio = data.get( 'num_servicio' );

			saldo_deudor();
			facturas_pendientes();
			numero_de_servicio();
			lista_de_recibos();

			mostrar( 'home_div' );

			document.querySelector( '#modal_content' ).innerHTML = `
				<div class="modal-header">
					<h5 class="modal-title" id="staticBackdropLabel">Alta servicio</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					Has dado de alta exitosamente un número de servicio.
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal">Entendido</button>
				</div>
			`;
		}else
		{
			document.querySelector( '#modal_content' ).innerHTML = `
				<div class="modal-header">
					<h5 class="modal-title" id="staticBackdropLabel">Alta servicio</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					Ya has dado de alta este servicio anteriormente o no existe. Favor de verificarlo.
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal">Entendido</button>
				</div>
			`;
		}

		$( '#modal' ).modal( 'show' );
	});
});

function lista_de_recibos() 
{
	var data = new FormData();
		data.append( 'num_servicio', Numero_de_servicio );

	fetch( 'scripts/lista_de_recibos.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( res   => res.json() ) 
	.then( r => 
	{
		document.querySelector( '#table_lista_de_recibos tbody' ).innerHTML = '';

		for ( var i = 0; i < r.length; i++ )
		{
			if ( r[i].pagado )
			{
				document.querySelector( '#table_lista_de_recibos tbody' ).innerHTML += `
					<tr>
						<td>${ r[i].periodo }</td>
						<td class="text-center">${ numeral( r[i].importe ).format( '$0,0.00' ) }<br>PAGADO</td>
						<td><i class="far fa-check-circle fa-2x"></i></td>
					</tr>
				`;
			}else
			{
				document.querySelector( '#table_lista_de_recibos tbody' ).innerHTML += `
					<tr>
						<td>${ r[i].periodo }</td>
						<td class="text-center">${ numeral( r[i].importe ).format( '$0,0.00' ) }<br>PENDIENTE</td>
						<td></td>
					</tr>
				`;
			}
		}
	});
}

function check_num_servicio()
{
	var data = new FormData();
		data.append( 'cliente', getCookie( 'user' ) );

	fetch( 'scripts/check_num_servicio.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( res   => res.json() ) 
	.then( r => 
	{
		if ( r !== false )
		{
			Numero_de_servicio = r;

			saldo_deudor();
			facturas_pendientes();
			numero_de_servicio();
			lista_de_recibos();

			document.querySelector( '#login_div' ).classList.add( 'd-none' );
			document.querySelector( '#home_div' ).classList.remove( 'd-none' );
		}else
		{
			document.querySelector( '#login_div' ).classList.add( 'd-none' );
			document.querySelector( '#agregar_servicio_div' ).classList.remove( 'd-none' );
		}
	});	
}

function saldo_deudor() 
{
	var data = new FormData();
		data.append( 'num_servicio', Numero_de_servicio );

	fetch( 'scripts/saldo_deudor.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( res   => res.json() ) 
	.then( r => 
	{
		if ( r.pagado )
		{
			document.querySelector( '#saldo_deudor_div' ).innerHTML = `
				<p class="text-center h5 mb-0">Saldo actual: Pagado <i class="far fa-check-circle"></i></p>
				<p class="text-center h3 font-weight-bold mb-5">${ r.importe }</p>
				<p class="text-center mb-0">Fecha límite de pago:</p>
				<p class="text-center font-weight-bold mb-5">${ r.fecha_limite }</p>
				<p class="text-center">Periodo: <span class="font-weight-bold">${ r.periodo }</span></p>
				<hr>
				<button class="btn btn-success btn-block" disabled>PAGAR</button>
			`;
		}else
		{
			document.querySelector( '#saldo_deudor_div' ).innerHTML = `
				<p class="text-center h5 mb-0">Saldo actual: PENDIENTE</p>
				<p class="text-center h3 font-weight-bold mb-5">${ r.importe }</p>
				<p class="text-center mb-0">Fecha límite de pago:</p>
				<p class="text-center font-weight-bold mb-5">${ r.fecha_limite }</p>
				<p class="text-center">Periodo: <span class="font-weight-bold">${ r.periodo }</span></p>
				<hr>
				<button class="btn btn-success btn-block" onclick="mostrar( 'facturas_pendientes_div' );">PAGAR</button>
			`;
		}
	});
}

function numero_de_servicio() 
{
	var data = new FormData();
		data.append( 'num_servicio', Numero_de_servicio );

	fetch( 'scripts/numero_de_servicio.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( res   => res.json() ) 
	.then( r => 
	{
		document.querySelector( '#numero_de_servicio_div' ).innerHTML = `
			<p class="text-center h2 font-weight-bold">${ r.num_servicio }</p>
			<p class="text-center font-weight-bold text-uppercase mb-0">${ r.nombre }</p>
			<p class="text-center font-weight-bold">${ r.RFC }</p>
			<div class="font-size-sm">
				<p class="text-center text-uppercase mb-0">${ r.calle } ${ r.num_ext } ${ r.colonia }</p>
				<p class="text-center text-uppercase">${ r.cp } ${ r.municipio } ${ r.estado }</p>
			</div>
		`;
	});
}

function facturas_pendientes()
{
	fetch( 'scripts/facturas_pendientes.php' )
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

document.querySelector( '#suma_de_facturas_btn' ).addEventListener( 'click', function()
{
	var data = new FormData();
		data.append( 'cliente', getCookie( 'user' ) );

		for ( var i = 0; i < related.length; i++ )
		{
			data.append( 'uuid['+i+']', related[i].uuid );
			data.append( 'unit_price['+i+']', numeral( related[i].last_balance ).format( '00.00' ) );
		}

	fetch( 'scripts/generar_link_de_pago.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( res   => res.json() ) 
	.then( r => 
	{
		window.location.href = r;
	});
});

document.querySelector( '#logout_btn' ).addEventListener( 'click', function()
{
	deleteCookie( 'user' );

	checkCookie();
});
