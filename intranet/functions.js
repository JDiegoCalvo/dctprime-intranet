if ( 'serviceWorker' in navigator ) 
{
	if ( navigator.serviceWorker.controller ) 
	{

	}else 
	{
		// Register the service worker
		navigator.serviceWorker
		.register( 'pwabuilder-sw.js', {
			scope: './'
		})
		.then( function ( reg ) 
		{
			// console.log( '[PWA Builder] Service worker has been registered for scope: ' + reg.scope );
		});
	}
}
