(function (Drupal, once) {
  Drupal.behaviors.phoneInput = {
    attach: function (context) {

      once('phoneInput', '#du-reg-phone', context).forEach(function (input) {

        const iti = window.intlTelInput(input, {
          initialCountry: "co",
          separateDialCode: true,
          utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.11/build/js/utils.js"
        });

        const hiddenInput = document.getElementById('du-reg-indicative');

        // ✅ Set inicial
        const setIndicative = () => {
          const countryData = iti.getSelectedCountryData();
          if(countryData  && hiddenInput)   hiddenInput.value = '+' + countryData.dialCode;
        };

        setIndicative();

        // ✅ Cuando cambia la bandera
        input.addEventListener('countrychange', function () {
          setIndicative();
        });

      });

    }
  };
})(Drupal, once);