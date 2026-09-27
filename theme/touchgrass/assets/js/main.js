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

	// Newsletter: real AJAX subscribe (core plugin stores it). No fake success.
	var form = document.getElementById('newsForm');
	if (form) {
		form.addEventListener('submit', function (ev) {
			ev.preventDefault();
			var email = document.getElementById('newsEmail');
			var done = document.getElementById('newsDone');
			var errBox = document.getElementById('newsError');
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
						document.getElementById('newsFine').style.display = 'none';
						if (done) { done.textContent = json.data.message; done.style.display = 'block'; }
					} else {
						var msg = (json && json.data && json.data.message) || 'Something went wrong. The grass apologizes.';
						if (errBox) { errBox.textContent = msg; errBox.style.display = 'block'; }
						if (btn) { btn.disabled = false; }
					}
				})
				.catch(function () {
					if (errBox) { errBox.textContent = 'Could not reach the greenhouse. Try again.'; errBox.style.display = 'block'; }
					if (btn) { btn.disabled = false; }
				});
		});
	}
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
})();
