<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * @Block(
 *   id = "dermau_convenios_block",
 *   admin_label = @Translation("DermaU - Convenios Universitarios")
 * )
 */
class ConveniosBlock extends BlockBase {

  public function defaultConfiguration() {
    return [
      'titulo_parte_1' => 'Convenios',
      'titulo_parte_2' => 'Universitarios',
      'descripcion' => '',
      'logos' => [],
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {

    $form['titulo_parte_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título resaltado'),
      '#default_value' => $this->configuration['titulo_parte_1'],
      '#required' => TRUE,
    ];

    $form['titulo_parte_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título normal'),
      '#default_value' => $this->configuration['titulo_parte_2'],
      '#required' => TRUE,
    ];

    $form['descripcion'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Descripción'),
      '#default_value' => $this->configuration['descripcion'],
    ];

    $form['logos'] = [
      '#type' => 'image',
      '#title' => $this->t('Logos'),
      '#multiple' => TRUE,
      '#upload_location' => 'public://convenios/',
      '#default_value' => $this->configuration['logos'] ?? [],
      '#description' => $this->t('Puedes subir múltiples logos.'),
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['titulo_parte_1'] = $form_state->getValue('titulo_parte_1');
    $this->configuration['titulo_parte_2'] = $form_state->getValue('titulo_parte_2');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');

    $fids = array_filter($form_state->getValue('logos'));

    foreach ($fids as $fid) {
      if ($file = File::load($fid)) {
        $file->setPermanent();
        $file->save();
      }
    }

    $this->configuration['logos'] = array_values($fids);
  }

  public function build() {

    $items = [];

    if (!empty($this->configuration['logos'])) {

      $files = File::loadMultiple($this->configuration['logos']);

      foreach ($files as $file) {
        $items[] = file_create_url($file->getFileUri());
      }
    }

    return [
      '#theme' => 'block_convenios',
      '#titulo_parte_1' => $this->configuration['titulo_parte_1'],
      '#titulo_parte_2' => $this->configuration['titulo_parte_2'],
      '#descripcion' => $this->configuration['descripcion'],
      '#items' => $items,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
