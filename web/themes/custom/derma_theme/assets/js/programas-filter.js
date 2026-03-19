(function (Drupal, once) {
	Drupal.behaviors.programasFilter = {
		attach: function (context) {
			once('programasFilter', '.du-filters', context).forEach(function (wrapper) {

				const form = wrapper.closest('form');

				// Autosubmit en selects
				wrapper.querySelectorAll('select').forEach(function (select) {
					select.addEventListener('change', function () {
						form.submit();
					});
				});

				// Autosubmit en búsqueda (con delay)
				const input = wrapper.querySelector('input[type="text"]');
				if (input) {
					let timeout = null;

					input.addEventListener('keyup', function () {
						clearTimeout(timeout);
						timeout = setTimeout(function () {
							form.submit();
						}, 500);
					});
				}

			});
		}
	};
})(Drupal, once);