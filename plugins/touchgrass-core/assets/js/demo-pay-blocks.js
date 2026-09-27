/* Demo Pay — Cart/Checkout Blocks payment method registration.
 * Vanilla JS, no build step. Loaded only inside the block checkout,
 * where wc-blocks-registry, wc-settings and wp-element are present. */
(function () {
	'use strict';

	if (
		!window.wc ||
		!window.wc.wcBlocksRegistry ||
		!window.wc.wcSettings ||
		!window.wp ||
		!window.wp.element
	) {
		return;
	}

	var registerPaymentMethod = window.wc.wcBlocksRegistry.registerPaymentMethod;
	var getSetting = window.wc.wcSettings.getSetting;
	var createElement = window.wp.element.createElement;

	/* Exposed server-side by TG_Demo_Pay_Blocks::get_payment_method_data(). */
	var settings = getSetting('touchgrass_demo_data', {});
	var title = settings.title || 'Demo Pay';
	var description =
		settings.description ||
		'Demo store: no card is charged and no grass is shipped.';

	var Content = function () {
		return createElement('div', { className: 'tg-demo-pay-blurb' }, description);
	};

	registerPaymentMethod({
		name: 'touchgrass_demo',
		label: createElement('span', null, title),
		content: createElement(Content, null),
		edit: createElement(Content, null),
		canMakePayment: function () {
			return true;
		},
		ariaLabel: title,
		supports: {
			features: settings.supports || ['products'],
		},
	});
})();
