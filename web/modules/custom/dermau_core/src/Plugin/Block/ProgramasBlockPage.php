<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Programas Block Page'.
 *
 * @Block(
 *   id = "programas_block_page",
 *   admin_label = @Translation("Programas Block Page")
 * )
 */
class ProgramasBlockPage extends BlockBase {

  public function build() {

    $programas = [];

    $nids = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'programa')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->execute();

    if ($nids) {

      $nodes = Node::loadMultiple($nids);

      foreach ($nodes as $node) {

        $imagen = '';

        if (!$node->get('field_imagen_programa')->isEmpty()) {

          $file = $node->get('field_imagen_programa')->entity;

          $imagen = \Drupal::service('file_url_generator')
            ->generateAbsoluteString($file->getFileUri());

        }

        $programas[] = [
          'id' => $node->id(),

          'titulo' => $node->getTitle(),

          'descripcion' => $node->get('field_descripcion_programa')->value ?? '',

          'tipo' => $node->get('field_tipo_de_programa')->value ?? '',

          'imagen' => $imagen,

          'duracion' => $node->get('field_duracion')->value ?? '',

          'modulos' => $node->get('field_modulos')->value ?? '',

          'cupos' => $node->get('field_cupos')->value ?? '',

          'url' => $node->toUrl()->toString(),

        ];

      }

    }

    return [
      '#theme' => 'programas_block_page',
      '#programas' => $programas,
      '#cache' => [
        'max-age' => 0,
      ],
    ];

  }

}
