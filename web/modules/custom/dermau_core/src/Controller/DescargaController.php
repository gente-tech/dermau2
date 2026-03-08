<?php

namespace Drupal\dermau_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Response;

class DescargaController extends ControllerBase {

  public function descargar($node) {

    $programa = Node::load($node);

    if ($programa && $programa->hasField('field_pdf_registro')) {

      $file = $programa->get('field_pdf_registro')->entity;

      if ($file) {

        $url = \Drupal::service('file_url_generator')
          ->generateAbsoluteString($file->getFileUri());

        return new Response('
          <html>
          <body>
          <script>

            const link = document.createElement("a");
            link.href = "'.$url.'";
            link.download = "";
            document.body.appendChild(link);
            link.click();

            setTimeout(function(){
              window.location.href="/gracias-por-registrarse";
            }, 2000);

          </script>
          </body>
          </html>
        ');

      }

    }

    return new Response('Error descargando archivo.');

  }

}
