(function (Drupal, once) {
	function refreshProgramSwiper() {
		setTimeout(function () {
			if (typeof window.initDuSwiperProgram === 'function') {
				window.initDuSwiperProgram();
				console.log('Swiper de programas reinicializado');
				return;
			}

			if (window.duSwiperProgram && typeof window.duSwiperProgram.update === 'function') {
				window.duSwiperProgram.update();
				console.log('Swiper de programas actualizado');
			}
		}, 300);
	}

	Drupal.behaviors.programasFilterDebug = {
		attach: function (context) {
			once('programasFilterDebug', 'body', context).forEach(function (body) {

				body.addEventListener('click', function (e) {
					const header = e.target.closest('.du-filter-down__header');
					if (header) {
						e.preventDefault();
						e.stopPropagation();

						const currentFilter = header.closest('.du-filter-down');
						if (!currentFilter) {
							console.log('No se encontró currentFilter');
							return;
						}

						console.log('CLICK HEADER');
						console.log('Filtro actual:', currentFilter);
						console.log('Target:', currentFilter.getAttribute('data-target'));

						document.querySelectorAll('.du-seach__content .du-filter-down').forEach(function (filter) {
							if (filter !== currentFilter) {
								filter.classList.remove('active');
							}
						});

						currentFilter.classList.toggle('active');
						console.log('¿Quedó active?', currentFilter.classList.contains('active'));
						return;
					}

					const item = e.target.closest('.du-filter-down__options li');
					if (item) {
						e.preventDefault();
						e.stopPropagation();

						const filter = item.closest('.du-filter-down');
						const wrapper = item.closest('.du-seach__content');
						const form = wrapper ? wrapper.closest('form') : null;

						console.log('CLICK ITEM');
						console.log('LI clickeado:', item);
						console.log('data-value:', item.getAttribute('data-value'));
						console.log('texto:', item.textContent.trim());

						if (!filter || !wrapper || !form) {
							console.log('Faltan nodos filter/wrapper/form', { filter, wrapper, form });
							return;
						}

						const target = filter.getAttribute('data-target');
						const nativeSelect = form.querySelector('select[name="' + target + '"]');
						const title = filter.querySelector('.du-filter-down__title');
						const submitButton = form.querySelector('[data-drupal-selector="edit-submit-dermau-programas"], .form-submit');

						console.log('target:', target);
						console.log('nativeSelect:', nativeSelect);
						console.log('title:', title);
						console.log('submitButton:', submitButton);

						if (!nativeSelect || !title) {
							console.log('No existe nativeSelect o title');
							return;
						}

						console.log('Opciones reales del select:');
						Array.from(nativeSelect.options).forEach(function (option, index) {
							console.log(index, 'value=', option.value, 'text=', option.textContent.trim());
						});

						const value = item.getAttribute('data-value');
						const text = item.textContent.trim();

						console.log('ANTES select.value =', nativeSelect.value);

						const matchingOption = Array.from(nativeSelect.options).find(function (option) {
							return option.value == value;
						});

						console.log('matchingOption:', matchingOption);

						if (!matchingOption) {
							console.log('No existe opción real en select para value=', value);
							return;
						}

						nativeSelect.value = matchingOption.value;
						console.log('DESPUÉS set value, select.value =', nativeSelect.value);

						nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
						console.log('Se disparó change');

						title.textContent = text;
						title.setAttribute('data-value', matchingOption.value);

						filter.classList.remove('active');

						if (submitButton) {
							console.log('Click submit manual');
							submitButton.click();
							refreshProgramSwiper();
						}

						setTimeout(function () {
							const refreshedSelect = document.querySelector('select[name="' + target + '"]');
							console.log('500ms después del click:');
							console.log('refreshedSelect:', refreshedSelect);
							console.log('refreshedSelect value:', refreshedSelect ? refreshedSelect.value : 'no existe');
						}, 500);

						setTimeout(function () {
							const refreshedSelect = document.querySelector('select[name="' + target + '"]');
							console.log('1500ms después del click:');
							console.log('refreshedSelect:', refreshedSelect);
							console.log('refreshedSelect value:', refreshedSelect ? refreshedSelect.value : 'no existe');
						}, 1500);

						return;
					}

					document.querySelectorAll('.du-seach__content .du-filter-down').forEach(function (filter) {
						if (!filter.contains(e.target)) {
							filter.classList.remove('active');
						}
					});
				});

				body.addEventListener('change', function (e) {
					const select = e.target.closest('select');
					if (!select) {
						return;
					}

					console.log('EVENTO CHANGE DETECTADO');
					console.log('select name:', select.name);
					console.log('select value:', select.value);
				});

				body.addEventListener('keyup', function (e) {
					const input = e.target.closest('#program-search, .du-seach__content input[type="text"]');
					if (!input) {
						return;
					}

					const wrapper = input.closest('.du-seach__content');
					const form = wrapper ? wrapper.closest('form') : null;

					console.log('KEYUP SEARCH');
					console.log('valor input:', input.value);

					if (!form) {
						console.log('No hay form en search');
						return;
					}

					const submitButton = form.querySelector('[data-drupal-selector="edit-submit-dermau-programas"], .form-submit');
					if (!submitButton) {
						console.log('No hay submitButton en search');
						return;
					}

					clearTimeout(input._duSearchTimeout);
					input._duSearchTimeout = setTimeout(function () {
						console.log('Click submit por search');
						submitButton.click();
						refreshProgramSwiper();
					}, 500);
				});
			});
		}
	};
})(Drupal, once);