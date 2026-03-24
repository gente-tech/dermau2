(function (Drupal, once) {
	Drupal.behaviors.docentesFilter = {
		attach: function (context) {
			once('docentesFilter', '.du-docentes-filters', context).forEach(function (wrapper) {
				const form = wrapper.closest('form');
				if (!form) {
					return;
				}

				const submitButton = form.querySelector('[data-drupal-selector="edit-submit-dermau-docentes"], .form-submit');

				const universidadSelect = wrapper.querySelector('select[name="field_universidad_target_id"]');
				const programaSelect = wrapper.querySelector('select[name="tipo_programa_docente"], select[name="field_tipo_de_programa_target_id"]');
				const input = wrapper.querySelector('input[type="text"]');

				if (universidadSelect && universidadSelect.options.length) {
					const firstOption = universidadSelect.options[0];
					if (firstOption.text.trim() === '- Any -' || firstOption.text.trim() === 'All') {
						firstOption.text = 'Todas las universidades';
						firstOption.value = 'All';
					}
				}

				if (programaSelect && programaSelect.options.length) {
					const firstOption = programaSelect.options[0];
					if (firstOption.text.trim() === '- Any -' || firstOption.text.trim() === 'All') {
						firstOption.text = 'Todos los programas';
						firstOption.value = 'All';
					}
				}

				if (submitButton && universidadSelect) {
					universidadSelect.addEventListener('change', function () {
						submitButton.click();
					});
				}

				if (submitButton && programaSelect) {
					programaSelect.addEventListener('change', function () {
						submitButton.click();
					});
				}

				if (submitButton && input) {
					let timeout = null;

					input.addEventListener('keyup', function () {
						clearTimeout(timeout);
						timeout = setTimeout(function () {
							submitButton.click();
						}, 500);
					});
				}
			});
		}
	};
})(Drupal, once);