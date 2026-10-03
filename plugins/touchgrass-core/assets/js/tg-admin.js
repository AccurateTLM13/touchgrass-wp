/* Touch Grass admin: one-click demo import + explicit demo reset. */
(function () {
	'use strict';
	if (typeof tgAdmin === 'undefined') { return; }

	function run(action, btn, resultId, confirmMsg) {
		if (confirmMsg && !window.confirm(confirmMsg)) { return; }
		btn.disabled = true;
		var original = btn.textContent;
		btn.textContent = action === 'tg_reset_products' ? tgAdmin.i18n.resetting : tgAdmin.i18n.importing;
		var result = document.getElementById(resultId);
		result.innerHTML = '';

		var body = new FormData();
		body.append('action', action);
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
	}

	var importBtn = document.getElementById('tg-import-btn');
	if (importBtn) {
		importBtn.addEventListener('click', function () {
			run('tg_import_demo', importBtn, 'tg-import-result');
		});
	}

	var resetBtn = document.getElementById('tg-reset-btn');
	if (resetBtn) {
		resetBtn.addEventListener('click', function () {
			run('tg_reset_products', resetBtn, 'tg-reset-result', tgAdmin.i18n.resetConfirm);
		});
	}
})();
