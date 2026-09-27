/* Touch Grass admin: one-click demo import. */
(function () {
	'use strict';
	var btn = document.getElementById('tg-import-btn');
	if (!btn || typeof tgAdmin === 'undefined') { return; }

	btn.addEventListener('click', function () {
		btn.disabled = true;
		var original = btn.textContent;
		btn.textContent = tgAdmin.i18n.importing;
		var result = document.getElementById('tg-import-result');
		result.innerHTML = '';

		var body = new FormData();
		body.append('action', 'tg_import_demo');
		body.append('nonce', tgAdmin.nonce);

		fetch(tgAdmin.ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (data.success) {
					var html = '<div class="notice notice-success inline"><p><strong>' + tgAdmin.i18n.done + '</strong></p><ul>';
					Object.keys(data.data.summary).forEach(function (step) {
						html += '<li><strong>' + step + ':</strong> ' + data.data.summary[step] + '</li>';
					});
					html += '</ul></div>';
					result.innerHTML = html;
					document.getElementById('tg-checklist').innerHTML = data.data.checklist.join('');
				} else {
					result.innerHTML = '<div class="notice notice-error inline"><p>' + (data.data && data.data.message ? data.data.message : tgAdmin.i18n.failed) + '</p></div>';
				}
			})
			.catch(function () {
				result.innerHTML = '<div class="notice notice-error inline"><p>' + tgAdmin.i18n.failed + '</p></div>';
			})
			.finally(function () {
				btn.disabled = false;
				btn.textContent = original;
			});
	});
})();
