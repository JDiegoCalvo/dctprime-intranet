// var cliente;
// var ejercicio;;
// var periodo = '01';
// var tipo = 'xml-recibidos/xml';

var xml_emitidos = [];

init();

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
	});
}

function dibujar()
{
	$( '#PUEtable tbody' ).html( '' );

	var a = 0;

	for ( var i = 0; i < xml_emitidos.length; i++ )
	{
		if ( xml_emitidos[i].metodo_de_pago == 'PUE' )
		{
			$( '#PUEtable tbody' ).append( `
				<tr>
					<td class="text-center text-secondary">${ a + 1 }</td>
					<td>
						<button class="btn btn-link text-left p-0" onclick="detalles( '${ xml_emitidos[i].folio_fiscal }' );">${ xml_emitidos[i].nombre }</button>
					</td>
					<td class="text-right">${ numeral( xml_emitidos[i].importe ).format( '$0,0.00' ) }</td>
					<td class="text-center">${ xml_emitidos[i].forma_de_pago }</td>
					<td class="text-center"><i class="far fa-edit text-primary" onclick="aplicar( '${ xml_emitidos[i].folio_fiscal }' )"></i></td>
					<td class="text-center" id="selectPUE${ a }">${ xml_emitidos[i].register }</td>
				</tr>
			` );

			a++;
		}
	}

	$( '#PPDtable tbody' ).html( '' );

	var b = 0;

	for ( var i = 0; i < xml_emitidos.length; i++ )
	{
		if ( xml_emitidos[i].metodo_de_pago == 'PPD' )
		{
			$( '#PPDtable tbody' ).append( `
				<tr>
					<td class="text-center">${ b + 1 }</td>
					<td>
						<button class="btn btn-link text-left p-0" onclick="detalles( '${ xml_emitidos[i].folio_fiscal }' );">${ xml_emitidos[i].nombre.substring( 0, 20 ) }</button>
					</td>
					<td class="text-right">${ numeral( xml_emitidos[i].importe ).format( '$0,0.00' ) }</td>
					<td class="text-center">${ xml_emitidos[i].forma_de_pago }</td>
					<td class="text-center"><i class="far fa-edit text-primary" onclick="aplicar( '${ xml_emitidos[i].folio_fiscal }' )"></i></td>
					<td class="text-center" id="selectPPD${ b }">${ xml_emitidos[i].register }</td>
				</tr>
			` );

			b++;
		}
	}

	$( '#pagoTable tbody' ).html( '' );

	var c = 0;

	for ( var i = 0; i < xml_emitidos.length; i++ )
	{
		if ( xml_emitidos[i].metodo_de_pago == 'Pago' )
		{
			$( '#pagoTable tbody' ).append( `
				<tr>
					<td class="text-center">${ c + 1 }</td>
					<td>
						<button class="btn btn-link text-left p-0" onclick="detalles_pago( '${ xml_emitidos[i].referencia }' );">${ xml_emitidos[i].nombre.substring( 0, 20 ) }</button>
					</td>
					<td class="text-right">${ numeral( xml_emitidos[i].importe ).format( '$0,0.00' ) }</td>
					<td class="text-center">${ xml_emitidos[i].forma_de_pago }</td>
					<td class="text-center"><i class="far fa-edit text-primary" onclick="aplicar( '${ xml_emitidos[i].folio_fiscal }' )"></i></td>
					<td class="text-center" id="selectPago${ c }">${ xml_emitidos[i].register }</td>
				</tr>
			` );

			c++;
		}
	}

	$( '#notaTable tbody' ).html( '' );

	var d = 0;

	for ( var i = 0; i < xml_emitidos.length; i++ )
	{
		if ( xml_emitidos[i].metodo_de_pago == 'Nota de crédito' )
		{
			$( '#notaTable tbody' ).append( `
				<tr>
					<td class="text-center">${ d + 1 }</td>
					<td>
						<button class="btn btn-link text-left p-0" onclick="detalles( '${ xml_emitidos[i].folio_fiscal }' );">${ xml_emitidos[i].nombre.substring( 0, 20 ) }</button>
					</td>
					<td class="text-right">${ numeral( xml_emitidos[i].importe ).format( '$0,0.00' ) }</td>
					<td class="text-center">${ xml_emitidos[i].forma_de_pago }</td>
					<td>
						<select class="form-control form-control-sm" id="selectNota${ d }" onchange="aplicar( '${ xml_emitidos[i].folio_fiscal }' );">
							<option>Selecciona</option>
							<option>Nota de crédito</option>
						</select>
					</td>
					<td class="text-center">${ xml_emitidos[i].register }</td>
				</tr>
			` );

			d++;
		}
	}
}

function aplicar( folio_fiscal )
{
	var data = new FormData();
		data.append( 'cliente', cliente );

	fetch( 'scripts/cuentas_de_banco.php', {
		method : 'POST',
		body   : data
	})
	.then( res => res.json() )
	.then( r => 
	{
		for ( var i = 0; i < xml_emitidos.length; i++ )
		{
			if ( folio_fiscal == xml_emitidos[i].folio_fiscal )
			{
				UUID   = folio_fiscal;
				RFC    = xml_emitidos[i].RFC;
				nombre = xml_emitidos[i].nombre;
				fecha  = xml_emitidos[i].fecha;
				CP     = xml_emitidos[i].CP;
				metodo_de_pago = xml_emitidos[i].metodo_de_pago;
				forma_de_pago  = xml_emitidos[i].forma_de_pago;
				serie   = xml_emitidos[i].serie;
				folio   = xml_emitidos[i].folio;
				select  = xml_emitidos[i].select;
				general = xml_emitidos[i].general;
				cero    = xml_emitidos[i].cero;
				exento  = xml_emitidos[i].exento;
				IVA     = xml_emitidos[i].IVA;
				IEPS    = xml_emitidos[i].IEPS;
				ISR_retenido = xml_emitidos[i].ISR_retenido;
				IVA_retenido = xml_emitidos[i].IVA_retenido;

				diferencia_general = numeral( xml_emitidos[i].general - ( xml_emitidos[i].IVA / 0.16 ) ).format( '0,0.00' );

				diferencia_total =  numeral( parseFloat( xml_emitidos[i].importe ) - 
										 ( parseFloat( xml_emitidos[i].general ) + 
										 parseFloat( xml_emitidos[i].cero ) + 
										 parseFloat( xml_emitidos[i].exento ) + 
										 parseFloat( xml_emitidos[i].IVA ) + 
										 parseFloat( xml_emitidos[i].IEPS ) - 
										 parseFloat( xml_emitidos[i].ISR_retenido ) - 
										 parseFloat( xml_emitidos[i].IVA_retenido ) ).toFixed( 2 ) ).format( '0,0.00' );
			}
		}

		descripcion = '';

		$.ajax({
			method: 'GET',
			url: '../archivos-xml/' + cliente + '/' + ejercicio + '/' + periodo + '/' + tipo + '/' + folio_fiscal.toUpperCase() + '.xml'
		}).done( function ( xml )
		{
			$( xml ).find( 'cfdi\\:Concepto' ).each( function()
			{
				descripcion += $( this ).attr( 'Descripcion' ) + ', ';
			});

			var HTML = '';

				if ( tipo == 'xml-emitidos/xml' )
				{
					HTML += `<form id="aplicar_form">`;

					donde_aplicar = 'aplicar_form';
				}else
				{
					HTML += `<form id="aplicar_recibidos_form">`;

					donde_aplicar = 'aplicar_recibidos_form';
				}

					HTML += `<div class="form-group">
						<ul class="list-unstyled">
							<li class="mb-1">
								<input text="text" class="form-control form-control-sm" name="descripcion_provision" value="${ descripcion }">
							</li>
							<li>
								<input text="text" class="form-control form-control-sm" name="descripcion_realizacion" value="${ descripcion }">
							</li>
						</ul>
					</div>
					<table class="table table-sm table-bordered table-hover table-striped font-size-sm">
						<tbody>
							<tr>
								<td>${ nombre }</td>
								<td><span class="font-weight-bold">Serie:</span> ${ serie }</td>
								<td><span class="font-weight-bold">Folio:</span> ${ folio }</td>
							</tr>
							<tr>
								<td><span class="font-weight-bold">Fecha:</span> ${ fecha }</td>
								<td><span class="font-weight-bold">RFC:</span> ${ RFC }</td>
								<td><span class="font-weight-bold">Predial:</span> ${ 0 }</td>
							</tr>
							<tr>
								<td><span class="font-weight-bold">Código Postal:</span> ${ CP }</td>
								<td><span class="font-weight-bold">Método de pago:</span> ${ metodo_de_pago }</td>
								<td><span class="font-weight-bold">Forma de pago:</span> ${ forma_de_pago }</td>
							</tr>
						</tbody>
					</table>

					<div class="row  my-3">
						<div class="col-6">
							<table class="table table-sm table-borderless">
								<tbody>
									<tr>
										<th>General: </th>
										<td>
											<input type="text" class="form-control form-control-sm" name="general" value="${ general }" required>
										</td>
									</tr>
									<tr>
										<th>Cero: </th>
										<td>
											<input type="text" class="form-control form-control-sm" name="cero" value="${ cero }" required>
										</td>
									</tr>
									<tr>
										<th>Exento: </th>
										<td>
											<input type="text" class="form-control form-control-sm" name="exento" value="${ exento }" required>
										</td>
									</tr>
									<tr>
										<th>IVA: </th>
										<td>
											<input type="text" class="form-control form-control-sm" name="" value="${ IVA }" required>
										</td>
									</tr>
									<tr>
										<th>IEPS: </th>
										<td>
											<input type="text" class="form-control form-control-sm" name="" value="${ IEPS }" required>
										</td>
									</tr>
									<tr>
										<th>ISR retenido: </th>
										<td>
											<input type="text" class="form-control form-control-sm" name="ISR_retenido" value="${ ISR_retenido }" required>
										</td>
									</tr>
									<tr>
										<th>IVA retenido: </th>
										<td>
											<input type="text" class="form-control form-control-sm" name="IVA_retenido" value="${ IVA_retenido }" required>
										</td>
									</tr>
									<tr>
										<th>Saldos: </th>
										<td>${ diferencia_general } | ${ diferencia_total }</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="col-6">
							<div class="text-center">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-primary active">
										<input type="radio" name="registro" value="De contado" id="option1" checked> De contado
									</label>
									<label class="btn btn-primary">
										<input type="radio" name="registro" value="Provisión" id="option2"> Provisión
									</label>
									<label class="btn btn-primary">
										<input type="radio" name="registro" value="Realización" id="option3"> Realización
									</label>
								</div>
							</div>
							<div class="row">
								<div class="col-6">
									<div class="form-group">
										<label>Cuenta</label>
										<select class="form-control" name="cuenta">`;

											if ( tipo == 'xml-emitidos/xml' )
											{
												HTML += `
												<optgroup label="Ingresos">
													<option value="401,01">Ventas y/o servicios gravados a la tasa general</option>
													<option value="401,07">Ventas y/o servicios exentos</option>
												</optgroup>`;
											}else
											{
												HTML += `
												<optgroup label="Inventario">
													<option value="115,01">Inventario</option>
												</optgroup>
												<optgroup label="Gastos">
													<option value="602,34">Honorarios a personas físicas residentes nacionales</option>
													<option value="602,38">Honorarios a personas morales residentes nacionales</option>
													<option value="602,45">Arrendamiento a personas físicas residentes nacionales</option>
													<option value="602,48">Combustibles y lubricantes</option>
													<option value="602,50">Teléfono, internet</option>
													<option value="602,52">Energía eléctrica</option>
													<option value="602,55">Papelería y artículos de oficina</option>
													<option value="602,56">Mantenimiento y conservación</option>
													<option value="602,84">Otros gastos de venta</option>
												</optgroup>
												<optgroup label="Gastos financieros">
													<option value="701,04">Intereses a cargo bancario nacional</option>
												</optgroup>
												<optgroup label="Otros gastos">
													<option value="703,21">Otros gastos</option>
												</optgroup>
												<optgroup label="Activos no circulantes">
													<option value="155,01">Mobiliario y equipo de oficina</option>
												</optgroup>
												`;
											}

										HTML += `
										</select>
									</div>
									<div class="form-group">`;

											if ( tipo == 'xml-emitidos/xml' )
											{
												HTML += `
												<label>Cliente</label>
												<select class="form-control" name="clientes">
													<option value="105,01">Nacional</option>
													<option value="105,02">Extranjero</option>
													<option value="105,03">Nacional parte relacionada</option>
													<option value="105,04">Extranjero parte relacionada</option>
												</select>`;
											}else
											{
												HTML += `
												<label>Proveedor</label>
												<select class="form-control" name="proveedor">
													<option value="201,01">Nacional</option>
													<option value="201,02">Extranjero</option>
													<option value="201,03">Nacional parte relacionada</option>
													<option value="201,04">Extranjero parte relacionada</option>
												</select>`;
											}

									HTML += `</div>
								</div>
								<div class="col-6">
									<div class="form-group">	
										<label>Fecha de pago</label>
										<input type="date" class="form-control" value="${ fecha.substr( 0, 10 ) }" name="fecha_de_pago">
									</div>
									<div class="form-group">
										<label>Forma de pago</label>
										<select class="form-control" name="forma_de_pago">
											<optgroup label="Efectivo" id="efectivo">
												<option value="101,01">Caja y efectivo</option>
											</optgroup>
											<optgroup label="Tarjeta de crédito" id="TDC">
							
											</optgroup>
											<optgroup label="Terceros" id="terceros">
					
											</optgroup>
										</select>
									</div>
								</div>
							</div>
							<div class="form-row">

								<div class="form-group col-6">
									<label>Forma de pago</label>
									<div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
										<label class="btn btn-primary active">
											<input type="radio" name="FP" value="Transferencia" id="option18" checked> Transferencia
										</label>
										<label class="btn btn-primary">
											<input type="radio" name="FP" value="Cheque" id="option19"> Cheque
										</label>
									</div>
								</div>

								<div class="form-group col-6">
									<label>No. de cheque</label>
									<input type="text" class="form-control form-control-sm" name="no_de_cheque">
								</div>
							</div>
							<div class="form-row">

								<div class="form-group col-6">
									<label>Cta. de destino</label>
									<input type="text" class="form-control form-control-sm" name="cta_destino" value="XXXX">
								</div>
								<div class="form-group col-6">
									<label>Banco de destino</label>
									<input type="text" class="form-control form-control-sm" name="banco_destino" value="BANORTE">
								</div>
							</div>
							<hr>
							<div class="form-group">
								<label>ISR</label>
								<div class="text-center">`;

									if ( cliente.length == 12 )
									{
										HTML += `<div class="form-group">
											<div class="form-check">
												<input class="form-check-input" type="radio" name="aplicar_ISR" id="gridRadios1" value="Provisión" checked>
												<label class="form-check-label" for="gridRadios1">
												Provisión
												</label>
											</div>
											<div class="form-check">
												<input class="form-check-input" type="radio" name="aplicar_ISR" id="gridRadios2" value="Realización">
												<label class="form-check-label" for="gridRadios2">
												Realización
												</label>
											</div>
										</div>`;
									}else
									{
										HTML += `<div class="form-group">
											<div class="form-check">
												<input class="form-check-input" type="radio" name="aplicar_ISR" id="gridRadios1" value="Provisión">
												<label class="form-check-label" for="gridRadios1">
												Provisión
												</label>
											</div>
											<div class="form-check">
												<input class="form-check-input" type="radio" name="aplicar_ISR" id="gridRadios2" value="Realización" checked>
												<label class="form-check-label" for="gridRadios2">
												Realización
												</label>
											</div>
										</div>`;
									}

									if ( tipo == 'xml-emitidos/xml' )
									{
										HTML += `
											<div class="form-group">
												<div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-primary active">
														<input type="radio" name="ISR" value="Gravado" id="option4" checked> Gravado
													</label>
													<label class="btn btn-primary">
														<input type="radio" name="ISR" value="Exento" id="option5"> Exento
													</label>
												</div>
											</div>
										`;
									}else 
									{
										HTML += `
											<div class="form-group">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-primary active">
														<input type="radio" name="ISR" value="Deducible" id="option4" checked> Deducible
													</label>
													<label class="btn btn-primary">
														<input type="radio" name="ISR" value="No deducible" id="option5"> No deducible
													</label>
												</div>
											</div>

											<div class="form-group">
												<select class="form-control form-control-sm" name="clasificacion">
													<option>Compras y gastos generales</option>
													<option>Seguros y fianzas</option>
													<option>Viáticos y gastos de viaje</option>
													<option>Consumo en restaurantes</option>
													<option>Gasolina y mantenimiento de transporte</option>
													<option>Aportaciones SAR e INFONAVIT y CRV</option>
													<option>Cuotas al IMSS</option>
													<option>Sueldos a adultos mayores y capacidades dif</option>
													<option>Sueldos otros</option>
													<option>Contribuciones exepto ISR, IETU e IVA</option>
													<option>Impuesto local por ingresos profesionales</option>
													<option>Deducción por renta de autos</option>
													<option>Monto exento de ingresos del trabajador</option>
													<option>Monto deducible 47%</option>
													<option>Monto deducible 53%</option>
												</select>
											</div>
										`;
									}

								HTML += `

									<div class="form-group">
										<select class="form-control form-control-sm" name="operacion">
											<option>Servicios profesionales</option>
											<option>Actividad empresarial</option>
											<option>Intereses</option>
											<option>Sueldos, salarios y asimilados</option>
										</select>
									</div>
								</div>
							</div>`;

							if ( 1 == 1 )
							{

							}else 
							{
								HTML += `
								<hr>
								<div class="form-group">
									<label>IVA</label>
									<select class="form-control form-control-sm" name="IVA">
										<optgroup label="Ingresos">
											<option>Intereses pagados a la tasa del 16%</option>
											<option>Regalías pagadas entre partes relacionadas a la tasa del 16%</option>
											<option>Otros actos o actividades pagados a la tasa del 16%</option>
										</optgroup>
										<optgroup label="Ingresos exentos">
											<option>Adquisición de suelo y construcciones adheridas al suelo, destinadas o utilizadas para casa habitación</option>
											<option>Adquisición de libros, periódicos o revistas (No editados por el contribuyente)</option>
											<option>Regalías pagadas a los autores</option>
											<option>Adquisición de bienes muebles usados excepto los adquiridos de empresas</option>
											<option>Servicio de transporte público terrestre de personas</option>
											<option>Servicios profesionales de medicina</option>
											<option>Aseguramiento contra riesgos agropecuarios</option>
											<option>Uso o goce temporal de fincas para fines agrícolas o ganaderos</option>
											<option>Actos o actividades pagados en la importación de bienes y servicios exentos</option>
											<option>Otros actos o actividades pagados exentos</option>
										</optgroup>
									</select>
								</div>`;
							}

						HTML += `</div>
					</div>
					<button class="btn btn-primary btn-block">Aceptar</button>
				</form>
			`;

			document.querySelector( '#modalBody' ).innerHTML = HTML;
			
			for ( var b = 0; b < r.efectivo.length; b++ )
			{
				document.querySelector( '#efectivo' ).innerHTML += `
					<option value="${ r.efectivo[b].value }">${ r.efectivo[b].text }</option>
				`;
			}

			for ( var b = 0; b < r.TDC.length; b++ )
			{
				document.querySelector( '#TDC' ).innerHTML += `
					<option value="${ r.TDC[b].value }">${ r.TDC[b].text }</option>
				`;
			}

			for ( var b = 0; b < r.terceros.length; b++ )
			{
				document.querySelector( '#terceros' ).innerHTML += `
					<option value="${ r.terceros[b].value }">${ r.terceros[b].text }</option>
				`;
			}

			$( '#modal' ).modal( 'show' );

			document.querySelector( '#' + donde_aplicar ).addEventListener( 'submit', function( e )
			{
				e.preventDefault();

				var data = new FormData( this );
					data.append( 'cliente', cliente );
					data.append( 'ejercicio', ejercicio );
					data.append( 'periodo', periodo );
					data.append( 'folio_fiscal', UUID );
					data.append( 'fecha', fecha );
					data.append( 'nombre', nombre );
					data.append( 'RFC', RFC );
					data.append( 'serie', serie );
					data.append( 'folio', folio );

				fetch( 'scripts/' + donde_aplicar + '.php', 
				{
					method : 'POST',
					body   : data
				})
				.then( r => 
				{
					document.querySelector( '#' + select ).innerHTML = '<i class="fas fa-check-circle text-success"></i>';
					
					$( '#modal' ).modal( 'hide' );
				});
			});
		});
	});
}

function detalles( folio_fiscal )
{
	$.ajax({
		method: 'GET',
		url: '../archivos-xml/' + cliente + '/' + ejercicio + '/' + periodo + '/' + tipo + '/' + folio_fiscal.toUpperCase() + '.xml'
	}).done( function ( xml )
	{
		$( '#modalBody' ).html( `
			<p class="text-center font-weight-bold" style="font-size: 12px;">${ folio_fiscal }</p>
			<table class="table table-sm table-striped table-hover" id="concepts" style="font-size: 12px;">
				<thead>
					<tr>
						<th class="text-center">Cantidad</th>
						<th class="text-center">Descripción</th>
						<th class="text-center">Importe</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		` );

		$( xml ).find( 'cfdi\\:Concepto' ).each( function()
		{
			$( '#concepts tbody' ).append( `
				<tr>
					<td class="text-center">${ numeral( $( this ).attr( 'Cantidad' ) ).format( '0,0.00' ) }</td>
					<td>${ $( this ).attr( 'Descripcion' ) }</td>
					<td class="text-right">${ numeral( $( this ).attr( 'Importe' ) ).format( '0,0.00' ) }</td>
				</tr>
			` );
		});

		$( '#modal' ).modal( 'show' );
	});
}

function detalles_pago( folio_fiscal )
{
	$.ajax({
		method: 'GET',
		url: '../archivos-xml/' + cliente + '/' + ejercicio + '/' + periodo + '/' + tipo + '-referencias/' + folio_fiscal.toUpperCase() + '.xml'
	}).done( function ( xml )
	{
		$( '#modalBody' ).html( `
			<p class="text-center font-weight-bold" style="font-size: 12px;">${ folio_fiscal }</p>
			<table class="table table-sm table-striped table-hover" id="concepts" style="font-size: 12px;">
				<thead>
					<tr>
						<th class="text-center">Cantidad</th>
						<th class="text-center">Descripción</th>
						<th class="text-center">Importe</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		` );

		$( xml ).find( 'cfdi\\:Concepto' ).each( function()
		{
			$( '#concepts tbody' ).append( `
				<tr>
					<td class="text-center">${ numeral( $( this ).attr( 'Cantidad' ) ).format( '0,0.00' ) }</td>
					<td>${ $( this ).attr( 'Descripcion' ) }</td>
					<td class="text-right">${ numeral( $( this ).attr( 'Importe' ) ).format( '0,0.00' ) }</td>
				</tr>
			` );
		});

		$( '#modal' ).modal( 'show' );
	});
}

document.querySelector( '#seleccionar' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	document.querySelector( '#cliente_elegido' ).innerHTML = document.querySelector( '#clientes' ).options[ document.querySelector( '#clientes' ).selectedIndex ].text;

	var periodo_elegido = document.querySelector( '#periodo' ).options[ document.querySelector( '#periodo' ).selectedIndex ].text.substr( 5 );
	document.querySelector( '#periodo_y_ejercicio' ).innerHTML = periodo_elegido + ' - ' + document.querySelector( '#ejercicio' ).value;

	cliente = document.querySelector( '#clientes' ).value;
	ejercicio = document.querySelector( '#ejercicio' ).value;
	periodo = document.querySelector( '#periodo' ).value;

	document.querySelector( '#elegir_cliente' ).classList.add( 'd-none' );
	document.querySelector( '#registrar_xml' ).classList.remove( 'd-none' );
});

document.querySelector( '#emitidos' ).addEventListener( 'click', function( e )
{
	e.preventDefault();

	tipo = 'xml-emitidos/xml';

	var data = new FormData();
		data.append( 'cliente', cliente );
		data.append( 'ejercicio', ejercicio );
		data.append( 'periodo', periodo );
		data.append( 'tipo', tipo );

	fetch( 'scripts/emitidos.php',
	{
		method : 'POST',
		body   : data
	})
	.then( res => res.json() )
	.then( r   => 
	{	
		crear_arreglo( r );
	});
});

document.querySelector( '#recibidos' ).addEventListener( 'click', function( e )
{
	e.preventDefault();

	tipo = 'xml-recibidos/xml';

	var data = new FormData();
		data.append( 'cliente', cliente );
		data.append( 'ejercicio', ejercicio );
		data.append( 'periodo', periodo );
		data.append( 'tipo', tipo );

	fetch( 'scripts/recibidos.php',
	{
		method : 'POST',
		body   : data
	})
	.then( res => res.json() )
	.then( r   => 
	{	
		crear_arreglo( r );
	});
});

function crear_arreglo( r )
{
	xml_emitidos = [];

	for ( var i = 0; i < r.PUE.length; i++ )
	{
		xml_emitidos.push({
			'general'       : parseFloat( r.PUE[i].general ).toFixed( 2 ),
			'cero'          : parseFloat( r.PUE[i].cero ).toFixed( 2 ),
			'exento'        : parseFloat( r.PUE[i].exento ).toFixed( 2 ),
			'IVA'           : parseFloat( r.PUE[i].IVA ).toFixed( 2 ),
			'IEPS'          : parseFloat( r.PUE[i].IEPS ).toFixed( 2 ),
			'total'         : parseFloat( r.PUE[i].total ).toFixed( 2 ),
			'ISR_retenido'  : parseFloat( r.PUE[i].ISRretenido ).toFixed( 2 ),
			'IVA_retenido'  : parseFloat( r.PUE[i].IVAretenido ).toFixed( 2 ),
			'importe'       : parseFloat( r.PUE[i].importe ).toFixed( 2 ),
			'select'        : 'selectPUE' + i,
			'folio_fiscal'  : r.PUE[i].folioFiscal,
			'RFC'           : r.PUE[i].RFC,
			'nombre'        : r.PUE[i].nombre,
			'fecha'         : r.PUE[i].fecha[0],
			'CP'            : r.PUE[i].CP[0],
			'serie'         : r.PUE[i].serie[0],
			'folio'         : r.PUE[i].folio[0],
			'metodo_de_pago': 'PUE',
			'forma_de_pago' : r.PUE[i].formaDePago[0],
			'register'      : r.PUE[i].register
		});
	}

	for ( var i = 0; i < r.PPD.length; i++ )
	{
		xml_emitidos.push({
			'general'       : parseFloat( r.PPD[i].general ).toFixed( 2 ),
			'cero'          : parseFloat( r.PPD[i].cero ).toFixed( 2 ),
			'exento'        : parseFloat( r.PPD[i].exento ).toFixed( 2 ),
			'IVA'           : parseFloat( r.PPD[i].IVA ).toFixed( 2 ),
			'IEPS'          : parseFloat( r.PPD[i].IEPS ).toFixed( 2 ),
			'total'         : parseFloat( r.PPD[i].total ).toFixed( 2 ),
			'ISR_retenido'  : parseFloat( r.PPD[i].ISRretenido ).toFixed( 2 ),
			'IVA_retenido'  : parseFloat( r.PPD[i].IVAretenido ).toFixed( 2 ),
			'importe'       : parseFloat( r.PPD[i].importe ).toFixed( 2 ),
			'select'        : 'selectPPD' + i,
			'folio_fiscal'  : r.PPD[i].folioFiscal,
			'RFC'           : r.PPD[i].RFC,
			'nombre'        : r.PPD[i].nombre,
			'fecha'         : r.PPD[i].fecha[0],
			'CP'            : r.PPD[i].CP[0],
			'serie'         : r.PPD[i].serie[0],
			'folio'         : r.PPD[i].folio[0],
			'metodo_de_pago': 'PPD',
			'forma_de_pago' : r.PPD[i].formaDePago[0],
			'register'      : r.PPD[i].register
		});
	}

	for ( var i = 0; i < r.pago.length; i++ )
	{
		xml_emitidos.push({
			'general'       : parseFloat( r.pago[i].general ).toFixed( 2 ),
			'cero'          : parseFloat( r.pago[i].cero ).toFixed( 2 ),
			'exento'        : parseFloat( r.pago[i].exento ).toFixed( 2 ),
			'IVA'           : parseFloat( r.pago[i].IVA ).toFixed( 2 ),
			'IEPS'          : parseFloat( r.pago[i].IEPS ).toFixed( 2 ),
			'total'         : parseFloat( r.pago[i].total ).toFixed( 2 ),
			'ISR_retenido'  : parseFloat( r.pago[i].ISRretenido ).toFixed( 2 ),
			'IVA_retenido'  : parseFloat( r.pago[i].IVAretenido ).toFixed( 2 ),
			'importe'       : parseFloat( r.pago[i].importe ).toFixed( 2 ),
			'select'        : 'selectPago' + i,
			'folio_fiscal'  : r.pago[i].folioFiscal,
			'RFC'           : r.pago[i].RFC,
			'nombre'        : r.pago[i].nombre,
			'fecha'         : r.pago[i].fecha[0],
			'CP'            : r.pago[i].CP[0],
			'serie'         : r.pago[i].serie[0],
			'folio'         : r.pago[i].folio[0],
			'referencia'    : r.pago[i].referencia[0],
			'metodo_de_pago': 'Pago',
			'forma_de_pago' : r.pago[i].formaDePago[0],
			'register'      : r.pago[i].register
		});
	}

	for ( var i = 0; i < r.nota.length; i++ )
	{
		xml_emitidos.push({
			'general'       : parseFloat( r.nota[i].general ).toFixed( 2 ),
			'cero'          : parseFloat( r.nota[i].cero ).toFixed( 2 ),
			'exento'        : parseFloat( r.nota[i].exento ).toFixed( 2 ),
			'IVA'           : parseFloat( r.nota[i].IVA ).toFixed( 2 ),
			'IEPS'          : parseFloat( r.nota[i].IEPS ).toFixed( 2 ),
			'total'         : parseFloat( r.nota[i].total ).toFixed( 2 ),
			'ISR_retenido'  : parseFloat( r.nota[i].ISRretenido ).toFixed( 2 ),
			'IVA_retenido'  : parseFloat( r.nota[i].IVAretenido ).toFixed( 2 ),
			'importe'       : parseFloat( r.nota[i].importe ).toFixed( 2 ),
			'select'        : 'selectNota' + i,
			'folio_fiscal'  : r.nota[i].folioFiscal,
			'RFC'           : r.nota[i].RFC,
			'nombre'        : r.nota[i].nombre,
			'fecha'         : r.nota[i].fecha[0],
			'CP'            : r.nota[i].CP[0],
			'serie'         : r.nota[i].serie,
			'folio'         : r.nota[i].folio[0],
			'metodo_de_pago': 'Nota de crédito',
			'forma_de_pago' : r.nota[i].formaDePago[0],
			'register'      : r.nota[i].register
		});
	}

	dibujar();
}

document.querySelector( '#regresar_btn' ).addEventListener( 'click', function( e )
{
	document.querySelector( '#registrar_xml' ).classList.add( 'd-none' );
	document.querySelector( '#elegir_cliente' ).classList.remove( 'd-none' );

	document.querySelector( '#label_emitidos' ).classList.remove( 'active' );
	document.querySelector( '#label_recibidos' ).classList.remove( 'active' );

	$( '#PUEtable tbody' ).html( '' );
	$( '#PPDtable tbody' ).html( '' );
	$( '#pagoTable tbody' ).html( '' );
	$( '#notaTable tbody' ).html( '' );
});
