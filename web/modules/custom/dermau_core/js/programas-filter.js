(function ($, Drupal) {

  Drupal.behaviors.programasFilter = {
    attach: function (context) {

      $('.filter-btn', context).once('programasFilter').on('click', function () {

        $('.filter-btn').removeClass('filter-btn--active');
        $(this).addClass('filter-btn--active');

        let tipo = $(this).data('tipo');

        $.ajax({
          url: '/ajax/programas',
          data: { tipo: tipo },
          beforeSend: function() {
            $('.du-oferta__grid').html('<div class="loading">Cargando...</div>');
          },
          success: function (response) {

            let html = '';

            response.forEach(function (programa) {

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

            $('.du-oferta__grid').html(html);

          }
        });

      });

    }
  };

})(jQuery, Drupal);
