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

    // Número de logos
    $logos_count = $form_state->get('logos_count');
    if ($logos_count === NULL) {
      $logos_count = count($this->configuration['logos']);
      $logos_count = $logos_count > 0 ? $logos_count : 1;
      $form_state->set('logos_count', $logos_count);
    }

    $form['logos_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Logos'),
      '#prefix' => '<div id="logos-wrapper">',
      '#suffix' => '</div>',
    ];

    for ($i = 0; $i < $logos_count; $i++) {

      $form['logos_wrapper']['logo_' . $i] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Logo @n', ['@n' => $i + 1]),
        '#upload_location' => 'public://convenios/',
        '#default_value' => $this->configuration['logos'][$i] ?? [],
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg svg webp'],
        ],
      ];
    }

    $form['add_logo'] = [
      '#type' => 'submit',
      '#value' => $this->t('Agregar otro logo'),
      '#submit' => ['::addLogo'],
      '#ajax' => [
        'callback' => '::addLogoCallback',
        'wrapper' => 'logos-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    return $form;
  }

  public function addLogo(array &$form, FormStateInterface $form_state) {
    $count = $form_state->get('logos_count');
    $form_state->set('logos_count', $count + 1);
    $form_state->setRebuild(TRUE);
  }

  public function addLogoCallback(array &$form, FormStateInterface $form_state) {
    return $form['logos_wrapper'];
  }

  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['titulo_parte_1'] = $form_state->getValue('titulo_parte_1');
    $this->configuration['titulo_parte_2'] = $form_state->getValue('titulo_parte_2');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');

    $logos = [];
    $logos_count = $form_state->get('logos_count');

    for ($i = 0; $i < $logos_count; $i++) {

      $value = $form_state->getValue(['logos_wrapper', 'logo_' . $i]);

      if (!empty($value[0])) {

        $file = File::load($value[0]);

        if ($file) {
          $file->setPermanent();
          $file->save();
          $logos[] = [$value[0]];
        }
      }
    }

    $this->configuration['logos'] = $logos;
  }

  public function build() {

    $items = [];

    foreach ($this->configuration['logos'] ?? [] as $logo) {

      if (!empty($logo[0])) {

        $file = File::load($logo[0]);

        if ($file) {
          $items[] = file_create_url($file->getFileUri());
        }
      }
    }

    return [
      '#theme' => 'block_convenios',
      '#titulo_parte_1' => $this->configuration['titulo_parte_1'],
      '#titulo_parte_2' => $this->configuration['titulo_parte_2'],
      '#descripcion' => $this->configuration['descripcion'],
      '#items' => $items,
      '#attached' => [
        'library' => [
          'dermau_core/convenios-swiper',
        ],
      ],
    ];
  }

}
