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
