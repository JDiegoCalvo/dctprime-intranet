clientes_();

function clientes_()
{
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
		document.querySelector( '#nombre' ).innerHTML = r.nombre;
		document.querySelector( '#fecha' ).innerHTML = r.fecha;

		var HTML = ``;

		for ( var i = 0; i < r.diario.length; i++ )
		{
			var debe = 0;
			var haber = 0;

			HTML += `
				<li>
					<hr class="hr-dotted">
					<table class="table table-sm table-bordered">
						<thead>
							<tr>
								<th>No. de póliza</th>
								<th>Fecha</th>
								<th>Concepto</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>10084</td>
								<td>${ r.diario[i].fecha }</td>
								<td>${ r.diario[i].descripcion }</td>
							</tr>
						</tbody>
					</table>
					<table class="table table-sm table-borderless mb-5">
						<thead>
							<tr>
								<th class="text-center">No. de cuenta</th>
								<th class="text-center">Concepto</th>
								<th class="text-center" colspan="2">Debe</th>
								<th class="text-center" colspan="2">Haber</th>
							</tr>
						</thead>
						<tbody>
						`;

						for ( var b = 0; b < r.diario[i].asiento.length; b++ )
						{
							if ( r.diario[i].asiento[b].debe > 0 && r.diario[i].asiento[b].haber == 0 )
							{
								HTML += `
									<tr>
										<td style="width: 50px;">${ r.diario[i].asiento[b].codigo }</td>
										<td class="pl-3">${ r.diario[i].asiento[b].cuenta }</td>
										<td style="width: 20px;">$</td>
										<td class="text-right">${ numeral( r.diario[i].asiento[b].debe ).format( '0,0.00' ) }</td>
										<td colspan="2"></td>
									</tr>
								`;
							}else
							{
								HTML += `
									<tr>
										<td style="width: 50px;">${ r.diario[i].asiento[b].codigo }</td>
										<td class="pl-5">${ r.diario[i].asiento[b].cuenta }</td>
										<td></td>
										<td></td>
										<td class="pl-3" style="width: 20px;">$</td>
										<td class="text-right">${ numeral( r.diario[i].asiento[b].haber ).format( '0,0.00' ) }</td>
									</tr>
								`;	
							}

							debe += r.diario[i].asiento[b].debe;
							haber += r.diario[i].asiento[b].haber;
						}

						HTML += `
						</tbody>
						</tfoot>
							<tr>
								<th class="text-right pr-5" colspan="2">SUMAS IGUALES:</th>
								<th class="balance" style="width: 20px;">$</th>
								<th class="text-right balance">${ numeral( debe ).format( '0,0.00' ) }</th>
								<th class="pl-3 balance" style="width: 20px;">$</th>
								<th class="text-right balance">${ numeral( haber ).format( '0,0.00' ) }</th>
							</tr>
						</tfoot>
					</table>`;

					if ( r.diario[i].UUID !== null )
					{
						HTML += `
						<table class="table table-sm table-bordered">
							<thead>
								<tr>
									<th>UUID</th>
									<th>Serie</th>
									<th>Folio</th>
									<th>Importe</th>
									<th>RFC</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>${ r.diario[i].UUID }</td>
									<td>${ r.diario[i].serie }</td>
									<td>${ r.diario[i].folio }</td>
									<td>${ numeral( r.diario[i].importe ).format( '$0,0.00' ) }</td>
									<td>${ r.diario[i].RFC }</td>
								</tr>
							</tbody>
						</table>`;
					}

					HTML += `</li>`;
					

			document.querySelector( '#contenido' ).innerHTML = HTML;
		}
	});
});