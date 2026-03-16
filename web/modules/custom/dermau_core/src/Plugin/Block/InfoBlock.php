<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a Dermau Info Block.
 *
 * @Block(
 *   id = "dermau_info_block",
 *   admin_label = @Translation("Dermau Info Block")
 * )
 */
class InfoBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'titulo_normal' => '',
      'titulo_resaltado' => '',
      'descripcion' => '',
      'imagen' => [],
      'imagen_alt' => '',
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

    $form['titulo_resaltado'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título resaltado'),
      '#default_value' => $this->configuration['titulo_resaltado'] ?? '',
      '#required' => FALSE,
    ];

    $form['descripcion'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Descripción'),
      '#default_value' => $this->configuration['descripcion'] ?? '',
      '#required' => FALSE,
    ];

    $form['imagen'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Imagen'),
      '#upload_location' => 'public://info-block/',
      '#default_value' => $this->configuration['imagen'] ?? [],
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg webp svg'],
      ],
      '#required' => FALSE,
    ];

    $form['imagen_alt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alt de la imagen'),
      '#default_value' => $this->configuration['imagen_alt'] ?? '',
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $imagen = $form_state->getValue('imagen');

    if (!empty($imagen[0])) {
      $file = File::load($imagen[0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
      }
    }

    $this->configuration['titulo_normal'] = $form_state->getValue('titulo_normal');
    $this->configuration['titulo_resaltado'] = $form_state->getValue('titulo_resaltado');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');
    $this->configuration['imagen'] = $imagen;
    $this->configuration['imagen_alt'] = $form_state->getValue('imagen_alt');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $image_url = '';

    if (!empty($this->configuration['imagen'][0])) {
      $file = File::load($this->configuration['imagen'][0]);
      if ($file) {
        $image_url = \Drupal::service('file_url_generator')
          ->generateAbsoluteString($file->getFileUri());
      }
    }

    return [
      '#theme' => 'dermau_info_block',
      '#titulo_normal' => $this->configuration['titulo_normal'] ?? '',
      '#titulo_resaltado' => $this->configuration['titulo_resaltado'] ?? '',
      '#descripcion' => $this->configuration['descripcion'] ?? '',
      '#imagen' => [
        'url' => $image_url,
        'alt' => $this->configuration['imagen_alt'] ?? '',
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}