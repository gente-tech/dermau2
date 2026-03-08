<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides Registro Form Block.
 *
 * @Block(
 *   id = "dermau_registro_form_block",
 *   admin_label = @Translation("DermaU Registro Form")
 * )
 */
class RegistroFormBlock extends BlockBase {

  public function build() {

    return \Drupal::formBuilder()->getForm(
      'Drupal\dermau_core\Form\ContactoRegistroForm'
    );

  }

}
