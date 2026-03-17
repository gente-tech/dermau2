(function (Drupal, once) {
	Drupal.behaviors.registroExitosoModal = {
		attach(context) {
			once('registroExitosoModal', 'body', context).forEach(() => {
				const modal = document.getElementById('duRegistroExitosoModal');
				if (!modal) {
					return;
				}

				const params = new URLSearchParams(window.location.search);
				const isSuccess = params.get('registro_exitoso') === '1';

				const openModal = () => {
					modal.classList.add('is-open');
					modal.setAttribute('aria-hidden', 'false');
					document.body.classList.add('du-modal-open');
				};

				const closeModal = () => {
					modal.classList.remove('is-open');
					modal.setAttribute('aria-hidden', 'true');
					document.body.classList.remove('du-modal-open');

					const url = new URL(window.location.href);
					url.searchParams.delete('registro_exitoso');
					window.history.replaceState({}, document.title, url.toString());
				};

				modal.querySelectorAll('[data-modal-close]').forEach((element) => {
					element.addEventListener('click', closeModal);
				});

				document.addEventListener('keydown', (event) => {
					if (event.key === 'Escape' && modal.classList.contains('is-open')) {
						closeModal();
					}
				});

				if (isSuccess) {
					openModal();
				}
			});
		}
	};
})(Drupal, once);