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
		document.querySelector( '#contenido' ).innerHTML = `
			<p class="font-weight-bold text-primary mb-0">${ r.nombre }</p>
			<p class="font-weight-bold text-primary mb-0">Estado de resultados, resultado del periodo, por función de gasto</p>
			<p class="font-weight-bold text-primary mb-0">Cantidades monetarias Pesos Mexicanos</p>
			<hr class="bg-success hr-custom">
			<table class="table table-sm table-striped table-hover table-bordered font-size-sm mb-5">
				<thead>
					<tr>
						<th style="width: 325px;">Concepto</th>
						<th>Trimestre Año Actual<br>${ r.fechas_trimestre_actual }</th>
						<th>Acumulado Año Actual<br>${ r.fechas_año_actual }</th>
						<th>Trimestre Año Anterior<br>${ r.fechas_trimestre_anterior }</th>
						<th>Acumulado Año Actual<br>${ r.fechas_año_anterior }</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="text-primary width-custom">Ingresos</td>
						<th class="text-right">${ numeral( r.ingresos_trimestre_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.ingresos_año_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.ingresos_trimestre_anterior ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.ingresos_año_anterior ).format( '0,0' ) }</th>
					</tr>
					<tr>
						<td class="text-primary width-custom">Costo de ventas</td>
						<td class="text-right">${ numeral( r.costo_trimestre_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.costo_año_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.costo_trimestre_anterior ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.costo_año_anterior ).format( '0,0' ) }</td>
					</tr>
					<tr>
						<td class="text-primary width-custom">Utilidad bruta</td>
						<th class="text-right">${ numeral( r.bruta_trimestre_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.bruta_año_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.bruta_trimestre_anterior ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.bruta_año_anterior ).format( '0,0' ) }</th>
					</tr>
					<tr>
						<td class="text-primary width-custom">Gastos de venta</td>
						<td class="text-right">${ numeral( r.g_venta_trimestre_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.g_venta_año_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.g_venta_trimestre_anterior ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.g_venta_año_anterior ).format( '0,0' ) }</td>
					</tr>
					<tr>
						<td class="text-primary width-custom">Gastos de administración</td>
						<td class="text-right">${ numeral( r.g_admin_trimestre_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.g_admin_año_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.g_admin_trimestre_anterior ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.g_admin_año_anterior ).format( '0,0' ) }</td>
					</tr>
					<tr>
						<td class="text-primary width-custom">Otros ingresos</td>
						<td class="text-right">${ numeral( r.o_ingresos_trimestre_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.o_ingresos_año_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.o_ingresos_trimestre_anterior ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.o_ingresos_año_anterior ).format( '0,0' ) }</td>
					</tr>
					<tr>
						<td class="text-primary width-custom">Otros gastos</td>
						<td class="text-right">${ numeral( r.o_gastos_trimestre_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.o_gastos_año_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.o_gastos_trimestre_anterior ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.o_gastos_año_anterior ).format( '0,0' ) }</td>
					</tr>
					<tr>
						<td class="text-primary width-custom">Utilidad (pérdida) de operación</td>
						<th class="text-right">${ numeral( r.u_operacion_trimestre_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.u_operacion_año_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.u_operacion_trimestre_anterior ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.u_operacion_año_anterior ).format( '0,0' ) }</th>
					</tr>
					<tr>
						<td class="text-primary width-custom">Ingresos financieros</td>
						<td class="text-right">${ numeral( r.i_fin_trimestre_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.i_fin_año_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.i_fin_trimestre_anterior ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.i_fin_año_anterior ).format( '0,0' ) }</td>
					</tr>
					<tr>
						<td class="text-primary width-custom">Gastos financieros</td>
						<td class="text-right">${ numeral( r.g_fin_trimestre_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.g_fin_año_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.g_fin_trimestre_anterior ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.g_fin_año_anterior ).format( '0,0' ) }</td>
					</tr>
					<tr>
						<td class="text-primary width-custom">Participación en la utilidad (pérdida) de asociadas y negocios conjuntos</td>
						<td class="text-right">${ numeral( r.participa_trimestre_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.participa_año_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.participa_trimestre_anterior ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.participa_año_anterior ).format( '0,0' ) }</td>
					</tr>
					<tr>
						<td class="text-primary width-custom">Utilidad (pérdida) antes de impuestos</td>
						<th class="text-right">${ numeral( r.u_antes_trimestre_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.u_antes_año_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.u_antes_trimestre_anterior ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.u_antes_año_anterior ).format( '0,0' ) }</th>
					</tr>
					<tr>
						<td class="text-primary width-custom">Impuestos a la utilidad</td>
						<td class="text-right">${ numeral( r.impuestos_trimestre_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.impuestos_año_actual ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.impuestos_trimestre_anterior ).format( '0,0' ) }</td>
						<td class="text-right">${ numeral( r.impuestos_año_anterior ).format( '0,0' ) }</td>
					</tr>
					<tr>
						<td class="text-primary width-custom">Utilidad (pérdida) de operaciones continuas</td>
						<th class="text-right">${ numeral( r.continuas_trimestre_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.continuas_año_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.continuas_trimestre_anterior ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.continuas_año_anterior ).format( '0,0' ) }</th>
					</tr>
					<tr>
						<td class="text-primary width-custom">Utilidad (pérdida) de operaciones discontinuadas</td>
						<th class="text-right">${ numeral( r.discontinuas_trimestre_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.discontinuas_año_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.discontinuas_trimestre_anterior ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.discontinuas_año_anterior ).format( '0,0' ) }</th>
					</tr>
					<tr>
						<td class="text-primary width-custom">Utilidad (pérdida) neta</td>
						<th class="text-right">${ numeral( r.neta_trimestre_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.neta_año_actual ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.neta_trimestre_anterior ).format( '0,0' ) }</th>
						<th class="text-right">${ numeral( r.neta_año_anterior ).format( '0,0' ) }</th>
					</tr>
				</tbody>
			</table>
		`;
	});
});