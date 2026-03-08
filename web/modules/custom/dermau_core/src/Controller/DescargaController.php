<?php

namespace Drupal\dermau_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DescargaController extends ControllerBase {

  public function descargar($node) {

    $programa = Node::load($node);

    if ($programa && $programa->hasField('field_pdf_registro')) {

      $file = $programa->get('field_pdf_registro')->entity;

      if ($file) {

        $url = \Drupal::service('file_url_generator')
          ->generateAbsoluteString($file->getFileUri());

        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"programa.pdf\"");

        readfile($url);

      }

    }

    return new RedirectResponse('/gracias-por-registrarse');

  }

}
