<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an Info Simple Block.
 *
 * @Block(
 *   id = "dermau_info_simple_block",
 *   admin_label = @Translation("Dermau Info Simple Block")
 * )
 */
class InfoSimpleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'titulo_normal' => '',
      'titulo_color' => '',
      'descripcion' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['titulo_normal'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título normal'),
      '#default_value' => $this->configuration['titulo_normal'] ?? '',
      '#required' => FALSE,
    ];

    $form['titulo_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título con color'),
      '#default_value' => $this->configuration['titulo_color'] ?? '',
      '#required' => FALSE,
    ];

    $form['descripcion'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Descripción'),
      '#default_value' => $this->configuration['descripcion'] ?? '',
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['titulo_normal'] = $form_state->getValue('titulo_normal');
    $this->configuration['titulo_color'] = $form_state->getValue('titulo_color');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'info_simple_block',
      '#titulo_normal' => $this->configuration['titulo_normal'] ?? '',
      '#titulo_color' => $this->configuration['titulo_color'] ?? '',
      '#descripcion' => $this->configuration['descripcion'] ?? '',
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}