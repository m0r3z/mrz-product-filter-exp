(function ($) {
	'use strict';

	// Enveloppe ul.products (+ pagination) dans un conteneur stable, cible des
	// remplacements. Partagé entre instances multiples de panneau.
	function ensureResults() {
		var existing = document.querySelector('.mrz-product-filter-exp-results');
		if (existing) { return existing; }

		var ul = document.querySelector('ul.products');
		if (!ul) { return null; }

		var box = document.createElement('div');
		box.className = 'mrz-product-filter-exp-results';
		ul.parentNode.insertBefore(box, ul);
		box.appendChild(ul);

		var next = box.nextElementSibling;
		if (next && next.classList && next.classList.contains('woocommerce-pagination')) {
			box.appendChild(next);
		}
		return box;
	}

	function initInstance(wrapper) {
		if (wrapper.__mrzPfeBound) { return; }
		wrapper.__mrzPfeBound = true;

		var form = wrapper.querySelector('.mrz-product-filter-exp-form');
		if (!form) { return; }

		var resultsEl = ensureResults();

		// Paramètres gérés par ce panneau (à réinitialiser dans l'URL).
		var managed = ['min_price', 'max_price', 'paged', 'product-page'];
		form.querySelectorAll('.mrz-product-filter-exp-section[data-param]').forEach(function (el) {
			var p = el.getAttribute('data-param');
			managed.push(p);
			// Les attributs portent aussi un paramètre de logique OU/ET.
			if (p.indexOf('filter_') === 0) {
				managed.push('query_type_' + p.slice(7));
			}
		});

		function collectParams() {
			var out = {};
			var groups = {};
			Array.prototype.forEach.call(form.elements, function (el) {
				if (!el.name) { return; }
				var name = el.name.replace(/\[\]$/, '');
				if (el.type === 'checkbox' || el.type === 'radio') {
					if (!el.checked || el.value === '') { return; }
					(groups[name] = groups[name] || []).push(el.value);
				} else if (el.tagName === 'SELECT') {
					if (el.value) { (groups[name] = groups[name] || []).push(el.value); }
				} else if (el.type === 'number') {
					if (el.value !== '') { out[name] = el.value; }
				}
			});
			Object.keys(groups).forEach(function (k) { out[k] = groups[k].join(','); });

			// Omet le prix s'il couvre toute la fourchette (aucun filtrage réel).
			var price = form.querySelector('.mrz-product-filter-exp-price');
			if (price) {
				var lo = price.getAttribute('data-min');
				var hi = price.getAttribute('data-max');
				if (out.min_price === lo && out.max_price === hi) {
					delete out.min_price;
					delete out.max_price;
				}
			}
			return out;
		}

		// Construit l'URL de l'archive courante avec les filtres appliqués (page 1).
		function buildUrl() {
			var url = new URL(window.location.href);
			// Réinitialise la pagination : paramètres de query ET segment de
			// chemin des permaliens jolis (/page/N/), sinon un filtre appliqué
			// depuis la page 2+ interroge une page inexistante du résultat filtré.
			url.pathname = url.pathname.replace(/\/page\/\d+(?=\/|$)/, '');
			managed.forEach(function (p) { url.searchParams.delete(p); });
			var params = collectParams();
			Object.keys(params).forEach(function (k) { url.searchParams.set(k, params[k]); });

			// Logique OU/ET par attribut (WooCommerce est en ET par défaut :
			// on transmet explicitement query_type pour obtenir l'union en OU).
			form.querySelectorAll('.mrz-product-filter-exp-section[data-param]').forEach(function (sec) {
				var p = sec.getAttribute('data-param');
				if (p.indexOf('filter_') === 0 && params[p]) {
					url.searchParams.set('query_type_' + p.slice(7), sec.getAttribute('data-logic') || 'or');
				}
			});

			var ord = document.querySelector('.woocommerce-ordering select[name="orderby"]');
			if (ord && ord.value) { url.searchParams.set('orderby', ord.value); }
			return url.toString();
		}

		function setLoading(state) {
			if (resultsEl) { resultsEl.classList.toggle('mrz-product-filter-exp-loading', !!state); }
		}

		// Récupère la vraie page filtrée et remplace la boucle produits.
		// scrollTop : remonte en haut de page une fois le contenu injecté
		// (utile à la pagination). Le scroll est fait APRÈS l'injection pour
		// que la hauteur de page soit stable (sinon on retombe « au milieu »).
		function loadUrl(targetUrl, scrollTop) {
			// Sans cible de remplacement (thème au balisage non standard) : on
			// laisse le navigateur charger la page filtrée normalement.
			if (!resultsEl) {
				window.location.assign(targetUrl);
				return;
			}

			setLoading(true);
			fetch(targetUrl, {
				credentials: 'same-origin',
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			})
				.then(function (r) { return r.text(); })
				.then(function (html) {
					setLoading(false);
					var doc = new DOMParser().parseFromString(html, 'text/html');
					var newUl = doc.querySelector('ul.products');
					var newPag = doc.querySelector('.woocommerce-pagination');

					resultsEl.innerHTML = '';
					if (newUl) {
						resultsEl.appendChild(document.importNode(newUl, true));
						if (newPag) { resultsEl.appendChild(document.importNode(newPag, true)); }
					} else {
						var notice = doc.querySelector('.woocommerce-info, .woocommerce-no-products-found');
						resultsEl.innerHTML = notice ? notice.outerHTML : '';
					}

					window.history.pushState({}, '', targetUrl);

					if (scrollTop) {
						window.scrollTo({ top: 0, behavior: 'smooth' });
					}
				})
				.catch(function () {
					setLoading(false);
					// Repli : navigation réelle (le serveur filtre nativement).
					window.location.assign(targetUrl);
				});
		}

		function apply() {
			loadUrl(buildUrl());
		}

		// Changements de filtres (hors champs numériques, gérés par le prix).
		form.addEventListener('change', function (e) {
			if (e.target && e.target.type !== 'number') { apply(); }
		});

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			apply();
		});

		// Pagination : on suit le vrai lien d'archive (href correct), en AJAX.
		if (resultsEl) {
			resultsEl.addEventListener('click', function (e) {
				var a = e.target.closest ? e.target.closest('a.page-numbers') : null;
				if (!a) { return; }
				var href = a.getAttribute('href');
				if (!href) { return; }
				e.preventDefault();
				loadUrl(href, true);
			});
		}

		// Réglette de prix (jQuery UI slider).
		initPriceSlider(wrapper, apply);

		// Retour navigateur : recharge l'état natif de la page.
		window.addEventListener('popstate', function () { window.location.reload(); });
	}

	function initPriceSlider(wrapper, apply) {
		var price = wrapper.querySelector('.mrz-product-filter-exp-price');
		if (!price || !$ || !$.fn.slider) { return; }

		var sliderEl = price.querySelector('.mrz-product-filter-exp-price-slider');
		var minIn = price.querySelector('.mrz-product-filter-exp-price-min');
		var maxIn = price.querySelector('.mrz-product-filter-exp-price-max');
		if (!sliderEl || !minIn || !maxIn) { return; }

		var lo = parseInt(price.getAttribute('data-min'), 10) || 0;
		var hi = parseInt(price.getAttribute('data-max'), 10) || 0;

		$(sliderEl).slider({
			range: true,
			min: lo,
			max: hi,
			values: [parseInt(minIn.value, 10) || lo, parseInt(maxIn.value, 10) || hi],
			slide: function (e, ui) {
				minIn.value = ui.values[0];
				maxIn.value = ui.values[1];
			}
		});
		$(sliderEl).on('slidestop', function () { apply(); });

		[minIn, maxIn].forEach(function (inp) {
			inp.addEventListener('change', function () {
				$(sliderEl).slider('values', [parseInt(minIn.value, 10) || lo, parseInt(maxIn.value, 10) || hi]);
				apply();
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-mrz-product-filter-exp]').forEach(initInstance);
	});
})(window.jQuery);
