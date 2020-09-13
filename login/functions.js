document.querySelector( '#login' ).addEventListener( 'submit', function( e )
{
	e.preventDefault();

	fetch( 'scripts/login.php', 
	{
		method : 'POST',
		body   : new FormData( this )
	})
	.then( res => res.text() )
	.then( responde => 
	{
		if ( responde == 'El usuario no existe' )
		{
			document.querySelector( '#login input[name=usuario]' ).classList.add( 'is-invalid' );
			document.querySelector( '#login input[name=contraseña]' ).classList.remove( 'is-invalid' );

		}else if ( responde == 'La contraseña es incorrecta' )
		{
			document.querySelector( '#login input[name=usuario]' ).classList.remove( 'is-invalid' );
			document.querySelector( '#login input[name=contraseña]' ).classList.add( 'is-invalid' );

		}else if ( responde == 'El usuario y la contraseña son válidos' )
		{
			document.querySelector( '#login input[name=usuario]' ).classList.remove( 'is-invalid' );
			document.querySelector( '#login input[name=contraseña]' ).classList.remove( 'is-invalid' );

			window.location.href = '../';
		}else
		{
			document.querySelector( '#login input[name=usuario]' ).classList.remove( 'is-invalid' );
			document.querySelector( '#login input[name=contraseña]' ).classList.remove( 'is-invalid' );

			alert( 'Error inesperado, intente más tarde.' );
		}
	});
});
