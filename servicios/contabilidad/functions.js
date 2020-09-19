window.dataLayer = window.dataLayer || [];

function gtag()
{
	dataLayer.push( arguments );
}

gtag( 'js', new Date() );

gtag( 'config', 'UA-36504698-4' );

function add_node ( data ) 
{
	var element = document.createElement( data.element );

	for ( var i = 0; i < data.classNames.length; i++ )
	{
		element.classList.add( data.classNames[i] );	
	}

	var textNode = document.createTextNode( data.string );

	element.appendChild( textNode );

	return element	
}

document.getElementById( 'calculator_form' ).onsubmit = function( e )
{
	e.preventDefault();

	const emitidos = this.emitidos.value;
	const recibidos = this.recibidos.value;

	document.getElementById( 'emitidos_cantidad' ).innerHTML = emitidos;
	document.getElementById( 'recibidos_cantidad' ).innerHTML = recibidos;
	document.getElementById( 'emitidos_importe' ).innerHTML = (emitidos * 15).toFixed( 2 );
	document.getElementById( 'recibidos_importe' ).innerHTML = (recibidos * 15).toFixed( 2 );

	const subtotal = 299 + (emitidos * 15) + (recibidos * 15);
	const IVA      = subtotal * 0.16;
	const total    = subtotal + IVA;

	document.getElementById( 'subtotal' ).innerHTML = subtotal.toFixed( 2 );
	document.getElementById( 'IVA' ).innerHTML = IVA.toFixed( 2 );
	document.getElementById( 'total' ).innerHTML = total.toFixed( 2 );
}

document.getElementById( 'contact_form' ).onsubmit = function( e )
{
	e.preventDefault();

	var data = new FormData( this );
	data.append( 'place', location.href );
	
	fetch( 'https://dctprime.com/blog/scripts/contact.php', {
		method: 'POST',
		body : data
	})

	var prop = {
		"element" : "p",
		"classNames" : [
			"h1",
			"text-center",
			"text-light",
			"font-weight-bolder"
		],
		"string" : "¡Muchas gracias por escribirnos! En breve nos pondremos en contacto contigo."
	}

	var element = add_node( prop );

	const root = document.getElementById( 'contact_form' );

	root.innerHTML = '';
	root.appendChild( element );
}
			