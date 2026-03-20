(function (Drupal, once) {
	function refreshProgramSwiper() {
		setTimeout(function () {
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
		}, 250);
	}

	function triggerDrupalAjax(form) {
		if (!form) return;

		const submitButton = form.querySelector('.js-form-submit');
		if (submitButton) {
			submitButton.click();
		}
	}

	Drupal.behaviors.programasFilter = {
		attach: function (context) {
			once('programasFilter', '.du-seach__content', context).forEach(function (wrapper) {
				const form = wrapper.closest('form');
				if (!form) {
					return;
				}

				const searchInput = form.querySelector('input[name="title"]');

				wrapper.addEventListener('click', function (e) {
					const header = e.target.closest('.du-filter-down__header');
					if (header) {
						e.preventDefault();
						e.stopPropagation();

						const currentFilter = header.closest('.du-filter-down');
						if (!currentFilter) return;

						wrapper.querySelectorAll('.du-filter-down').forEach(function (filter) {
							if (filter !== currentFilter) {
								filter.classList.remove('active');
							}
						});

						currentFilter.classList.toggle('active');
						return;
					}

					const item = e.target.closest('.du-filter-down__options li');
					if (item) {
						e.preventDefault();
						e.stopPropagation();

						const filter = item.closest('.du-filter-down');
						if (!filter) return;

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
						refreshProgramSwiper();
						return;
					}

					wrapper.querySelectorAll('.du-filter-down').forEach(function (filter) {
						if (!filter.contains(e.target)) {
							filter.classList.remove('active');
						}
					});
				});

				if (searchInput) {
					let timeout = null;

					searchInput.removeAttribute('id');
					searchInput.setAttribute('id', 'program-search');
					searchInput.setAttribute('placeholder', 'Buscar programa...');

					searchInput.addEventListener('input', function () {
						clearTimeout(timeout);
						timeout = setTimeout(function () {
							triggerDrupalAjax(form);
							refreshProgramSwiper();
						}, 500);
					});
				}
			});
		}
	};
})(Drupal, once);