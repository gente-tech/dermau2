(function (Drupal, once) {
  Drupal.behaviors.dermauPhoneInput = {
    attach(context) {
      once('dermauPhoneInput', '#du-reg-phone', context).forEach(function (input) {
        if (typeof window.intlTelInput !== 'function') {
          return;
        }

        const indicativeHidden = document.getElementById('du-reg-indicative');

        const iti = window.intlTelInput(input, {
          initialCountry: 'co',
          nationalMode: true,
          separateDialCode: true,
          autoPlaceholder: 'off',
          formatOnDisplay: false,
          utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.11/build/js/utils.js'
        });

        function syncIndicative() {
          const countryData = iti.getSelectedCountryData();
          const dialCode = countryData && countryData.dialCode ? `+${countryData.dialCode}` : '+57';

          if (indicativeHidden) {
            indicativeHidden.value = dialCode;
          }
        }

        syncIndicative();

        input.addEventListener('countrychange', function () {
          syncIndicative();
        });

        input.addEventListener('input', function () {
          this.value = this.value.replace(/\D+/g, '');
        });

        input.addEventListener('paste', function () {
          const field = this;
          setTimeout(function () {
            field.value = field.value.replace(/\D+/g, '');
          }, 0);
        });

        input.addEventListener('keydown', function (event) {
          if (event.key === '+' || event.key === '-' || event.key === 'e' || event.key === 'E') {
            event.preventDefault();
          }
        });
      });
    }
  };
})(Drupal, once);