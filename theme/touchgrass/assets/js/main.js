/* Touch Grass theme JS: reveal on scroll, FAQ accordion, newsletter. */
(function () {
	'use strict';

	// Reveal on scroll.
	var els = document.querySelectorAll('.reveal');
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add('in');
					io.unobserve(e.target);
				}
			});
		}, { threshold: 0.12 });
		els.forEach(function (el) { io.observe(el); });
	} else {
		els.forEach(function (el) { el.classList.add('in'); });
	}

	// FAQ accordion.
	document.querySelectorAll('.faq-item').forEach(function (item) {
		var q = item.querySelector('.faq-q');
		var a = item.querySelector('.faq-a');
		if (!q || !a) { return; }
		q.addEventListener('click', function () {
			var open = item.classList.contains('open');
			document.querySelectorAll('.faq-item.open').forEach(function (other) {
				other.classList.remove('open');
				other.querySelector('.faq-a').style.maxHeight = null;
				other.querySelector('.faq-q').setAttribute('aria-expanded', 'false');
			});
			if (!open) {
				item.classList.add('open');
				a.style.maxHeight = a.scrollHeight + 'px';
				q.setAttribute('aria-expanded', 'true');
			}
		});
	});

	// Newsletter: real AJAX subscribe. No fake success.
	// Form-scoped: every .tg-news-form on the page (footer newsletter,
	// Grass Club) gets its own handler bound to its own status elements.
	document.querySelectorAll('.tg-news-form').forEach(function (form) {
		var wrap = form.closest('.tg-news-wrap') || document;
		form.addEventListener('submit', function (ev) {
			ev.preventDefault();
			submitNewsletter(false);
		});

		function submitNewsletter(retried) {
			var email = form.querySelector('input[type="email"]');
			var done = wrap.querySelector('.news-done');
			var errBox = wrap.querySelector('.news-error');
			var fine = wrap.querySelector('.news-fine');
			var btn = form.querySelector('button[type="submit"]');
			if (errBox) { errBox.style.display = 'none'; errBox.textContent = ''; }
			if (!email || email.value.indexOf('@') < 0) { if (email) email.focus(); return; }
			if (btn) { btn.disabled = true; }
			var data = new FormData(form);
			data.append('action', 'tg_newsletter_subscribe');
			fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin' })
				.then(function (res) { return res.json(); })
				.then(function (json) {
					if (json && json.success) {
						form.style.display = 'none';
						if (fine) { fine.style.display = 'none'; }
						if (done) { done.textContent = json.data.message; done.style.display = 'block'; }
					} else if (json && json.data && json.data.code === 'expired_nonce' && !retried) {
						/* Long-cached page: fetch a fresh nonce and retry once, silently. */
						refreshNonce(function () { submitNewsletter(true); }, function () {
							showError((json.data && json.data.message) || 'Something went wrong. The grass apologizes.');
						});
					} else {
						showError((json && json.data && json.data.message) || 'Something went wrong. The grass apologizes.');
					}
				})
				.catch(function () {
					showError('Could not reach the greenhouse. Try again.');
				});

			function showError(msg) {
				if (errBox) { errBox.textContent = msg; errBox.style.display = 'block'; }
				if (btn) { btn.disabled = false; }
			}
		}

		function refreshNonce(ok, fail) {
			var data = new FormData();
			data.append('action', 'tg_newsletter_nonce_refresh');
			fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin' })
				.then(function (res) { return res.json(); })
				.then(function (json) {
					if (json && json.success && json.data.nonce) {
						var field = form.querySelector('input[name="tg_newsletter_nonce"]');
						if (field) { field.value = json.data.nonce; }
						ok();
					} else { fail(); }
				})
				.catch(fail);
		}
	});
	// Mobile nav toggle.
	var toggle = document.querySelector('.nav-toggle');
	var nav = document.getElementById('primary-nav');
	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			var open = nav.classList.toggle('open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		nav.addEventListener('click', function (e) {
			if (e.target.closest('a')) {
				nav.classList.remove('open');
				toggle.setAttribute('aria-expanded', 'false');
			}
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && nav.classList.contains('open')) {
				nav.classList.remove('open');
				toggle.setAttribute('aria-expanded', 'false');
				toggle.focus();
			}
		});
	}

	// Sticky add-to-cart (PDP, mobile): appears after scrolling past the
	// main add-to-cart form. The sticky button triggers the real form's
	// button so validation and extensions keep working.
	var sticky = document.querySelector('.tg-sticky-atc');
	var mainForm = document.querySelector('.summary form.cart');
	if (sticky && mainForm) {
		var stickyBtn = sticky.querySelector('.tg-sticky-atc-btn');
		var realBtn = mainForm.querySelector('.single_add_to_cart_button');
		function setSticky(visible) {
			sticky.classList.toggle('is-visible', visible);
			sticky.setAttribute('aria-hidden', visible ? 'false' : 'true');
		}
		if ('IntersectionObserver' in window) {
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					setSticky(!entry.isIntersecting && entry.boundingClientRect.top < 0);
				});
			}, { threshold: 0 });
			io.observe(mainForm);
		}
		if (stickyBtn) {
			stickyBtn.addEventListener('click', function () {
				if (realBtn) { realBtn.click(); }
				else { mainForm.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
			});
		}
	}
})();
