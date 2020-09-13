init();

function init()
{
	fetch( 'scripts/init.php' )
	.then( res => res.json() )
	.then( r   => 
	{
		var classNameRFC = document.querySelectorAll( '.clientes' );

		for ( var a = 0; a < classNameRFC.length; a++ ) 
		{
			for ( var b = 0; b < r.length; b++ )
			{
				classNameRFC[a].innerHTML += `
					<option value="${ r[b].RFC }">${ r[b].cliente }</option>
				`;
			}
		}
	});
}

function cuentas_de_banco( c )
{
	var data = new FormData();
		data.append( 'cliente', document.querySelector( '#' + c ).value );

	fetch( 'scripts/cuentas_de_banco.php',
	{
		method : 'POST',
		body   : data
	})
	.then( res => res.json() )
	.then( r   => 
	{
		document.querySelector( '#efectivo_1' ).innerHTML = '';
		document.querySelector( '#TDC_1' ).innerHTML = '';
		document.querySelector( '#terceros_1' ).innerHTML = '';

		for ( var i = 0; i < r.efectivo.length; i++ )
		{
			document.querySelector( '#efectivo_1' ).innerHTML += `
				<option value="${ r.efectivo[i].value }">${ r.efectivo[i].text }</option>
			`;
		}

		for ( var i = 0; i < r.TDC.length; i++ )
		{
			document.querySelector( '#TDC_1' ).innerHTML += `
				<option value="${ r.TDC[i].value }">${ r.TDC[i].text }</option>
			`;
		}

		for ( var i = 0; i < r.terceros.length; i++ )
		{
			document.querySelector( '#terceros_1' ).innerHTML += `
				<option value="${ r.terceros[i].value }">${ r.terceros[i].text }</option>
			`;
		}

		document.querySelector( '#efectivo_2' ).innerHTML = '';
		document.querySelector( '#TDC_2' ).innerHTML = '';
		document.querySelector( '#terceros_2' ).innerHTML = '';

		for ( var i = 0; i < r.efectivo.length; i++ )
		{
			document.querySelector( '#efectivo_2' ).innerHTML += `
				<option value="${ r.efectivo[i].value }">${ r.efectivo[i].text }</option>
			`;
		}

		for ( var i = 0; i < r.TDC.length; i++ )
		{
			document.querySelector( '#TDC_2' ).innerHTML += `
				<option value="${ r.TDC[i].value }">${ r.TDC[i].text }</option>
			`;
		}

		for ( var i = 0; i < r.terceros.length; i++ )
		{
			document.querySelector( '#terceros_2' ).innerHTML += `
				<option value="${ r.terceros[i].value }">${ r.terceros[i].text }</option>
			`;
		}
	});
}

document.querySelector( '#agregar_cuenta_banco' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	var data = new FormData( this );

	fetch( 'scripts/agregar_cuenta_banco.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( r   => 
	{
		alert( 'Registro exitoso.' );
	});
});

document.querySelector( '#isr_causado' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	var data = new FormData( this );

	fetch( 'scripts/isr_causado.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( r   => 
	{
		alert( 'Registro exitoso.' );
	});
});

document.querySelector( '#aplicar_factor_IVA_a_polizas' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	var data = new FormData( this );

	fetch( 'scripts/aplicar_factor_IVA_a_polizas.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( r   => 
	{
		alert( 'Registro exitoso.' );
	});
});

document.querySelector( '#cerrar_periodo_IVA' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	var data = new FormData( this );

	fetch( 'scripts/cerrar_periodo_IVA.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( r   => 
	{
		alert( 'Registro exitoso.' );
	});
});

document.querySelector( '#OIClientes' ).addEventListener( 'change', function( e )
{
	cuentas_de_banco( 'OIClientes' );
});

document.querySelector( '#OGClientes' ).addEventListener( 'change', function( e )
{
	cuentas_de_banco( 'OGClientes' );
});

document.querySelector( '#otros_ingresos_sin_CFDI' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	var data = new FormData( this );

	fetch( 'scripts/otros_ingresos_sin_CFDI.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( r   => 
	{
		alert( 'Registro exitoso.' );
	});
});

document.querySelector( '#otros_gastos_sin_CFDI' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	var data = new FormData( this );

	fetch( 'scripts/otros_gastos_sin_CFDI.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( r   => 
	{
		alert( 'Registro exitoso.' );
	});
});
