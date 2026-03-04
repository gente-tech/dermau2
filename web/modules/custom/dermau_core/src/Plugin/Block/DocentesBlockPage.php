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

        // Rol
        $rol = '';
        if (!$node->get('field_rol')->isEmpty()) {
          $rol = $node->get('field_rol')->value;
        }

        // Universidad
        $universidad = '';
        if (!$node->get('field_universidad')->isEmpty()) {
          $universidad = $node->get('field_universidad')->entity->label();
        }

        // Programas
        $programas = [];

        if (!$node->get('field_programas')->isEmpty()) {
          foreach ($node->get('field_programas')->referencedEntities() as $programa) {
            $programas[] = $programa->label();
          }
        }

        $docentes[] = [
          'id' => $node->id(),
          'nombre' => $node->getTitle(),
          'rol' => $rol,
          'universidad' => $universidad,
          'programas' => $programas,
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
