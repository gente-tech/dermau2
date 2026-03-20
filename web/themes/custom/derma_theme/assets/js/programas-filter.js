(function (Drupal) {
	function getProgramasForm(element) {
		const form = element.closest('form');
		return form && form.matches('[data-drupal-selector="views-exposed-form-dermau-programas-page-1"]')
			? form
			: null;
	}

	function triggerDrupalAjax(form) {
		if (!form) return;
		const submitButton = form.querySelector('.js-form-submit, .form-submit');
		if (submitButton) {
			submitButton.click();
		}
	}

	function refreshProgramSwiper() {
		const grid = document.querySelector('.du-programs-grid');
		const slider = document.querySelector('.du-swiper-program .swiper');

		if (grid) {
			if (window.duSwiperProgram && typeof window.duSwiperProgram.destroy === 'function') {
				window.duSwiperProgram.destroy(true, true);
				window.duSwiperProgram = null;
			}
			return;
		}

		if (slider && typeof window.initDuSwiperProgram === 'function') {
			window.initDuSwiperProgram();
		}
	}

	function closeAllProgramFilters(scope) {
		(scope || document).querySelectorAll('.du-seach__content .du-filter-down[data-target]').forEach(function (filter) {
			filter.classList.remove('active');
		});
	}

	function initSearchInput(scope) {
		const input = (scope || document).querySelector(
			'.du-seach__content input[name="title"]'
		);

		if (!input) return;

		input.setAttribute('id', 'program-search');
		input.setAttribute('placeholder', 'Buscar programa...');
	}

	Drupal.behaviors.programasFilter = {
		attach: function (context) {
			initSearchInput(context);

			if (!document.body.dataset.programasFilterBound) {
				document.body.dataset.programasFilterBound = 'true';

				document.addEventListener('click', function (e) {
					const header = e.target.closest('.du-seach__content .du-filter-down[data-target] .du-filter-down__header');
					if (header) {
						e.preventDefault();
						e.stopPropagation();

						const currentFilter = header.closest('.du-filter-down');
						if (!currentFilter) return;

						document.querySelectorAll('.du-seach__content .du-filter-down[data-target]').forEach(function (filter) {
							if (filter !== currentFilter) {
								filter.classList.remove('active');
							}
						});

						currentFilter.classList.toggle('active');
						return;
					}

					const item = e.target.closest('.du-seach__content .du-filter-down[data-target] .du-filter-down__options li');
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

						const value = item.getAttribute('data-value');
						const text = item.textContent.trim();

						nativeSelect.value = value;
						title.textContent = text;
						title.setAttribute('data-value', value);

						filter.classList.remove('active');
						triggerDrupalAjax(form);
						return;
					}

					if (!e.target.closest('.du-seach__content .du-filter-down[data-target]')) {
						closeAllProgramFilters();
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
					}
				});

				if (window.jQuery) {
					window.jQuery(document).ajaxComplete(function () {
						initSearchInput(document);
						closeAllProgramFilters(document);
						refreshProgramSwiper();
					});
				}
			}
		}
	};
})(Drupal);