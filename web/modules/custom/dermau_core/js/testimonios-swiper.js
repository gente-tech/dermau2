(function (Drupal, once) {
  Drupal.behaviors.testimoniosSwiper = {
    attach: function (context) {
      once('testimoniosSwiper', '.du-testimonials__swiper', context).forEach(function (swiperElement) {
        const wrapper = swiperElement.closest('.du-testimonials__slider-wrapper');
        const slidesPerView = parseInt(swiperElement.dataset.slidesPerView || '2', 10);
        const contentAlignment = swiperElement.dataset.contentAlignment || 'full';

        new Swiper(swiperElement, {
          loop: true,
          slidesPerView: slidesPerView,
          slidesPerGroup: 1,
          spaceBetween: slidesPerView === 1 ? 0 : 40,
          centeredSlides: false,
          watchOverflow: true,
          navigation: {
            nextEl: ".du-testimonials__next",
            prevEl: ".du-testimonials__prev",
          },
          pagination: { el: ".du-testimonials__pagination", clickable: true },
          breakpoints: {
            0: {
              slidesPerView: 1,
              slidesPerGroup: 1,
              spaceBetween: 0,
            },
            768: {
              slidesPerView: slidesPerView,
              slidesPerGroup: 1,
              spaceBetween: slidesPerView === 1 ? 0 : 40,
            },
            1024: {
              slidesPerView: slidesPerView,
              slidesPerGroup: 1,
              spaceBetween: slidesPerView === 1 ? 0 : 40,
            }
          }
        });
      });
    }
  };
})(Drupal, once);