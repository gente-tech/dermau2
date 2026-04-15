(function (Drupal, once) {
  Drupal.behaviors.testimoniosSwiper = {
    attach: function (context) {
      once('testimoniosSwiper', '.du-testimonials__swiper', context).forEach(function (swiperElement) {
        new Swiper(swiperElement, {
          loop: true,
          slidesPerView: 1,
          slidesPerGroup: 1,
          spaceBetween: 0,
          navigation: {
            nextEl: swiperElement.closest('.du-testimonials__slider-wrapper').querySelector('.du-testimonials__next'),
            prevEl: swiperElement.closest('.du-testimonials__slider-wrapper').querySelector('.du-testimonials__prev'),
          },
          pagination: {
            el: swiperElement.closest('.du-testimonials__slider-wrapper').querySelector('.du-testimonials__pagination'),
            clickable: true,
          },
          breakpoints: {
            0: {
              slidesPerView: 1,
              slidesPerGroup: 1,
            },
            768: {
              slidesPerView: 1,
              slidesPerGroup: 1,
            },
            1024: {
              slidesPerView: 1,
              slidesPerGroup: 1,
            }
          }
        });
      });
    }
  };
})(Drupal, once);