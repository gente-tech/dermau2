(function (Drupal, once) {

  Drupal.behaviors.programasFilter = {
    attach: function (context) {

      once('programasFilter', context.querySelectorAll('.filter-btn')).forEach((element) => {

        element.addEventListener('click', function () {

          document.querySelectorAll('.filter-btn')
            .forEach(btn => btn.classList.remove('filter-btn--active'));

          this.classList.add('filter-btn--active');

          let tipo = this.dataset.tipo;

          const grid = document.querySelector('.du-oferta__grid');
          grid.innerHTML = '<div class="loading">Cargando...</div>';

          fetch(`/ajax/programas?tipo=${tipo}`)
            .then(response => response.json())
            .then(data => {

              let html = '';

              data.forEach(programa => {

                html += `
                  <article class="du-card__diplomat">
                    <div class="du-card__diplomat-image-container">
                      <span class="du-card__diplomat-badge">${programa.tipo}</span>
                      <img src="${programa.imagen}" alt="${programa.title}" class="du-card__diplomat-image"/>
                    </div>
                    <div class="du-card__diplomat-content">
                      <h3 class="du-card__diplomat-title">${programa.title}</h3>
                      <p class="du-card__diplomat-description">${programa.descripcion}</p>
                      <div class="du-card__diplomat-info">
                        <div class="du-card__diplomat-info-item">
                          ${programa.duracion}
                        </div>
                        <div class="du-card__diplomat-info-item">
                          ${programa.modulos} módulos
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

            });

        });

      });

    }
  };

})(Drupal, once);
