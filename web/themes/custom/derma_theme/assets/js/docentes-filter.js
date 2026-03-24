(function (Drupal, once) {
  Drupal.behaviors.docentesFilter = {
    attach: function (context) {
      once('docentesFilter', '.du-docentes-filters', context).forEach(function (wrapper) {
        const form = wrapper.closest('form');
        if (!form) {
          return;
        }

        const submitButton = form.querySelector('[data-drupal-selector="edit-submit-dermau-docentes"], .form-submit');

        if (!submitButton) {
          return;
        }

        wrapper.querySelectorAll('select').forEach(function (select) {
          select.addEventListener('change', function () {
            submitButton.click();
          });
        });

        const input = wrapper.querySelector('input[type="text"]');
        if (input) {
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