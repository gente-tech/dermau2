(function (Drupal, once) {
	Drupal.behaviors.programasFilter = {
		attach: function (context) {
			once('programasFilter', '.du-filters', context).forEach(function (wrapper) {
				const form = wrapper.closest('form');
				const submitButton = form.querySelector('[data-drupal-selector="edit-submit-dermau-programas"], .form-submit');

				wrapper.querySelectorAll('select').forEach(function (select) {
					const allOption = select.querySelector('option[value="All"]');

					if (allOption) {
						if (select.name.includes('field_universidad')) {
							allOption.textContent = 'Todas las universidades';
						}

						if (select.name.includes('field_tipo_de_programa')) {
							allOption.textContent = 'Todos los programas';
						}
					}

					select.addEventListener('change', function () {
						if (submitButton) {
							submitButton.click();
						}
					});
				});

				const input = wrapper.querySelector('input[type="text"]');
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
			});
		}
	};
})(Drupal, once);