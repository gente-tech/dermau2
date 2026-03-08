(function (Drupal, once) {

  Drupal.behaviors.programasFilter = {
    attach: function (context) {

      const buttons = once('programasFilter', context.querySelectorAll('.filter-btn'));

      buttons.forEach((button) => {

        button.addEventListener('click', function () {

          // Activar botón seleccionado
          document.querySelectorAll('.filter-btn')
            .forEach(btn => btn.classList.remove('filter-btn--active'));

          this.classList.add('filter-btn--active');

          const tipo = this.dataset.tipo;
          const grid = document.querySelector('.du-oferta__grid');

          if (!grid) return;

          grid.innerHTML = '<div class="loading">Cargando...</div>';

          fetch(`/ajax/programas?tipo=${tipo}`)
            .then(response => response.json())
            .then(data => {

              let html = '';

              data.forEach(programa => {

                html += `
                  <article class="du-card__diplomat">
                    <div class="du-card__diplomat-image-container">
                      <span class="du-card__diplomat-badge">${programa.tipo ?? ''}</span>
                      <img src="${programa.imagen ?? ''}" alt="${programa.title}" class="du-card__diplomat-image"/>
                    </div>

                    <div class="du-card__diplomat-content">

                      <h3 class="du-card__diplomat-title">
                        ${programa.title}
                      </h3>

                      <p class="du-card__diplomat-description">
                        ${programa.descripcion ?? ''}
                      </p>

                      <div class="du-card__diplomat-info">

                        <div class="du-card__diplomat-info-item">
                          <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                            <path d="M7.22917 13.9583C10.9456 13.9583 13.9583 10.9456 13.9583 7.22917C13.9583 3.51275 10.9456 0.5 7.22917 0.5C3.51275 0.5 0.5 3.51275 0.5 7.22917C0.5 10.9456 3.51275 13.9583 7.22917 13.9583Z" stroke="#007BE3"/>
                            <path d="M10.7916 7.22921H7.42704V4.45837" stroke="#007BE3" stroke-linecap="round"/>
                          </svg>
                          ${programa.duracion ?? ''}
                        </div>

                        <div class="du-card__diplomat-info-item">
                          <svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.5 1.99457C7.5 1.99457 4 -1.00543 0.5 1.49457V12.4946C4 9.99457 7.5 12.4946 7.5 12.4946M7.5 1.99457C7.5 1.99457 11 -1.00543 14.5 1.49457V12.4946C11 9.99457 7.5 12.4946 7.5 12.4946M7.5 1.99457V12.4946" stroke="#007BE3" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          ${programa.modulos ?? 0} módulos
                        </div>

                        <div class="du-card__diplomat-info-item">
                          <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.6111 13.5V12.0555C10.6111 11.2893 10.3067 10.5545 9.76497 10.0128C9.2232 9.47099 8.4884 9.16663 7.72222 9.16663H3.38889C2.62271 9.16663 1.88791 9.47099 1.34614 10.0128C0.804364 10.5545 0.5 11.2893 0.5 12.0555V13.5M10.6111 0.592407C11.2306 0.753008 11.7792 1.11476 12.1709 1.6209C12.5625 2.12703 12.775 2.74888 12.775 3.38885C12.775 4.02882 12.5625 4.65067 12.1709 5.15681C11.7792 5.66294 11.2306 6.0247 10.6111 6.1853M14.9444 13.5V12.0555C14.944 11.4154 14.7309 10.7936 14.3388 10.2877C13.9466 9.78186 13.3975 9.42054 12.7778 9.26052" stroke="#007BE3" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.55558 6.27778C7.15106 6.27778 8.44446 4.98438 8.44446 3.38889C8.44446 1.7934 7.15106 0.5 5.55558 0.5C3.96009 0.5 2.66669 1.7934 2.66669 3.38889C2.66669 4.98438 3.96009 6.27778 5.55558 6.27778Z" stroke="#007BE3" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          <span class="du-card__diplomat-info-text">
                            Cupos limitados
                          </span>
                        </div>

                      </div>

                      <a href="${programa.url}" class="du-card__diplomat-btn">
                        Ver detalle
                      </a>

                    </div>
                  </article>
                `;
              });

              grid.innerHTML = html;

            })
            .catch(() => {
              grid.innerHTML = '<div class="error">Error cargando programas.</div>';
            });

        });

      });

      // 🔥 CARGA INICIAL AUTOMÁTICA
      const activeButton = context.querySelector('.filter-btn--active');
      if (activeButton) {
        activeButton.click();
      }

    }
  };

})(Drupal, once);
