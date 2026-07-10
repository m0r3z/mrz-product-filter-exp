(function ($) {
	'use strict';

	// Répéteur de sources : ajout / suppression / tri, et visibilité du
	// sélecteur OU/ET selon le mode d'affichage.
	function initSourcesRepeater() {
		var wrap = document.querySelector('.mrz-product-filter-exp-sources');
		var tpl = document.getElementById('mrz-product-filter-exp-source-template');
		var addBtn = document.querySelector('.mrz-product-filter-exp-source-add');
		if (!wrap || !tpl || !addBtn) { return; }

		function updateLogicVisibility(row) {
			var mode = row.querySelector('.mrz-product-filter-exp-source-mode');
			var logicWrap = row.querySelector('.mrz-product-filter-exp-source-logic-wrap');
			if (!mode || !logicWrap) { return; }
			logicWrap.style.display = (mode.value === 'checkbox') ? '' : 'none';
		}

		function bindRow(row) {
			var removeBtn = row.querySelector('.mrz-product-filter-exp-source-remove');
			if (removeBtn) {
				removeBtn.addEventListener('click', function (e) {
					e.preventDefault();
					row.remove();
				});
			}
			var mode = row.querySelector('.mrz-product-filter-exp-source-mode');
			if (mode) {
				mode.addEventListener('change', function () { updateLogicVisibility(row); });
			}
			updateLogicVisibility(row);
		}

		wrap.querySelectorAll('.mrz-product-filter-exp-source-row').forEach(bindRow);

		addBtn.addEventListener('click', function (e) {
			e.preventDefault();
			var idx = parseInt(wrap.getAttribute('data-next-index') || '0', 10);
			var html = tpl.innerHTML.replace(/__INDEX__/g, String(idx));
			var temp = document.createElement('div');
			temp.innerHTML = html.trim();
			var row = temp.firstElementChild;
			wrap.appendChild(row);
			bindRow(row);
			wrap.setAttribute('data-next-index', String(idx + 1));
		});

		if ($ && $.fn.sortable) {
			$(wrap).sortable({
				handle: '.mrz-product-filter-exp-drag',
				items: '> .mrz-product-filter-exp-source-row',
				axis: 'y'
			});
		}
	}

	// Initialise les WP Color Pickers non encore instanciés.
	function initColorPickers(root) {
		if (!$ || !$.fn.wpColorPicker) { return; }
		$(root || document).find('.mrz-product-filter-exp-color-picker').each(function () {
			if ($(this).closest('.wp-picker-container').length) { return; }
			$(this).wpColorPicker();
		});
	}

	// Attribut couleur : recharge les termes en AJAX et reconstruit les lignes.
	function initTermColors() {
		var sel = document.getElementById('mrz_product_filter_exp_color_attribute');
		var container = document.getElementById('mrz_product_filter_exp_term_colors');
		var tpl = document.getElementById('mrz-product-filter-exp-term-color-template');
		if (!sel || !container || !tpl) { return; }

		var ajaxUrl = container.getAttribute('data-ajax-url');
		var nonce = container.getAttribute('data-nonce');

		function escHtml(s) {
			return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		}

		function buildRow(term) {
			var html = tpl.innerHTML
				.replace(/__TERM_ID__/g, String(term.id))
				.replace(/__TERM_NAME__/g, escHtml(term.name))
				.replace(/__COLOR__/g, (term.color || '').replace(/"/g, '&quot;'));
			var temp = document.createElement('div');
			temp.innerHTML = html.trim();
			return temp.firstElementChild;
		}

		sel.addEventListener('change', function () {
			var tax = sel.value;
			container.innerHTML = '<p class="description">…</p>';
			if (!tax) {
				container.innerHTML = '<p class="description">Sélectionnez d\'abord un attribut ci-dessus.</p>';
				return;
			}
			var form = new FormData();
			form.append('action', 'mrz_product_filter_exp_fetch_terms');
			form.append('nonce', nonce);
			form.append('taxonomy', tax);

			fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: form })
				.then(function (r) { return r.json(); })
				.then(function (json) {
					container.innerHTML = '';
					if (!json || !json.success) {
						container.innerHTML = '<p class="description">Erreur de chargement.</p>';
						return;
					}
					var terms = json.data || [];
					if (!terms.length) {
						container.innerHTML = '<p class="description">Aucun terme dans cet attribut.</p>';
						return;
					}
					terms.forEach(function (t) { container.appendChild(buildRow(t)); });
					initColorPickers(container);
				})
				.catch(function () {
					container.innerHTML = '<p class="description">Erreur réseau.</p>';
				});
		});
	}

	$(function () {
		initSourcesRepeater();
		initColorPickers(document);
		initTermColors();
	});
})(window.jQuery);
