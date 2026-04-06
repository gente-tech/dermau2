(function (Drupal, once) {
  console.log('modal-registro-exitoso.js cargó');
  Drupal.behaviors.dermauRegistroExitosoModal = {
    attach(context) {
      console.log('attach del modal ejecutado');
      once('dermauRegistroExitosoModal', 'body', context).forEach(() => {
        const modal = document.getElementById('modal-ok');
        console.log('modal encontrado:', modal);
        if (!modal) {
          return;
        }

        console.log('data-auto-open:', modal.dataset.autoOpen);

        const closeBtn = modal.querySelector('.du-modal__close');
        const overlay = modal.querySelector('.du-modal__overlay');

        const openModal = () => {
          console.log('abriendo modal');
          modal.style.display = 'flex';
          document.body.style.overflow = 'hidden';
        };

        const closeModal = () => {
          modal.style.display = 'none';
          document.body.style.overflow = 'auto';
        };

        if (modal.dataset.autoOpen === '1') {
          openModal();
        }

        if (closeBtn) {
          closeBtn.addEventListener('click', closeModal);
        }

        if (overlay) {
          overlay.addEventListener('click', closeModal);
        }

        document.addEventListener('keydown', (event) => {
          if (event.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
          }
        });
      });
    }
  };
})(Drupal, once);