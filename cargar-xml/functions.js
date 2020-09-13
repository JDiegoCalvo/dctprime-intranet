init();

alertify.set( 'notifier','position', 'top-right' );

document.querySelector( '.custom-file-input' ).addEventListener( 'change', function( e )
{
	var nombre_del_archivo   = document.querySelector( '#customFileLang' ).files[0].name;
	var siguiente_nodo       = e.target.nextElementSibling;
	siguiente_nodo.innerText = nombre_del_archivo;
});

function init()
{
	var a_input = document.querySelectorAll( '.a_input' );

	for ( var i = 0; i < a_input.length; i++ ) 
	{
		a_input[i].addEventListener( 'click', function( event ) 
		{
			const elemento = event.currentTarget;
			const datos    = elemento.dataset;

			if ( datos.id !== undefined )
			{
				copiar_con_clic( datos.id );
			}
		});
	}

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
	});
}

function copiar_con_clic( ID ) 
{
	var copiar_texto = document.getElementById( ID );

	copiar_texto.select();
	copiar_texto.setSelectionRange( 0, 99999 );

	document.execCommand( 'copy' );

	alertify.notify( 'Texto copiado', 'success', 3 );
}

document.querySelector( '#seleccionar' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	fetch( 'scripts/seleccionar.php', 
	{
		method : 'POST',
		body   : new FormData( this )
	})
	.then( res => res.json() )
	.then( r   => 
	{
		var periodo = document.querySelector( '#periodo' ).options[ document.querySelector( '#periodo' ).selectedIndex ].text.substr( 5 );
		document.querySelector( '#periodo_y_ejercicio' ).innerHTML = periodo + ' - ' + document.querySelector( '#ejercicio' ).value;

		document.querySelector( '#cargar_xml_form input[name=cliente_RFC]' ).value = r.cliente_RFC;
		document.querySelector( '#cargar_xml_form input[name=CIEC]' ).value        = r.CIEC;
		document.querySelector( '#cargar_xml_form input[name=clave_FIEL]' ).value  = r.clave_FIEL;

		document.querySelector( '#elegir_cliente' ).classList.add( 'd-none' );
		document.querySelector( '#cargar_xml' ).classList.remove( 'd-none' );
	});
});

document.querySelector( '#cargar_xml_form' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	var data = new FormData( this );
		data.append( 'cliente', document.querySelector( '#clientes' ).value );
		data.append( 'ejercicio', document.querySelector( '#ejercicio' ).value );
		data.append( 'periodo', document.querySelector( '#periodo' ).value );

	document.getElementById( 'loader' ).classList.remove( 'd-none' );

	fetch( 'scripts/cargar_XML.php', 
	{
		method : 'POST',
		body   : data
	})
	.then( res => res.json() )
	.then( r   => 
	{
		document.querySelector( '#CFDIs tbody' ).innerHTML = '';

		for ( var i = 0; i < r.CFDIs.length; i++ )
		{
			document.querySelector( '#CFDIs tbody' ).innerHTML += `
				<tr ${ r.CFDIs[i].class }>
					<td class="text-truncate">${ i + 1 }</td>
					<td class="text-truncate">${ r.CFDIs[i].folio_fiscal }</td>
					<td>${ r.CFDIs[i].RFC }</td>
					<td class="text-truncate">${ r.CFDIs[i].nombre }</td>
					<td class="text-truncate">${ r.CFDIs[i].fecha_emision }</td>
					<td class="text-truncate">${ r.CFDIs[i].fecha_certificacion }</td>
					<td>${ r.CFDIs[i].PAC }</td>
					<td class="text-right">${ numeral( r.CFDIs[i].total ).format( '$0,0.00' ) }</td>
					<td>${ r.CFDIs[i].efecto }</td>
				</tr>
			`;
		}

		document.querySelector( '#referencias tbody' ).innerHTML = '';

		for ( var i = 0; i < r.referencias.length; i++ )
		{
			document.querySelector( '#referencias tbody' ).innerHTML += `
				<tr>
					<td class="text-truncate">${ i + 1 }</td>
					<td class="text-truncate">
						<input type="text" class="form-control form-control-sm b_input" autocomplete="off" value="${ r.referencias[i].folio_fiscal }" id="referencia_xml_no_${ i }" data-id="referencia_xml_no_${ i }" readonly>
					</td>
					<td>${ r.referencias[i].icon }</td>
				</tr>
			`;
		}

		var b_input = document.querySelectorAll( '.b_input' );

		for ( var i = 0; i < b_input.length; i++ ) 
		{
			b_input[i].addEventListener( 'click', function( event ) 
			{
				const elemento = event.currentTarget;
				const datos    = elemento.dataset;

				if ( datos.id !== undefined )
				{
					copiar_con_clic( datos.id );
				}
			});
		}

		document.getElementById( 'loader' ).classList.add( 'd-none' );
	});
});

document.querySelector( '#regresar' ).addEventListener( 'click', function()
{
	document.querySelector( '#elegir_cliente' ).classList.remove( 'd-none' );
	document.querySelector( '#cargar_xml' ).classList.add( 'd-none' );
});
