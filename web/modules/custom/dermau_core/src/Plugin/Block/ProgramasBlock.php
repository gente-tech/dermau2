<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;

/**
 * @Block(
 *   id = "dermau_programas_block",
 *   admin_label = @Translation("Dermau Programas Block"),
 * )
 */
class ProgramasBlock extends BlockBase {

  public function build() {

    // Obtener términos para filtros
    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('tipos_de_programas');

    return [
      '#theme' => 'dermau_programas_block',
      '#terms' => $terms,
      '#attached' => [
        'library' => [
          'dermau_core/programas-filter',
        ],
      ],
    ];
  }

}
