(function ($, Drupal) {

  Drupal.behaviors.programasFilter = {
    attach: function (context) {

      $('.du-filter-button', context).once('programasFilter').on('click', function () {

        let tipo = $(this).data('tipo');

        $.ajax({
          url: '/ajax/programas',
          data: { tipo: tipo },
          success: function (response) {

            let html = '';

            response.forEach(function (programa) {

              html += `
                <div class="du-program-card">
                  <img src="${programa.imagen}" />
                  <span class="badge">${programa.tipo}</span>
                  <h3>${programa.title}</h3>
                  <p>${programa.descripcion}</p>
                  <div class="meta">
                    <span>${programa.duracion}</span>
                  </div>
                  <a href="${programa.url}" class="du-btn">Ver detalle</a>
                </div>
              `;
            });

            $('.du-programas-grid').html(html);

          }
        });

      });

    }
  };

})(jQuery, Drupal);
