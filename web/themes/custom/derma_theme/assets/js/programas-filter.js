(function (Drupal, once) {
	function getProgramasForm(element) {
		if (!element) return null;

		const form = element.closest('form');
		if (!form) return null;

		if (
			form.matches('[data-drupal-selector="views-exposed-form-dermau-programas-page-1"]') ||
			form.querySelector('[data-drupal-selector="edit-submit-dermau-programas"]')
		) {
			return form;
		}

		return null;
	}

	function getSubmitButton(form) {
		if (!form) return null;

		return form.querySelector(
			'[data-drupal-selector="edit-submit-dermau-programas"], .js-form-submit, .form-submit'
		);
	}

	function triggerDrupalAjax(form) {
		const submitButton = getSubmitButton(form);
		if (submitButton) {
			submitButton.click();
		}
	}

	function closeAllProgramFilters(scope) {
		(scope || document)
			.querySelectorAll('.du-seach__content .du-filter-down[data-target]')
			.forEach(function (filter) {
				filter.classList.remove('active');
				filter.classList.remove('open');
			});
	}

	function refreshProgramSwiper() {
		const grid = document.querySelector('.du-programs-grid');
		const slider = document.querySelector('.du-swiper-program .swiper');

		if (window.duSwiperProgram && typeof window.duSwiperProgram.destroy === 'function') {
			window.duSwiperProgram.destroy(true, true);
			window.duSwiperProgram = null;
		}

		if (grid) {
			return;
		}

		if (slider && typeof window.initDuSwiperProgram === 'function') {
			window.initDuSwiperProgram();
		}
	}

	function normalizeSelectOptions(scope) {
		(scope || document).querySelectorAll('.du-filter-native select').forEach(function (select) {
			const allOption = select.querySelector('option[value="All"], option[value="_none"], option[value=""]');

			if (allOption) {
				if (select.name.indexOf('field_universidad_del_programa_target_id_verf') !== -1) {
					allOption.textContent = 'Todas las universidades';
				}

				if (select.name.indexOf('field_tipo_de_programa_target_id') !== -1) {
					allOption.textContent = 'Todos los programas';
				}
			}
		});
	}

	function initSearchInput(scope) {
		const input = (scope || document).querySelector('.du-seach__content input[name="title"]');

		if (!input) return;

		input.setAttribute('id', 'program-search');
		input.setAttribute('placeholder', 'Buscar programa...');
		input.setAttribute('autocomplete', 'off');
	}

	function syncCustomFilters(scope) {
		const root = scope || document;
		const form = root.querySelector(
			'form[data-drupal-selector="views-exposed-form-dermau-programas-page-1"]'
		) || document.querySelector(
			'form[data-drupal-selector="views-exposed-form-dermau-programas-page-1"]'
		);

		if (!form) return;

		root.querySelectorAll('.du-seach__content .du-filter-down[data-target]').forEach(function (filter) {
			const target = filter.getAttribute('data-target');
			const nativeSelect = form.querySelector('select[name="' + target + '"]');
			const title = filter.querySelector('.du-filter-down__title');

			if (!nativeSelect || !title) return;

			const currentValue = nativeSelect.value || 'All';
			const selectedOption = nativeSelect.querySelector(
				'option[value="' + CSS.escape(currentValue) + '"]'
			);

			if (
				selectedOption &&
				selectedOption.textContent.trim() !== '' &&
				selectedOption.textContent.trim() !== '- Any -'
			) {
				title.textContent = selectedOption.textContent.trim();
				title.setAttribute('data-value', currentValue);
			} else if (target === 'field_universidad_del_programa_target_id_verf') {
				title.textContent = 'Todas las universidades';
				title.setAttribute('data-value', 'All');
			} else if (target === 'field_tipo_de_programa_target_id') {
				title.textContent = 'Todos los programas';
				title.setAttribute('data-value', 'All');
			}
		});
	}

	function bindDocumentEvents() {
		if (document.body.dataset.programasFilterBound === 'true') {
			return;
		}

		document.body.dataset.programasFilterBound = 'true';

		document.addEventListener('click', function (e) {
			const header = e.target.closest(
				'.du-seach__content .du-filter-down[data-target] .du-filter-down__header'
			);

			if (header) {
				e.preventDefault();
				e.stopPropagation();

				const currentFilter = header.closest('.du-filter-down');
				if (!currentFilter) return;

				document
					.querySelectorAll('.du-seach__content .du-filter-down[data-target]')
					.forEach(function (filter) {
						if (filter !== currentFilter) {
							filter.classList.remove('active');
							filter.classList.remove('open');
						}
					});

				const willOpen = !currentFilter.classList.contains('active');
				currentFilter.classList.toggle('active', willOpen);
				currentFilter.classList.toggle('open', willOpen);
				return;
			}

			const item = e.target.closest(
				'.du-seach__content .du-filter-down[data-target] .du-filter-down__options li'
			);

			if (item) {
				e.preventDefault();
				e.stopPropagation();

				const filter = item.closest('.du-filter-down');
				const form = getProgramasForm(item);

				if (!filter || !form) return;

				const target = filter.getAttribute('data-target');
				const nativeSelect = form.querySelector('select[name="' + target + '"]');
				const title = filter.querySelector('.du-filter-down__title');

				if (!nativeSelect || !title) return;

				const rawValue = item.getAttribute('data-value');
				const value = rawValue === null || rawValue === '' ? 'All' : rawValue;
				const text = item.textContent.trim();

				if (nativeSelect.value === value && title.textContent.trim() === text) {
					filter.classList.remove('active');
					filter.classList.remove('open');
					return;
				}

				nativeSelect.value = value;
				nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));

				title.textContent = text;
				title.setAttribute('data-value', value);

				filter.classList.remove('active');
				filter.classList.remove('open');

				triggerDrupalAjax(form);
				return;
			}

			if (!e.target.closest('.du-seach__content .du-filter-down[data-target]')) {
				closeAllProgramFilters(document);
			}
		});

		document.addEventListener('input', function (e) {
			const input = e.target.closest('.du-seach__content input[name="title"]');
			if (!input) return;

			const form = getProgramasForm(input);
			if (!form) return;

			clearTimeout(input._duSearchTimeout);
			input._duSearchTimeout = setTimeout(function () {
				triggerDrupalAjax(form);
			}, 500);
		});

		document.addEventListener('keydown', function (e) {
			const input = e.target.closest('.du-seach__content input[name="title"]');
			if (!input) return;

			if (e.key === 'Enter') {
				e.preventDefault();

				clearTimeout(input._duSearchTimeout);

				const form = getProgramasForm(input);
				if (!form) return;

				triggerDrupalAjax(form);
			}
		});

		if (window.jQuery) {
			window.jQuery(document).ajaxComplete(function () {
				normalizeSelectOptions(document);
				initSearchInput(document);
				syncCustomFilters(document);
				closeAllProgramFilters(document);
				refreshProgramSwiper();
			});
		}
	}

	Drupal.behaviors.programasFilter = {
		attach: function (context) {
			once('programasFilterInit', 'form[data-drupal-selector="views-exposed-form-dermau-programas-page-1"]', context).forEach(function (form) {
				normalizeSelectOptions(form);
				initSearchInput(form);
				syncCustomFilters(form);
			});

			bindDocumentEvents();
		}
	};
})(Drupal, once);