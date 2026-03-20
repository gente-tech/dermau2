const header = document.querySelector(".du-header");
const menuToggle = document.getElementById("duMenuToggle");
const navList = document.getElementById("duNavList");
const navOverlay = document.getElementById("duNavOverlay");

const openMenu = () => {
  header.setAttribute("data-open", "true");
  navList.classList.add("du-header__nav-list--open");
  navOverlay.classList.add("du-header__nav-overlay--active");
  document.body.style.overflow = "hidden"; // Evita scroll al estar abierto
};

const closeMenu = () => {
  header.setAttribute("data-open", "false");
  navList.classList.remove("du-header__nav-list--open");
  navOverlay.classList.remove("du-header__nav-overlay--active");
  document.body.style.overflow = "auto";
};

if (menuToggle) {
  menuToggle.addEventListener("click", openMenu);
  const menuClose = document.getElementById("duMenuClose");
  if (menuClose) menuClose.addEventListener("click", closeMenu);
}

if (navOverlay) navOverlay.addEventListener("click", closeMenu);


/* Swipers  sliders */

const duSwiperHero = new Swiper(".du-hero-swiper .swiper", {
  loop: false,
  speed: 800,
  autoplay: { delay: 5000, disableOnInteraction: false },
  pagination: { el: ".du-hero-swiper .swiper-pagination", clickable: true },
  effect: "fade",
  fadeEffect: { crossFade: true },
});

const duSwiperAgreement = new Swiper(".du-agreements .swiper", {
  loop: false,
  autoplay: { delay: 3000 },
  breakpoints: {
    320: {
      slidesPerView: 1,
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

const duSwiperTestimonials = new Swiper(".du-testimonials .swiper", {
  slidesPerView: 1,
  spaceBetween: 30,
  loop: false,
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


const duSwiperExperts = new Swiper('.du-swiper-expert .swiper', {
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
    renderBullet: function (index, className) {
      let bullet = `<span class="${className}" data-swiper-slide-index="${index}">${index + 1}</span>`;
      /*let totalSlides = this.slides.length;
     if (index === 0)  bullet = `<span class="nav-label" onclick="duSwiperExperts.slideTo(0)" data-swiper-slide-index="${index}"> < </span>` + bullet;
      if (index === totalSlides - 1)  bullet = bullet + `<span class="nav-label" onclick="duSwiperExperts.slideTo(${totalSlides - 1})" data-swiper-slide-index="${index}"> > </span>`;*/
      return bullet;
    },
  },
  breakpoints: {
    1024: { slidesPerView: 3, spaceBetween: 30 }
  }
});


const duSwiperOferta = new Swiper(".du-swiper-oferta .swiper", {
  loop: false,
  autoplay: { delay: 3000 },
  breakpoints: {
    320: {
      slidesPerView: 1.2,
      spaceBetween: 20,
      pagination: { el: ".du-swiper-oferta .swiper-pagination", clickable: true },
    },
    992: {
      slidesPerView: 4,
      spaceBetween: 40,
      allowTouchMove: false,
    },
  },
});

window.initDuSwiperProgram = function () {
  const swiperElement = document.querySelector('.du-swiper-program .swiper');

  if (!swiperElement) {
    return;
  }

  if (window.duSwiperProgram && typeof window.duSwiperProgram.destroy === 'function') {
    window.duSwiperProgram.destroy(true, true);
  }

  window.duSwiperProgram = new Swiper('.du-swiper-program .swiper', {
    loop: false,
    slidesPerView: 1,
    spaceBetween: 20,
    navigation: {
      nextEl: '.du-swiper-program .swiper-button-next',
      prevEl: '.du-swiper-program .swiper-button-prev',
    },
    pagination: {
      el: '.du-swiper-program .swiper-pagination',
      clickable: true,
      renderBullet: function (index, className) {
        return `<span class="${className}" data-swiper-slide-index="${index}">${index + 1}</span>`;
      },
    },
    breakpoints: {
      1024: {
        slidesPerView: 4,
        spaceBetween: 30,
        grid: {
          rows: 2,
          fill: 'row'
        },
      }
    }
  });
}

window.initDuSwiperProgram();



/* FAQ Interacciones */
document.addEventListener("DOMContentLoaded", () => {
  // Tabs Desktop
  const tabBtns = document.querySelectorAll(".du-faq__tab-btn");
  const groups = document.querySelectorAll(".du-faq__group-wrapper");

  if (tabBtns.length > 0)
    tabBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        const target = btn.getAttribute("data-tab");
        tabBtns.forEach((b) => b.classList.remove("active"));
        groups.forEach((g) => {
          g.classList.remove("active");
          g.classList.remove("open"); // Reset en caso de venir de móvil
        });

        btn.classList.add("active");
        document.getElementById(target).classList.add("active");
      });
    });

  // Mobile Acordeón Categorías
  document.querySelectorAll(".du-faq__mobile-trigger").forEach((trigger) => {
    trigger.addEventListener("click", () => {
      trigger.parentElement.classList.toggle("is-open");
    });
  });

  // Preguntas Internas
  document.querySelectorAll(".du-accordion-header").forEach((header) => {
    header.addEventListener("click", () => {
      const item = header.parentElement;
      item.classList.toggle("is-open");
    });
  });
});

/* Acordeón General */
document.addEventListener('DOMContentLoaded', () => {
  const accordionItems = document.querySelectorAll('.du-accordion-block__item');
  if (accordionItems.length > 1)
    accordionItems.forEach(item => {
      const header = item.querySelector('.du-accordion-block__header');
      header.addEventListener('click', () => {
        const isOpen = item.classList.contains('is-open');
        accordionItems.forEach(i => i.classList.remove('is-open'));
        if (!isOpen) item.classList.add('is-open');
      });
    });
});

/* Panel Acordeón */
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

/* Dropdowns */
document.addEventListener("DOMContentLoaded", () => {
  const dropdowns = document.querySelectorAll(".du-filter-down");
  if (!dropdowns) return;
  dropdowns.forEach(dropdown => {
    const header = dropdown.querySelector(".du-filter-down__header");
    const options = dropdown.querySelector(".du-filter-down__options");
    const title = dropdown.querySelector(".du-filter-down__title");
    if (!header || !options || !title) return;
    header.addEventListener("click", (e) => {
      e.stopPropagation();
      dropdowns.forEach(d => {
        if (d !== dropdown) d.classList.remove("open");
      });
      dropdown.classList.toggle("open");
    });
    options.querySelectorAll("li").forEach(option => {
      option.addEventListener("click", () => {
        const value = option.getAttribute("data-value");
        const text = option.textContent.trim();
        title.textContent = text;
        title.setAttribute("data-value", value);
        dropdown.classList.remove("open");
      });
    });
  });
  document.addEventListener("click", () => {
    dropdowns.forEach(dropdown => dropdown.classList.remove("open"));
  });
});


/*  Selector de País */
function initCountrySelect(selectId, inputId, countryArr, baseUrl) {
  const selectContainer = document.getElementById(selectId);
  if (!selectContainer) return;

  const selected = selectContainer.querySelector('.selected');
  const optionsContainer = selectContainer.querySelector('.options');
  const telfInput = document.getElementById(inputId);

  // Cargar opciones dinámicamente
  countryArr.forEach((c, index) => {
    const option = document.createElement('div');
    option.classList.add('option');
    option.setAttribute('data-value', c.code);
    option.innerHTML = `<img src="${baseUrl}${c.icon}" alt="${c.name}">`;
    optionsContainer.appendChild(option);
    if (index === 0) {
      selected.setAttribute('data-value', c.code);
      selected.innerHTML = option.innerHTML;
      telfInput.value = c.code + ' ';
    }
    option.addEventListener('click', () => {
      selected.setAttribute('data-value', c.code);
      selected.innerHTML = option.innerHTML;
      telfInput.value = c.code + ' ' + telfInput.value.replace(/^\+\d+\s*/, '');
      optionsContainer.style.display = 'none';
    });
  });
  selected.addEventListener('click', () => {
    optionsContainer.style.display =
      optionsContainer.style.display === 'block' ? 'none' : 'block';
  });
}

const countryArr = [
  { name: 'Colombia', code: '+57', icon: 'co.png' },
  { name: 'Venezuela', code: '+58', icon: 've.png' },
  { name: 'México', code: '+52', icon: 'mx.png' },
  { name: 'Argentina', code: '+54', icon: 'ar.png' },
  { name: 'Chile', code: '+56', icon: 'cl.png' },
  /*{name: 'Perú', code: '+51', icon: 'pe.png'},
  {name: 'Ecuador', code: '+593', icon: 'ec.png'},
  {name: 'Uruguay', code: '+598', icon: 'uy.png'},
  {name: 'Paraguay', code: '+595', icon: 'py.png'},
  {name: 'Bolivia', code: '+591', icon: 'bo.png'}*/
];

initCountrySelect('select-country', 'du-reg-phone', countryArr, 'https://flagcdn.com/w40/');


/* btn action form float */
document.addEventListener("DOMContentLoaded", () => {
  const btnChat = document.querySelector(".du-float-chat");
  const btnTitle = document.querySelector(".du-form-register__subtitle");
  const isMobile = window.innerWidth < 992;
  const toScroll = 150;
  if (!btnChat || isMobile) return;

  let lastScrollState = null; // null, "hidden", "shown"

  function formShow(isUser = false) {
    if (isMobile) return;
    const form = document.querySelector(".du-form-register__form");
    if (form && isUser) form.classList.toggle("max-h-0");
    else if (form && !isUser) {
      if (lastScrollState === "hidden") form.classList.add("max-h-0");
      else form.classList.remove("max-h-0");
    }
  }

  // Acción al hacer click (usuario)
  btnChat.addEventListener("click", (e) => {
    e.preventDefault();
    formShow(true);
  });

  btnTitle.addEventListener("click", () => {
    formShow(true);
  });

  // Mostrar/ocultar según scroll (automático)
  window.addEventListener("scroll", () => {
    if (isMobile) return;
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    if (scrollTop >= toScroll && lastScrollState !== "hidden") {
      btnChat.classList.add("enfasis");
      lastScrollState = "hidden";
      formShow(false);
    } else if (scrollTop < toScroll && lastScrollState !== "shown") {
      // mostrar solo una vez
      btnChat.classList.remove("enfasis");
      lastScrollState = "shown";
      formShow(false);
    }
  });
});
