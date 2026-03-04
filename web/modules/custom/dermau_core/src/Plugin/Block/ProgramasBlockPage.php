<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Programas Page Block'.
 *
 * @Block(
 *   id = "programas_block_page",
 *   admin_label = @Translation("Programas Page Block")
 * )
 */
class ProgramasBlockPage extends BlockBase {

  public function build() {

    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'programa')
      ->accessCheck(TRUE);

    $nids = $query->execute();

    $programas = Node::loadMultiple($nids);

    return [
      '#theme' => 'programas_block_page',
      '#programas' => $programas,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
