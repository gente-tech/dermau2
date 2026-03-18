(function (Drupal, once) {
	Drupal.behaviors.dermaThemeConvenios = {
		attach: function (context) {
			const agreementContainers = once(
				'dermaThemeConveniosMainSwiper',
				'#sw-convenio-agreements',
				context
			);

			agreementContainers.forEach(function (agreementContainer) {
				const slides = agreementContainer.querySelectorAll('.swiper-slide');
				const blocks = document.querySelectorAll('.du-convenio-block');

				if (!slides.length) {
					return;
				}

				const duSwiperAgreement = new Swiper(agreementContainer, {
					slidesPerView: 1,
					spaceBetween: 20,
					loop: false,
					navigation: {
						nextEl: agreementContainer.querySelector('.swiper-button-next'),
						prevEl: agreementContainer.querySelector('.swiper-button-prev'),
					},
					breakpoints: {
						768: {
							slidesPerView: 3,
							spaceBetween: 20,
						},
						1024: {
							slidesPerView: 4,
							spaceBetween: 30,
						},
					},
				});

				slides.forEach(function (slide) {
					slide.addEventListener('click', function () {
						const realIndex = Number(slide.getAttribute('data-swiper-slide-index'));

						if (!Number.isNaN(realIndex)) {
							duSwiperAgreement.slideTo(realIndex);
						}

						activarSlide(slide, slides, blocks);
					});
				});

				duSwiperAgreement.on('slideChange', function () {
					const activeIndex = duSwiperAgreement.activeIndex;
					const activeSlide = slides[activeIndex];

					if (activeSlide) {
						activarSlide(activeSlide, slides, blocks);
					}
				});

				const initialSlide =
					agreementContainer.querySelector('.swiper-slide.swiper-slide-active') ||
					slides[0];

				if (initialSlide) {
					activarSlide(initialSlide, slides, blocks);
				}
			});

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

			function activarSlide(slide, slides, blocks) {
				if (!slide) {
					return;
				}

				const id = slide.getAttribute('data-action');
				const targetBlock = id ? document.getElementById(id) : null;

				slides.forEach(function (s) {
					s.classList.remove('swiper-slide-active');
					s.classList.remove('du-agreements__slide');
				});

				blocks.forEach(function (b) {
					b.classList.remove('active');
				});

				slide.classList.add('swiper-slide-active');
				slide.classList.add('du-agreements__slide');

				if (targetBlock) {
					targetBlock.classList.add('active');
				}
			}
		}
	};
})(Drupal, once);