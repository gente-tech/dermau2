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

    $query = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'programa')
      ->condition('status', 1)
      ->sort('created', 'DESC');

    $nids = $query->execute();

    if ($nids) {

      $nodes = Node::loadMultiple($nids);

      foreach ($nodes as $node) {

        /* Imagen */
        $imagen = '';
        if ($node->hasField('field_imagen_programa') && !$node->get('field_imagen_programa')->isEmpty()) {

          $file = $node->get('field_imagen_programa')->entity;

          $imagen = \Drupal::service('file_url_generator')
            ->generateAbsoluteString($file->getFileUri());
        }

        /* Tipo de programa (Taxonomía) */
        $tipo = '';
        if ($node->hasField('field_tipo_de_programa') && !$node->get('field_tipo_de_programa')->isEmpty()) {

          $tipo = $node->get('field_tipo_de_programa')->entity->label();
        }

        /* Duración */
        $duracion = '';
        if ($node->hasField('field_duracion_programa') && !$node->get('field_duracion_programa')->isEmpty()) {

          $duracion = $node->get('field_duracion_programa')->value;
        }

        /* Módulos (paragraph count) */
        $modulos = 0;
        if ($node->hasField('field_modulos') && !$node->get('field_modulos')->isEmpty()) {

          $modulos = count($node->get('field_modulos')->getValue());
        }

        /* Descripción corta */
        $descripcion = '';
        if ($node->hasField('field_descripcion_corta') && !$node->get('field_descripcion_corta')->isEmpty()) {

          $descripcion = $node->get('field_descripcion_corta')->value;
        }

        $programas[] = [

          'id' => $node->id(),

          'titulo' => $node->label(),

          'descripcion' => $descripcion,

          'tipo' => $tipo,

          'imagen' => $imagen,

          'duracion' => $duracion,

          'modulos' => $modulos,

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
