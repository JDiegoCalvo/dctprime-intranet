<?php
	include '../../connection.php';

	include 'funciones.php';

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];

	$data = new stdClass;

	$data->nombre = nombre( $mysql, $cliente );

	$f = periodo( $ejercicio, $periodo );

	$data->fechas_trimestre_actual   = $f['trimestre_actual'];
	$data->fechas_año_actual         = '2020-01-01 2020-09-31';
	$data->fechas_trimestre_anterior = $f['trimestre_anterior'];
	$data->fechas_año_anterior       = '2019-01-01 2019-09-31';

	$data->ingresos_trimestre_actual   = ingreso( $mysql, $ejercicio, $cliente, '1', $f['periodo_query'] );
	$data->ingresos_año_actual         = ingreso( $mysql, $ejercicio, $cliente, '2', $f['periodo_query'] );
	$data->ingresos_trimestre_anterior = ingreso( $mysql, $ejercicio, $cliente, '3', $f['periodo_query'] );
	$data->ingresos_año_anterior       = ingreso( $mysql, $ejercicio, $cliente, '4', $f['periodo_query'] );

	$data->costo_trimestre_actual   = deudor( $mysql, '501', $ejercicio, $cliente, '1', $f['periodo_query'] );
	$data->costo_año_actual         = deudor( $mysql, '501', $ejercicio, $cliente, '2', $f['periodo_query'] );
	$data->costo_trimestre_anterior = deudor( $mysql, '501', $ejercicio, $cliente, '3', $f['periodo_query'] );
	$data->costo_año_anterior       = deudor( $mysql, '501', $ejercicio, $cliente, '4', $f['periodo_query'] );

	$data->bruta_trimestre_actual   = $data->ingresos_trimestre_actual + $data->costo_trimestre_actual;
	$data->bruta_año_actual         = $data->ingresos_año_actual + $data->costo_año_actual;
	$data->bruta_trimestre_anterior = $data->ingresos_trimestre_anterior + $data->costo_trimestre_anterior;
	$data->bruta_año_anterior       = $data->ingresos_año_anterior + $data->costo_año_anterior;

	$data->g_venta_trimestre_actual = deudor( $mysql, '602', $ejercicio, $cliente, '1', $f['periodo_query'] );
	$data->g_venta_año_actual = deudor( $mysql, '602', $ejercicio, $cliente, '2', $f['periodo_query'] );
	$data->g_venta_trimestre_anterior = deudor( $mysql, '602', $ejercicio, $cliente, '3', $f['periodo_query'] );
	$data->g_venta_año_anterior = deudor( $mysql, '602', $ejercicio, $cliente, '4', $f['periodo_query'] );

	$data->g_admin_trimestre_actual = deudor( $mysql, '603', $ejercicio, $cliente, '1', $f['periodo_query'] );
	$data->g_admin_año_actual = deudor( $mysql, '603', $ejercicio, $cliente, '2', $f['periodo_query'] );
	$data->g_admin_trimestre_anterior = deudor( $mysql, '603', $ejercicio, $cliente, '3', $f['periodo_query'] );
	$data->g_admin_año_anterior = deudor( $mysql, '603', $ejercicio, $cliente, '4', $f['periodo_query'] );

	$data->o_ingresos_trimestre_actual = acreedor( $mysql, '704', $ejercicio, $cliente, '1', $f['periodo_query'] );
	$data->o_ingresos_año_actual = acreedor( $mysql, '704', $ejercicio, $cliente, '2', $f['periodo_query'] );
	$data->o_ingresos_trimestre_anterior = acreedor( $mysql, '704', $ejercicio, $cliente, '3', $f['periodo_query'] );
	$data->o_ingresos_año_anterior = acreedor( $mysql, '704', $ejercicio, $cliente, '4', $f['periodo_query'] );

	$data->o_gastos_trimestre_actual = deudor( $mysql, '703', $ejercicio, $cliente, '1', $f['periodo_query'] );
	$data->o_gastos_año_actual = deudor( $mysql, '703', $ejercicio, $cliente, '2', $f['periodo_query'] );
	$data->o_gastos_trimestre_anterior = deudor( $mysql, '703', $ejercicio, $cliente, '3', $f['periodo_query'] );
	$data->o_gastos_año_anterior = deudor( $mysql, '703', $ejercicio, $cliente, '4', $f['periodo_query'] );


	$data->u_operacion_trimestre_actual = $data->bruta_trimestre_actual
										- $data->g_venta_trimestre_actual
										- $data->g_admin_trimestre_actual
										+ $data->o_ingresos_trimestre_actual
										- $data->o_gastos_trimestre_actual;


	$data->u_operacion_año_actual = $data->bruta_año_actual
								  - $data->g_venta_año_actual
								  - $data->g_admin_año_actual
								  + $data->o_ingresos_año_actual
								  - $data->o_gastos_año_actual;

	$data->u_operacion_trimestre_anterior = $data->bruta_trimestre_anterior
										  - $data->g_venta_trimestre_anterior
										  - $data->g_admin_trimestre_anterior
										  + $data->o_ingresos_trimestre_anterior
										  - $data->o_gastos_trimestre_anterior;

	$data->u_operacion_año_anterior = $data->bruta_año_anterior
								    - $data->g_venta_año_anterior
								    - $data->g_admin_año_anterior
								    + $data->o_ingresos_año_anterior
								    - $data->o_gastos_año_anterior;

	$data->i_fin_trimestre_actual = acreedor( $mysql, '702', $ejercicio, $cliente, '1', $f['periodo_query'] );
	$data->i_fin_año_actual = acreedor( $mysql, '702', $ejercicio, $cliente, '2', $f['periodo_query'] );
	$data->i_fin_trimestre_anterior = acreedor( $mysql, '702', $ejercicio, $cliente, '3', $f['periodo_query'] );
	$data->i_fin_año_anterior = acreedor( $mysql, '702', $ejercicio, $cliente, '4', $f['periodo_query'] );

	$data->g_fin_trimestre_actual = deudor( $mysql, '701', $ejercicio, $cliente, '1', $f['periodo_query'] );
	$data->g_fin_año_actual = deudor( $mysql, '701', $ejercicio, $cliente, '2', $f['periodo_query'] );
	$data->g_fin_trimestre_anterior = deudor( $mysql, '701', $ejercicio, $cliente, '3', $f['periodo_query'] );
	$data->g_fin_año_anterior = deudor( $mysql, '701', $ejercicio, $cliente, '4', $f['periodo_query'] );

	$data->participa_trimestre_actual = acreedor( $mysql, '609', $ejercicio, $cliente, '1', $f['periodo_query'] );
	$data->participa_año_actual = acreedor( $mysql, '609', $ejercicio, $cliente, '2', $f['periodo_query'] );
	$data->participa_trimestre_anterior = acreedor( $mysql, '609', $ejercicio, $cliente, '3', $f['periodo_query'] );
	$data->participa_año_anterior = acreedor( $mysql, '609', $ejercicio, $cliente, '4', $f['periodo_query'] );

	$data->u_antes_trimestre_actual = $data->u_operacion_trimestre_actual
									+ $data->i_fin_trimestre_actual
									- $data->g_fin_trimestre_actual
									+ $data->participa_trimestre_actual;


	$data->u_antes_año_actual = $data->u_operacion_año_actual
							  - $data->i_fin_año_actual
							  - $data->g_fin_año_actual
							  + $data->participa_año_actual;

	$data->u_antes_trimestre_anterior = $data->u_operacion_trimestre_anterior
									  - $data->i_fin_trimestre_anterior
									  - $data->g_fin_trimestre_anterior
									  + $data->participa_trimestre_anterior;

	$data->u_antes_año_anterior = $data->u_operacion_año_anterior
								- $data->i_fin_año_anterior
								- $data->g_fin_año_anterior
								+ $data->participa_año_anterior;

	$data->impuestos_trimestre_actual = deudor( $mysql, '611', $ejercicio, $cliente, '1', $f['periodo_query'] );
	$data->impuestos_año_actual = deudor( $mysql, '611', $ejercicio, $cliente, '2', $f['periodo_query'] );
	$data->impuestos_trimestre_anterior = deudor( $mysql, '611', $ejercicio, $cliente, '3', $f['periodo_query'] );
	$data->impuestos_año_anterior = deudor( $mysql, '611', $ejercicio, $cliente, '4', $f['periodo_query'] );

	$data->continuas_trimestre_actual   = 0;
	$data->continuas_año_actual         = 0;
	$data->continuas_trimestre_anterior = 0;
	$data->continuas_año_anterior       = 0;

	$data->discontinuas_trimestre_actual   = 0;
	$data->discontinuas_año_actual         = 0;
	$data->discontinuas_trimestre_anterior = 0;
	$data->discontinuas_año_anterior       = 0;

	$data->neta_trimestre_actual = $data->u_antes_trimestre_actual
								 - $data->impuestos_trimestre_actual
								 + $data->continuas_trimestre_actual
								 + $data->discontinuas_trimestre_actual;


	$data->neta_año_actual = $data->u_operacion_año_actual
						   - $data->impuestos_año_actual
						   + $data->continuas_año_actual
						   + $data->discontinuas_año_actual;

	$data->neta_trimestre_anterior = $data->u_operacion_trimestre_anterior
								   - $data->impuestos_trimestre_anterior
								   + $data->continuas_trimestre_anterior
								   + $data->discontinuas_año_anterior;

	$data->neta_año_anterior = $data->u_operacion_año_anterior
							 - $data->impuestos_año_anterior
							 + $data->continuas_año_anterior
							 + $data->discontinuas_año_anterior;

	echo json_encode( $data );
?>
