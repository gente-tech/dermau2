<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a Dermau Banner Simple Form Block.
 *
 * @Block(
 *   id = "dermau_banner_simple_form_block",
 *   admin_label = @Translation("Dermau Banner Simple Form Block")
 * )
 */
class BannerSimpleFormBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration()
  {
    return [
      'titulo' => '',
      'descripcion' => '',
      'label_btn' => '',
      'link' => '',
      'imagen' => [],
      'imagen_alt' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state)
  {
    $form['titulo'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título'),
      '#default_value' => $this->configuration['titulo'] ?? '',
    ];

    $form['descripcion'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Descripción'),
      '#default_value' => $this->configuration['descripcion'] ?? '',
    ];

    $form['label_btn'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label del botón'),
      '#default_value' => $this->configuration['label_btn'] ?? '',
    ];

    $form['link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link del botón'),
      '#default_value' => $this->configuration['link'] ?? '',
      '#description' => $this->t('Url de redirección.'),
    ];

    $form['imagen'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Imagen'),
      '#upload_location' => 'public://banner-simple-form/',
      '#default_value' => $this->configuration['imagen'] ?? [],
      '#multiple' => FALSE,
    ];

    $form['imagen_alt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alt de la imagen'),
      '#default_value' => $this->configuration['imagen_alt'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state)
  {
    $imagen = $form_state->getValue('imagen');

    if (empty($imagen) && !empty($this->configuration['imagen'])) {
      $imagen = $this->configuration['imagen'];
    }

    if (!empty($imagen[0])) {
      $file = File::load($imagen[0]);

      if ($file) {
        $file->setPermanent();
        $file->save();
      }
    }

    $this->configuration['titulo'] = $form_state->getValue('titulo');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');
    $this->configuration['label_btn'] = $form_state->getValue('label_btn');
    $this->configuration['link'] = $form_state->getValue('link');
    $this->configuration['imagen'] = $imagen ?: [];
    $this->configuration['imagen_alt'] = $form_state->getValue('imagen_alt');
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $image_url = '';
    $image_alt = $this->configuration['imagen_alt'] ?? '';

    if (!empty($this->configuration['imagen'][0])) {
      $file = File::load($this->configuration['imagen'][0]);

      if ($file) {
        $image_url = \Drupal::service('file_url_generator')
          ->generateAbsoluteString($file->getFileUri());
      }
    }

    return [
      '#theme' => 'dermau_banner_simple_form_block',
      '#titulo' => $this->configuration['titulo'] ?? '',
      '#descripcion' => $this->configuration['descripcion'] ?? '',
      '#label_btn' => $this->configuration['label_btn'] ?? '',
      '#link' => $this->configuration['link'] ?? '',
      '#imagen' => [
        'url' => $image_url,
        'alt' => $image_alt,
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}