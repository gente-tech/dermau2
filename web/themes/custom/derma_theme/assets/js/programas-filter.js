(function (Drupal, once) {
	Drupal.behaviors.programasFilter = {
		attach: function (context) {
			once('programasFilterGlobal', 'body', context).forEach(function (body) {

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
						if (!filter) {
							return;
						}

						const wrapper = item.closest('.du-seach__content');
						if (!wrapper) {
							return;
						}

						const form = wrapper.closest('form');
						if (!form) {
							return;
						}

						const target = filter.getAttribute('data-target');
						const nativeSelect = form.querySelector('select[name="' + target + '"]');
						const title = filter.querySelector('.du-filter-down__title');
						const submitButton = form.querySelector('[data-drupal-selector="edit-submit-dermau-programas"], .form-submit');

						if (!nativeSelect || !title) {
							return;
						}

						const value = item.getAttribute('data-value');
						const text = item.textContent.trim();

						const optionExists = Array.from(nativeSelect.options).some(function (option) {
							return option.value == value;
						});

						if (!optionExists) {
							return;
						}

						nativeSelect.value = value;
						nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
						title.textContent = text;
						title.setAttribute('data-value', value);

						filter.classList.remove('active');

						if (submitButton) {
							submitButton.click();
						}
						return;
					}

					document.querySelectorAll('.du-seach__content .du-filter-down').forEach(function (filter) {
						if (!filter.contains(e.target)) {
							filter.classList.remove('active');
						}
					});
				});

				body.addEventListener('keyup', function (e) {
					const input = e.target.closest('#program-search, .du-seach__content input[type="text"]');
					if (!input) {
						return;
					}

					const wrapper = input.closest('.du-seach__content');
					if (!wrapper) {
						return;
					}

					const form = wrapper.closest('form');
					if (!form) {
						return;
					}

					const submitButton = form.querySelector('[data-drupal-selector="edit-submit-dermau-programas"], .form-submit');
					if (!submitButton) {
						return;
					}

					clearTimeout(input._duSearchTimeout);
					input._duSearchTimeout = setTimeout(function () {
						submitButton.click();
					}, 500);
				});
			});
		}
	};
})(Drupal, once);