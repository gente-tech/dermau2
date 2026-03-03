(function (Drupal) {
  Drupal.behaviors.conveniosSwiper = {
    attach: function (context) {

      if (typeof Swiper !== 'undefined') {

        new Swiper('.du-agreements .swiper', {
          slidesPerView: 4,
          spaceBetween: 30,
          loop: true,
          navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          },
          pagination: {
            el: '.swiper-pagination',
            clickable: true,
          },
          breakpoints: {
            320: { slidesPerView: 1 },
            768: { slidesPerView: 2 },
            1024: { slidesPerView: 4 }
          }
        });

      }

    }
  };
})(Drupal);
