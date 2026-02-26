(function (Drupal, once) {
  Drupal.behaviors.dermaTheme = {
    attach: function (context) {
      once('dermaThemeInit', 'html', context).forEach(function () {

        /* =========================
           MENU MOBILE
        ========================== */

        const menuToggle = document.getElementById("duMenuToggle");
        const menuClose = document.getElementById("duMenuClose");
        const navList = document.getElementById("duNavList");
        const navOverlay = document.getElementById("duNavOverlay");

        const openMenu = () => {
          if (!navList || !navOverlay) return;
          navList.classList.add("du-header__nav-list--open");
          navOverlay.classList.add("du-header__nav-overlay--active");
          document.body.style.overflow = "hidden";
        };

        const closeMenu = () => {
          if (!navList || !navOverlay) return;
          navList.classList.remove("du-header__nav-list--open");
          navOverlay.classList.remove("du-header__nav-overlay--active");
          document.body.style.overflow = "auto";
        };

        if (menuToggle) menuToggle.addEventListener("click", openMenu);
        if (menuClose) menuClose.addEventListener("click", closeMenu);
        if (navOverlay) navOverlay.addEventListener("click", closeMenu);


        /* =========================
           SWIPERS
        ========================== */

        if (typeof Swiper !== "undefined") {

          if (document.querySelector(".du-hero-swiper .swiper")) {
            new Swiper(".du-hero-swiper .swiper", {
              loop: true,
              speed: 800,
              autoplay: { delay: 5000, disableOnInteraction: false },
              pagination: { el: ".du-hero-swiper .swiper-pagination", clickable: true },
              effect: "fade",
              fadeEffect: { crossFade: true },
            });
          }

          if (document.querySelector(".du-agreements .swiper")) {
            new Swiper(".du-agreements .swiper", {
              loop: true,
              autoplay: { delay: 3000 },
              breakpoints: {
                320: {
                  slidesPerView: 2,
                  spaceBetween: 20,
                  navigation: {
                    nextEl: ".du-agreements .swiper-button-next",
                    prevEl: ".du-agreements .swiper-button-prev",
                  },
                  pagination: { el: ".du-agreements .swiper-pagination", clickable: true },
                },
                992: {
                  slidesPerView: 4,
                  spaceBetween: 40,
                  allowTouchMove: false,
                },
              },
            });
          }

          if (document.querySelector(".du-testimonials .swiper")) {
            new Swiper(".du-testimonials .swiper", {
              slidesPerView: 1,
              spaceBetween: 30,
              loop: true,
              autoplay: { delay: 5000, disableOnInteraction: false },
              pagination: { el: ".du-testimonials__pagination", clickable: true },
              navigation: {
                nextEl: ".du-testimonials__next",
                prevEl: ".du-testimonials__prev",
              },
              breakpoints: {
                992: { slidesPerView: 2, spaceBetween: 40 },
              },
            });
          }

          if (document.querySelector(".du-swiper-expert .swiper")) {
            new Swiper('.du-swiper-expert .swiper', {
              slidesPerView: 1,
              spaceBetween: 20,
              loop: false,
              navigation: {
                nextEl: '.du-swiper-expert .swiper-button-next',
                prevEl: '.du-swiper-expert .swiper-button-prev',
              },
              pagination: {
                el: '.du-swiper-expert .swiper-pagination',
                clickable: true,
              },
              breakpoints: {
                1024: { slidesPerView: 3, spaceBetween: 30 }
              }
            });
          }

          if (document.querySelector(".du-swiper-oferta .swiper")) {
            new Swiper(".du-swiper-oferta .swiper", {
              loop: true,
              autoplay: { delay: 3000 },
              breakpoints: {
                320: {
                  slidesPerView: 2,
                  spaceBetween: 20,
                  pagination: { el: ".du-swiper-oferta .swiper-pagination", clickable: true },
                },
                992: {
                  slidesPerView: 3,
                  spaceBetween: 40,
                  allowTouchMove: false,
                },
              },
            });
          }

        }


        /* =========================
           FAQ TABS
        ========================== */

        const tabBtns = document.querySelectorAll(".du-faq__tab-btn");
        const groups = document.querySelectorAll(".du-faq__group-wrapper");

        if (tabBtns.length > 0) {
          tabBtns.forEach((btn) => {
            btn.addEventListener("click", () => {
              const target = btn.getAttribute("data-tab");

              tabBtns.forEach((b) => b.classList.remove("active"));
              groups.forEach((g) => {
                g.classList.remove("active");
                g.classList.remove("open");
              });

              btn.classList.add("active");

              const targetGroup = document.getElementById(target);
              if (targetGroup) targetGroup.classList.add("active");
            });
          });
        }


        /* =========================
           FAQ MOBILE CATEGORIES
        ========================== */

        document.querySelectorAll(".du-faq__mobile-trigger").forEach((trigger) => {
          trigger.addEventListener("click", () => {
            trigger.parentElement.classList.toggle("is-open");
          });
        });


        /* =========================
           FAQ INTERNAL QUESTIONS
        ========================== */

        document.querySelectorAll(".du-accordion-header").forEach((header) => {
          header.addEventListener("click", () => {
            const item = header.parentElement;
            item.classList.toggle("is-open");
          });
        });


        /* =========================
           ACORDEÓN GENERAL
        ========================== */

        const accordionItems = document.querySelectorAll('.du-accordion-block__item');
        if (accordionItems.length > 1) {
          accordionItems.forEach(item => {
            const header = item.querySelector('.du-accordion-block__header');
            if (!header) return;

            header.addEventListener('click', () => {
              const isOpen = item.classList.contains('is-open');
              accordionItems.forEach(i => i.classList.remove('is-open'));
              if (!isOpen) item.classList.add('is-open');
            });
          });
        }


        /* =========================
           PANEL ACORDEÓN
        ========================== */

        document.querySelectorAll('.du-panel-block__trigger').forEach(trigger => {
          trigger.addEventListener('click', () => {
            const parent = trigger.parentElement;
            const isOpen = parent.classList.contains('is-open');

            document.querySelectorAll('.du-panel-block__item').forEach(item => {
              item.classList.remove('is-open');
            });

            if (!isOpen) parent.classList.add('is-open');
          });
        });

      });
    }
  };
})(Drupal, once);
