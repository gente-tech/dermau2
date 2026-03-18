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

        // Programas con nombre + tipo + tid
        $programas = [];

        if (!$node->get('field_programas_vinculados')->isEmpty()) {

          foreach ($node->get('field_programas_vinculados')->referencedEntities() as $programa) {

            $tid_tipo = null;
            $tipo_nombre = '';

            if (!$programa->get('field_tipo_de_programa')->isEmpty()) {

              $term = $programa->get('field_tipo_de_programa')->entity;

              if ($term) {
                $tid_tipo = $term->id();
                $tipo_nombre = $term->label();
              }

            }

            $programas[] = [
              'nombre' => $programa->label(), // nombre del programa
              'tipo' => $tipo_nombre,        // Diplomado, Curso, etc
              'tid' => $tid_tipo,            // tid del tipo
            ];

          }

        }

        $descripcion = $node->get('field_perfil_profesional')->value ?? '';

        $docentes[] = [
          'id' => $node->id(),

          'nombre' => $node->getTitle(),

          'rol' => $node->get('field_especialidad')->value ?? '',

          'ciudad' => $node->get('field_ciudad')->value ?? '',

          'email' => $node->get('field_correo_electronico')->value ?? '',

          'web' => $node->get('field_pagina_web')->value ?? '',

          'descripcion' => Unicode::truncate(
            strip_tags($descripcion),
            285,
            TRUE,
            TRUE
          ),

          'perfil' => $descripcion,

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
