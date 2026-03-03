<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

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
      'titulo_parte_1' => 'Convenios',
      'titulo_parte_2' => 'Universitarios',
      'descripcion' => '',
      'items' => [],
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {

    $form['titulo_parte_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título parte resaltada'),
      '#default_value' => $this->configuration['titulo_parte_1'],
      '#required' => TRUE,
    ];

    $form['titulo_parte_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título parte normal'),
      '#default_value' => $this->configuration['titulo_parte_2'],
      '#required' => TRUE,
    ];

    $form['descripcion'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Descripción'),
      '#default_value' => $this->configuration['descripcion'],
    ];

    $form['items'] = [
      '#type' => 'details',
      '#title' => $this->t('Logos de convenios'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];

    $items = $this->configuration['items'] ?? [];

    for ($i = 0; $i < 10; $i++) {

      $form['items'][$i]['logo'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Logo'),
        '#upload_location' => 'public://convenios/',
        '#default_value' => $items[$i]['logo'] ?? NULL,
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg svg webp'],
        ],
      ];

      $form['items'][$i]['url'] = [
        '#type' => 'url',
        '#title' => $this->t('URL'),
        '#default_value' => $items[$i]['url'] ?? '',
      ];

      $form['items'][$i]['alt'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Texto alternativo'),
        '#default_value' => $items[$i]['alt'] ?? '',
      ];
    }

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['titulo_parte_1'] = $form_state->getValue('titulo_parte_1');
    $this->configuration['titulo_parte_2'] = $form_state->getValue('titulo_parte_2');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');

    $items = array_filter($form_state->getValue('items'), function ($item) {
      return !empty($item['logo']);
    });

    foreach ($items as &$item) {
      if (!empty($item['logo'][0])) {
        $file = File::load($item['logo'][0]);
        if ($file) {
          $file->setPermanent();
          $file->save();
          $item['logo_url'] = file_create_url($file->getFileUri());
        }
      }
    }

    $this->configuration['items'] = $items;
  }

  public function build() {

    $items = [];

    foreach ($this->configuration['items'] as $item) {

      if (!empty($item['logo'][0])) {

        $file = File::load($item['logo'][0]);

        if ($file) {
          $items[] = [
            'logo' => file_create_url($file->getFileUri()),
            'url' => $item['url'],
            'alt' => $item['alt'],
          ];
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
