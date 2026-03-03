<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Convenios Universitarios' block.
 *
 * @Block(
 *   id = "dermau_convenios_block",
 *   admin_label = @Translation("DermaU - Convenios Universitarios")
 * )
 */
class ConveniosBlock extends BlockBase {

  public function defaultConfiguration() {
    return [
      'titulo' => 'Convenios Universitarios',
      'descripcion' => 'Nuestros convenios fortalecen el aprendizaje y aseguran que cada programa cumpla con estándares educativos.',
      'logos' => [],
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {

    $form['titulo'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título'),
      '#default_value' => $this->configuration['titulo'],
      '#required' => TRUE,
    ];

    $form['descripcion'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Descripción'),
      '#default_value' => $this->configuration['descripcion'],
    ];

    $form['logos'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Logos de convenios'),
      '#upload_location' => 'public://convenios/',
      '#multiple' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg svg webp'],
      ],
      '#default_value' => $this->configuration['logos'],
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['titulo'] = $form_state->getValue('titulo');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');
    $this->configuration['logos'] = $form_state->getValue('logos');

    // Marcar archivos como permanentes
    if (!empty($this->configuration['logos'])) {
      foreach ($this->configuration['logos'] as $fid) {
        $file = \Drupal\file\Entity\File::load($fid);
        if ($file) {
          $file->setPermanent();
          $file->save();
        }
      }
    }
  }

  public function build() {

    $logos = [];

    if (!empty($this->configuration['logos'])) {
      foreach ($this->configuration['logos'] as $fid) {
        $file = \Drupal\file\Entity\File::load($fid);
        if ($file) {
          $logos[] = file_create_url($file->getFileUri());
        }
      }
    }

    return [
      '#theme' => 'block_convenios',
      '#titulo' => $this->configuration['titulo'],
      '#descripcion' => $this->configuration['descripcion'],
      '#logos' => $logos,
    ];
  }

}
