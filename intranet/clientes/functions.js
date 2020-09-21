clientes();

function clientes()
{
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
							<td>${ r[i].spei_recurrente }</td>
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

				HTML += `</tr>
			`;

			document.querySelector( '#table_clientes tbody' ).innerHTML = HTML;
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
