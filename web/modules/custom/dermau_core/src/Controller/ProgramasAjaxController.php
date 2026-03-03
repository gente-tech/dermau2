<?php

namespace Drupal\dermau_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;

class ProgramasAjaxController extends ControllerBase {

  public function loadProgramas() {

    $tipo = \Drupal::request()->query->get('tipo');

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'programa')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, 20)
      ->accessCheck(TRUE);

    if ($tipo && $tipo != 'all') {
      $query->condition('field_tipo_de_programa.target_id', $tipo);
    }

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);

    $data = [];

    foreach ($nodes as $node) {

      $image = '';
      if (!$node->get('field_imagen_programa')->isEmpty()) {
        $file = $node->get('field_imagen_programa')->entity;
        $image = \Drupal::service('file_url_generator')
          ->generateAbsoluteString($file->getFileUri());
      }

      $tipo_term = '';
      if (!$node->get('field_tipo_de_programa')->isEmpty()) {
        $tipo_term = $node->get('field_tipo_de_programa')->entity->label();
      }

      $data[] = [
        'title' => $node->getTitle(),
        'url' => $node->toUrl()->toString(),
        'descripcion' => $node->get('field_descripcion_corta')->value ?? '',
        'duracion' => $node->get('field_duracion_programa')->value ?? '',
        'tipo' => $tipo_term,
        'imagen' => $image,
      ];
    }

    return new JsonResponse($data);
  }

}
