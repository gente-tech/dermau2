(function (Drupal, once) {
	Drupal.behaviors.dermaThemeConvenios = {
		attach: function (context) {
			once('dermaThemeConveniosExpertSwiper', '.du-swiper-expert', context).forEach(function (expertWrapper) {
				const swiperElement = expertWrapper.querySelector('.swiper');
				const nextButton = expertWrapper.querySelector('.swiper-button-next');
				const prevButton = expertWrapper.querySelector('.swiper-button-prev');
				const paginationElement = expertWrapper.querySelector('.swiper-pagination');

				if (!swiperElement) {
					return;
				}

				new Swiper(swiperElement, {
					slidesPerView: 1,
					spaceBetween: 20,
					loop: false,
					navigation: {
						nextEl: nextButton,
						prevEl: prevButton,
					},
					pagination: {
						el: paginationElement,
						clickable: true,
						renderBullet: function (index, className) {
							return `<span class="${className}" data-swiper-slide-index="${index}">${index + 1}</span>`;
						},
					},
					breakpoints: {
						1024: {
							slidesPerView: 4,
							spaceBetween: 30,
						},
					},
				});
			});
		}
	};
})(Drupal, once);