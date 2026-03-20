(function (Drupal, once) {
	function refreshProgramSwiper() {
		setTimeout(function () {
			const hasFilters =
				!!document.querySelector('.du-programs-grid');

			if (hasFilters) {
				if (window.duSwiperProgram && typeof window.duSwiperProgram.destroy === 'function') {
					window.duSwiperProgram.destroy(true, true);
					window.duSwiperProgram = null;
				}
				return;
			}

			if (typeof window.initDuSwiperProgram === 'function') {
				window.initDuSwiperProgram();
			}
		}, 250);
	}

	Drupal.behaviors.programasFilter = {
		attach: function (context) {
			once('programasFilter', 'body', context).forEach(function (body) {

				body.addEventListener('click', function (e) {
					const header = e.target.closest('.du-filter-down__header');
					if (header) {
						e.preventDefault();
						e.stopPropagation();

						const currentFilter = header.closest('.du-filter-down');
						if (!currentFilter) {
							return;
						}

						document.querySelectorAll('.du-seach__content .du-filter-down').forEach(function (filter) {
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
						const wrapper = item.closest('.du-seach__content');
						const form = wrapper ? wrapper.closest('form') : null;

						if (!filter || !form) {
							return;
						}

						const target = filter.getAttribute('data-target');
						const nativeSelect = form.querySelector('select[name="' + target + '"]');
						const title = filter.querySelector('.du-filter-down__title');

						if (!nativeSelect || !title) {
							return;
						}

						const value = item.getAttribute('data-value');
						const text = item.textContent.trim();

						const matchingOption = Array.from(nativeSelect.options).find(function (option) {
							return option.value == value;
						});

						if (!matchingOption) {
							return;
						}

						nativeSelect.value = matchingOption.value;
						title.textContent = text;
						title.setAttribute('data-value', matchingOption.value);

						filter.classList.remove('active');

						nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
						return;
					}

					document.querySelectorAll('.du-seach__content .du-filter-down').forEach(function (filter) {
						if (!filter.contains(e.target)) {
							filter.classList.remove('active');
						}
					});
				});

				body.addEventListener('change', function (e) {
					const select = e.target.closest('.du-seach__content select');
					if (!select) {
						return;
					}

					refreshProgramSwiper();
				});

				body.addEventListener('input', function (e) {
					const input = e.target.closest('#program-search, .du-seach__content input[type="text"]');
					if (!input) {
						return;
					}

					clearTimeout(input._duSearchTimeout);
					input._duSearchTimeout = setTimeout(function () {
						input.dispatchEvent(new Event('change', { bubbles: true }));
						refreshProgramSwiper();
					}, 400);
				});
			});
		}
	};
})(Drupal, once);