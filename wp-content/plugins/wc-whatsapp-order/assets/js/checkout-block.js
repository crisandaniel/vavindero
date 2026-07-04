( function () {
	const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
	const { createElement } = window.wp.element;
	const settings = window.wc.wcSettings.getSetting( 'whatsapp_order_data', {} );

	const label = settings.title || 'Comandă pe WhatsApp';

	const Content = () =>
		createElement( 'div', {}, settings.description || '' );

	const Label = () => createElement( 'span', {}, label );

	registerPaymentMethod( {
		name: 'whatsapp_order',
		label: createElement( Label ),
		content: createElement( Content ),
		edit: createElement( Content ),
		canMakePayment: () => true,
		ariaLabel: label,
		supports: {
			features: [ 'products' ],
		},
	} );
} )();
