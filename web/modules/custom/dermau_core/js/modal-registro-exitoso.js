(function (Drupal, once) {
  Drupal.behaviors.dermauRegistroExitosoModal = {
    attach(context) {
      once('dermauRegistroExitosoModal', 'body', context).forEach(() => {
        const modal = document.getElementById('modal-ok');
        if (!modal) {
          return;
        }

        const closeBtn = modal.querySelector('.du-modal__close');
        const overlay = modal.querySelector('.du-modal__overlay');

        const openModal = () => {
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