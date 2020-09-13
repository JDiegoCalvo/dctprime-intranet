init();

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

		leer();

		alert( 'Recibo creado.' );
	});
});
