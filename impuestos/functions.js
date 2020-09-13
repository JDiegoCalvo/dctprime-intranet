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

document.querySelector( '#seleccionar' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	var data = new FormData( this );

	fetch( 'scripts/impuestos.php', 
	{
		method: 'POST',
		body  : data
	})
	.then( res => res.json() )
	.then( r => 
	{
		var tarifario_ISR = [
			[ 0.01, 578.52, 0.00, 1.92 ],
			[ 578.53, 4910.18, 11.11, 6.40 ],
			[ 4910.19, 8629.20, 288.33, 10.88 ],
			[ 8629.21, 10031.07, 692.96, 16.00 ],
			[ 10031.08, 12009.94, 917.26, 17.92 ],
			[ 12009.95, 24222.31, 1271.87, 21.36 ],
			[ 24222.32, 38177.69, 3880.44, 23.52 ],
			[ 38177.70, 72887.50, 7162.74, 30.00 ],
			[ 72887.51, 97183.33, 17575.69, 32.00 ],
			[ 97183.34, 291550, 25350.35, 34.00 ],
			[ 291550.01, 'En adelante', 91435.02, 35.00 ]
		];

		var ISR_causado = 0;
		var base = r.base_gravable_del_pago_provisional;
		var periodo = 1;

		if ( base > ( periodo * tarifario_ISR[10][0] ) )
		{
			ISR_causado = ( ( base - ( periodo * tarifario_ISR[10][0] ) ) * ( tarifario_ISR[10][3] / 100 ) ) + (periodo * tarifario_ISR[10][2]);
		}else
		{
			for ( var i = 0; i < 10; i++ )
			{
				if ( base >= (periodo * tarifario_ISR[i][0]) && base <= (periodo * tarifario_ISR[i][1]) )
				{
					ISR_causado = ( ( base - (periodo * tarifario_ISR[i][0]) ) * ( tarifario_ISR[i][3] / 100 ) ) + (periodo * tarifario_ISR[i][2]);
				}
			}
		}

		var ISR_a_cargo = ISR_causado - r.total_de_impuestos_retenidos;

		var tasa_efectiva = ( ISR_causado / base ) * 100;

		document.querySelector( '#impuestos_ISR' ).innerHTML = `
			<tr class="table-active">
				<td>Ingresos de periodos anteriores</td>
				<td class="text-right">${ numeral( r.ingresos_de_periodos_anteriores ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active">
				<td>Ingresos del periodo</td>
				<td class="text-right">${ numeral( r.ingresos_del_periodo ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active font-weight-bold">
				<td>Total de ingresos gravados</td>
				<td class="text-right">${ numeral( r.total_de_ingresos_gravados ).format( '0,0' ) }</td>
			</tr>
			<tr>
				<td>Deducciones autorizadas de periodos anteriores</td>
				<td class="text-right">${ numeral( r.deducciones_autorizadas_de_periodos_anteriores ).format( '0,0' ) }</td>
			</tr>
			<tr>
				<td>Deducciones autorizadas del periodo</td>
				<td class="text-right">${ numeral( r.deducciones_autorizadas_del_periodo ).format( '0,0' ) }</td>
			</tr>
			<tr class="font-weight-bold">
				<td>Total de deducciones autorizadas</td>
				<td class="text-right">${ numeral( r.total_de_deducciones_autorizadas ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active font-weight-bold">
				<td>PTU pagada</td>
				<td class="text-right">${ numeral( r.PTU_pagada ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active font-weight-bold">
				<td>Pérdidas fiscales de ejercicios anteriores</td>
				<td class="text-right">${ numeral( r.perdidas_fiscales_de_ejercicios_anteriores ).format( '0,0' ) }</td>
			</tr>
			<tr class="font-size-lg">
				<td>Base gravable del pago provisional</td>
				<td class="text-right">${ numeral( r.base_gravable_del_pago_provisional ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active">
				<td>ISR causado</td>
				<td class="text-right">${ numeral( ISR_causado ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active">
				<td>Tasa efectiva</td>
				<td class="text-right">${ numeral( tasa_efectiva ).format( '0,0.0' ) }%</td>
			</tr>
			<tr>
				<td>Pagos provisionales efectuados con aterioridad</td>
				<td class="text-right">${ numeral( r.pagos_provisionales_efectuados_con_anterioridad ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active">
				<td>Impuesto retenido de periodos anteriores</td>
				<td class="text-right">${ numeral( r.impuesto_retenido_de_periodos_anteriores ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active">
				<td>Impuesto retenido del periodo</td>
				<td class="text-right">${ numeral( r.impuesto_retenido_del_periodo ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active font-weight-bold">
				<td>Total de impuestos retenidos</td>
				<td class="text-right">${ numeral( r.total_de_impuestos_retenidos ).format( '0,0' ) }</td>
			</tr>
			<tr class="font-weight-bold font-size-lg">
				<td>ISR a cargo</td>
				<td class="text-right">${ numeral( ISR_a_cargo ).format( '0,0' ) }</td>
			</tr>
		`;

		document.querySelector( '#impuestos_IVA' ).innerHTML = `
			<tr class="table-active">
				<td>Tasa del 16%</td>
				<td class="text-right">${ numeral( r.tasa_del_16 ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active">
				<td>Tasa del 0%</td>
				<td class="text-right">${ numeral( r.tasa_del_0 ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active">
				<td>Exento</td>
				<td class="text-right">${ numeral( r.exento ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active">
				<td>Ingresos totales</td>
				<td class="text-right">0</td>
			</tr>
			<tr>
				<td>Proporción</td>
				<td class="text-right">${ numeral( r.proporcion ).format( '0,0.0000' ) }</td>
			</tr>
			<tr class="table-active">
				<td>IVA trasladado</td>
				<td class="text-right">${ numeral( r.IVA_trasladado ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active">
				<td>IVA acreditable</td>
				<td class="text-right">${ numeral( r.IVA_acreditable ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active">
				<td>IVA retenido</td>
				<td class="text-right">${ numeral( r.IVA_retenido ).format( '0,0' ) }</td>
			</tr>
			<tr>
				<td>Cantidad a favor</td>
				<td class="text-right">${ numeral( r.cantidad_a_favor ).format( '0,0' ) }</td>
			</tr>
			<tr>
				<td>Cantidad a cargo</td>
				<td class="text-right">${ numeral( r.cantidad_a_cargo ).format( '0,0' ) }</td>
			</tr>
			<tr>
				<td>Saldo a favor de periodos anteriores</td>
				<td class="text-right">${ numeral( r.saldo_a_favor_de_periodos_anteriores ).format( '0,0' ) }</td>
			</tr>
			<tr class="table-active font-weight-bold font-size-lg">
				<td>IVA a cargo</td>
				<td class="text-right">${ numeral( r.IVA_a_cargo ).format( '0,0' ) }</td>
			</tr>
		`;

		document.querySelector( '#cliente_elegido' ).innerHTML = document.querySelector( '#clientes' ).options[ document.querySelector( '#clientes' ).selectedIndex ].text;

		var periodo_elegido = document.querySelector( '#periodo' ).options[ document.querySelector( '#periodo' ).selectedIndex ].text.substr( 5 );
		document.querySelector( '#periodo_y_ejercicio' ).innerHTML = periodo_elegido + ' - ' + document.querySelector( '#ejercicio' ).value;

		cliente = document.querySelector( '#clientes' ).value;
		ejercicio = document.querySelector( '#ejercicio' ).value;
		periodo = document.querySelector( '#periodo' ).value;

		document.querySelector( '#elegir_cliente' ).classList.add( 'd-none' );
		document.querySelector( '#impuestos' ).classList.remove( 'd-none' );
	});
});

document.querySelector( '#regresar_btn' ).addEventListener( 'click', function( e )
{
	document.querySelector( '#impuestos' ).classList.add( 'd-none' );
	document.querySelector( '#elegir_cliente' ).classList.remove( 'd-none' );
});
