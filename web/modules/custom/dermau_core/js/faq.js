(function (Drupal) {
  Drupal.behaviors.faqBehavior = {
    attach: function (context) {

      const tabs = context.querySelectorAll('.du-faq__tab-btn');
      const groups = context.querySelectorAll('.du-faq__group-wrapper');

      tabs.forEach(tab => {
        tab.addEventListener('click', function () {

          tabs.forEach(t => t.classList.remove('active'));
          groups.forEach(g => g.classList.remove('active'));

          this.classList.add('active');

          const id = this.dataset.tab;
          const target = context.querySelector('#' + id);

          if (target) {
            target.classList.add('active');
          }
        });
      });

      const headers = context.querySelectorAll('.du-accordion-header');

      headers.forEach(header => {
        header.addEventListener('click', function () {
          this.parentElement.classList.toggle('open');
        });
      });

    }
  };
})(Drupal);
