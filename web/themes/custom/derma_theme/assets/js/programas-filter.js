(function (Drupal, once) {
	Drupal.behaviors.programasFilter = {
		attach: function (context) {
			once('programasFilter', '.du-seach__content', context).forEach(function (wrapper) {
				const form = wrapper.closest('form');
				const submitButton = form.querySelector('[data-drupal-selector="edit-submit-dermau-programas"], .form-submit');

				const input = wrapper.querySelector('#program-search, input[type="text"]');
				if (input) {
					let timeout = null;

					input.addEventListener('keyup', function () {
						clearTimeout(timeout);
						timeout = setTimeout(function () {
							if (submitButton) {
								submitButton.click();
							}
						}, 500);
					});
				}

				wrapper.querySelectorAll('.du-filter-down').forEach(function (filter) {
					const header = filter.querySelector('.du-filter-down__header');
					const title = filter.querySelector('.du-filter-down__title');
					const items = filter.querySelectorAll('.du-filter-down__options li');
					const target = filter.getAttribute('data-target');
					const nativeSelect = form.querySelector('select[name="' + target + '"]');

					if (!header || !title || !items.length || !nativeSelect) {
						return;
					}

					header.addEventListener('click', function (e) {
						e.stopPropagation();

						wrapper.querySelectorAll('.du-filter-down').forEach(function (otherFilter) {
							if (otherFilter !== filter) {
								otherFilter.classList.remove('active');
							}
						});

						filter.classList.toggle('active');
					});

					items.forEach(function (item) {
						item.addEventListener('click', function (e) {
							e.stopPropagation();

							const value = this.getAttribute('data-value');
							const text = this.textContent.trim();

							title.textContent = text;
							title.setAttribute('data-value', value);

							nativeSelect.value = value;
							nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));

							filter.classList.remove('active');

							if (submitButton) {
								submitButton.click();
							}
						});
					});
				});

				document.addEventListener('click', function () {
					wrapper.querySelectorAll('.du-filter-down').forEach(function (filter) {
						filter.classList.remove('active');
					});
				});
			});
		}
	};
})(Drupal, once);