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
                          <svg width="20" height="16" viewBox="0 0 20 16" fill="none">
                            <path d="M0 0.625H6.8725C8.1775 0.625 9.32667 1.29167 9.99833 2.30333C10.3404 1.78718 10.805 1.36378 11.3506 1.07092C11.8962 0.778058 12.5058 0.624859 13.125 0.625H19.375V13.125H12.9692C12.638 13.125 12.3097 13.1883 12.0033 13.3139C11.6968 13.4395 11.4184 13.6247 11.1842 13.8588L10.4425 14.6013L9.55833 13.8505C8.33793 12.63 7.70215 12.3667 7.03917 12.3667H0.625V0.625Z" fill="#007BE3"/>
                          </svg>
                          ${programa.modulos ?? 0} módulos
                        </div>

                        <div class="du-card__diplomat-info-item">
                          <svg width="16" height="14" viewBox="0 0 16 14" fill="none">
                            <path d="M10.6111 13.5V12.0555C10.6111 11.2893 10.3067 10.5545 9.76497 10.0128C9.2232 9.47099 8.4884 9.16663 7.72222 9.16663H3.38889C2.62271 9.16663 1.88791 9.47099 1.34614 10.0128C0.804364 10.5545 0.5 11.2893 0.5 12.0555V13.5" stroke="#007BE3" stroke-linecap="round"/>
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
