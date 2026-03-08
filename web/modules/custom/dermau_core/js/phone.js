(function (Drupal, once) {

  Drupal.behaviors.dermauPhone = {

    attach: function (context) {

      once('dermau-phone', context.querySelectorAll('#du-reg-phone')).forEach(function (input) {

        window.intlTelInput(input, {

          initialCountry: "co",

          preferredCountries: ["co","mx","ar","cl"],

          separateDialCode: true,

          nationalMode: false,

          autoPlaceholder: "polite",

          utilsScript:
            "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.11/build/js/utils.js"

        });

      });

    }

  };

})(Drupal, once);
