<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a Dermau Banner Simple Small Form Block.
 *
 * @Block(
 *   id = "dermau_banner_simple_small_form_block",
 *   admin_label = @Translation("Dermau Banner Simple Small Form Block")
 * )
 */
class BannerSimpleSmallFormBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration()
  {
    return [
      'titulo_normal' => '',
      'titulo_color' => '',
      'descripcion' => '',
      'label_btn' => '',
      'url_btn' => '',
      'imagen' => [],
      'imagen_alt' => '',
	  'mostrar_formulario' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state)
  {
    $form['titulo_normal'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título normal'),
      '#default_value' => $this->configuration['titulo_normal'] ?? '',
    ];

    $form['titulo_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título color'),
      '#default_value' => $this->configuration['titulo_color'] ?? '',
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

    $form['url_btn'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL del botón'),
      '#default_value' => $this->configuration['url_btn'] ?? '',
      '#description' => $this->t('Ruta o URL en texto. Ejemplo: /contacto o https://dominio.com'),
    ];

    $form['imagen'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Imagen'),
      '#upload_location' => 'public://banner-simple-small-form/',
      '#default_value' => $this->configuration['imagen'] ?? [],
      '#multiple' => FALSE,
    ];

    $form['imagen_alt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alt de la imagen'),
      '#default_value' => $this->configuration['imagen_alt'] ?? '',
    ];

	$form['mostrar_formulario'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar formulario'),
      '#default_value' => $this->configuration['mostrar_formulario'] ?? 0,
      '#description' => $this->t('Si está marcado, en el Twig se podrá mostrar el formulario.'),
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

    $this->configuration['titulo_normal'] = $form_state->getValue('titulo_normal');
    $this->configuration['titulo_color'] = $form_state->getValue('titulo_color');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');
    $this->configuration['label_btn'] = $form_state->getValue('label_btn');
    $this->configuration['url_btn'] = $form_state->getValue('url_btn');
    $this->configuration['imagen'] = $imagen ?: [];
    $this->configuration['imagen_alt'] = $form_state->getValue('imagen_alt');
	$this->configuration['mostrar_formulario'] = $form_state->getValue('mostrar_formulario');
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
      '#theme' => 'dermau_banner_simple_small_form_block',
      '#titulo_normal' => $this->configuration['titulo_normal'] ?? '',
      '#titulo_color' => $this->configuration['titulo_color'] ?? '',
      '#descripcion' => $this->configuration['descripcion'] ?? '',
      '#label_btn' => $this->configuration['label_btn'] ?? '',
      '#url_btn' => $this->configuration['url_btn'] ?? '',
	  '#mostrar_formulario' => $this->configuration['mostrar_formulario'] ?? 0,
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