<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Docentes Block Page'.
 *
 * @Block(
 *   id = "docentes_block_page",
 *   admin_label = @Translation("Docentes Block Page")
 * )
 */
class DocentesBlockPage extends BlockBase {

  public function build() {

    $docentes = [];

    $nids = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'docente')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->execute();

    if ($nids) {

      $nodes = Node::loadMultiple($nids);

      foreach ($nodes as $node) {

        $imagen = '';

        if (!$node->get('field_imagen_docente')->isEmpty()) {

          $file = $node->get('field_imagen_docente')->entity;

          $imagen = \Drupal::service('file_url_generator')
            ->generateAbsoluteString($file->getFileUri());
        }

        $docentes[] = [
          'id' => $node->id(),
          'nombre' => $node->getTitle(),
          'descripcion' => $node->get('field_descripcion')->value ?? '',
          'imagen' => $imagen,
          'url' => $node->toUrl()->toString(),
        ];

      }

    }

    return [
      '#theme' => 'docentes_block_page',
      '#docentes' => $docentes,
      '#cache' => [
        'max-age' => 0,
      ],
    ];

  }

}
