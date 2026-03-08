<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Component\Utility\Unicode;

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

        if (!$node->get('field_foto_docente')->isEmpty()) {

          $file = $node->get('field_foto_docente')->entity;

          $imagen = \Drupal::service('file_url_generator')
            ->generateAbsoluteString($file->getFileUri());
        }

        // Programas
        $programas = [];

        if (!$node->get('field_programas_vinculados')->isEmpty()) {

          foreach ($node->get('field_programas_vinculados')->referencedEntities() as $programa) {
            $programas[] = $programa->label();
          }

        }

        $descripcion = $node->get('field_perfil_profesional')->value ?? '';

        $docentes[] = [
          'id' => $node->id(),
          'nombre' => $node->getTitle(),
          'rol' => $node->get('field_especialidad')->value ?? '',
          'universidad' => $node->get('field_ciudad')->value ?? '',
          'descripcion' => Unicode::truncate(
            strip_tags($descripcion),
            285,
            TRUE,
            TRUE
          ),
          'programas' => $programas,
          'imagen' => $imagen,
          'url' => $node->toUrl()->toString(),
        ];

      }

    }

    return [
      '#theme' => 'docentes_block_page',
      '#docentes' => $docentes,
      '#cache' => [
        'tags' => ['node_list:docente'],
      ],
    ];

  }

}
