<?php

namespace Drupal\dermau_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;

/**
 * Controller for AJAX Programas listing.
 */
class ProgramasAjaxController extends ControllerBase {

  /**
   * Load programas via AJAX.
   */
  public function loadProgramas(Request $request) {

    $tipo = $request->query->get('tipo');

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'programa')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, 20)
      ->accessCheck(TRUE);

    // Filtro por tipo de programa (taxonomy)
    if (!empty($tipo) && $tipo !== 'all') {
      $query->condition('field_tipo_de_programa.target_id', $tipo);
    }

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);

    $data = [];

    foreach ($nodes as $node) {

      // Imagen
      $imagen = '';
      if (!$node->get('field_imagen_programa')->isEmpty()) {
        $file = $node->get('field_imagen_programa')->entity;
        if ($file) {
          $imagen = \Drupal::service('file_url_generator')
            ->generateAbsoluteString($file->getFileUri());
        }
      }

      // Tipo programa (taxonomy label)
      $tipo_label = '';
      if (!$node->get('field_tipo_de_programa')->isEmpty()) {
        $term = $node->get('field_tipo_de_programa')->entity;
        if ($term) {
          $tipo_label = $term->label();
        }
      }

      // Duración
      $duracion = $node->get('field_duracion_programa')->value ?? '';

      // Descripción corta
      $descripcion = $node->get('field_descripcion_corta')->value ?? '';

      // Conteo automático de módulos (Paragraphs)
      $modulos_count = 0;
      if ($node->hasField('field_modulos') && !$node->get('field_modulos')->isEmpty()) {
        $modulos_count = count($node->get('field_modulos')->getValue());
      }

      $data[] = [
        'title' => $node->getTitle(),
        'url' => $node->toUrl()->toString(),
        'descripcion' => $descripcion,
        'duracion' => $duracion,
        'tipo' => $tipo_label,
        'imagen' => $imagen,
        'modulos' => $modulos_count,
      ];
    }

    return new JsonResponse($data);
  }

}
