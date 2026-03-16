<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block with the program interested form.
 *
 * @Block(
 *   id = "dermau_programa_interesado_block",
 *   admin_label = @Translation("DermaU Programa Interesado Form")
 * )
 */
class ProgramaInteresadoBlock extends BlockBase {

  public function build(): array {
    return \Drupal::formBuilder()->getForm('Drupal\dermau_core\Form\ProgramaInteresadoForm');
  }

}