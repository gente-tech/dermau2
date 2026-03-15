<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "dermau_bar_menu_block",
 *   admin_label = @Translation("DermaU - Barra menú anclas")
 * )
 */
class BarMenuBlock extends BlockBase {

  public function build() {
    return [
      '#theme' => 'block_bar_menu',
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
