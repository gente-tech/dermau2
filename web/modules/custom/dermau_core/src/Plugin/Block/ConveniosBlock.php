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
      'items' => [],
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

    $items = $form_state->get('items_count');
    if ($items === NULL) {
      $items = count($this->configuration['items']) ?: 1;
      $form_state->set('items_count', $items);
    }

    $form['items_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Logos'),
      '#prefix' => '<div id="items-wrapper">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    for ($i = 0; $i < $items; $i++) {

      $form['items_wrapper'][$i]['logo'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Logo'),
        '#upload_location' => 'public://convenios/',
        '#default_value' => $this->configuration['items'][$i]['logo'] ?? NULL,
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg svg webp'],
        ],
      ];

      $form['items_wrapper'][$i]['url'] = [
        '#type' => 'url',
        '#title' => $this->t('URL'),
        '#default_value' => $this->configuration['items'][$i]['url'] ?? '',
      ];

      $form['items_wrapper'][$i]['alt'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Texto alternativo'),
        '#default_value' => $this->configuration['items'][$i]['alt'] ?? '',
      ];
    }

    $form['add_item'] = [
      '#type' => 'submit',
      '#value' => $this->t('Agregar otro logo'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addMoreCallback',
        'wrapper' => 'items-wrapper',
      ],
    ];

    return $form;
  }

  public function addOne(array &$form, FormStateInterface $form_state) {
    $count = $form_state->get('items_count');
    $form_state->set('items_count', $count + 1);
    $form_state->setRebuild();
  }

  public function addMoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['items_wrapper'];
  }

  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['titulo_parte_1'] = $form_state->getValue('titulo_parte_1');
    $this->configuration['titulo_parte_2'] = $form_state->getValue('titulo_parte_2');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');

    $items = $form_state->getValue('items_wrapper');
    $clean_items = [];

    foreach ($items as $item) {

      if (!empty($item['logo'][0])) {

        $file = File::load($item['logo'][0]);

        if ($file) {
          $file->setPermanent();
          $file->save();

          $clean_items[] = [
            'logo' => [$item['logo'][0]],
            'url' => $item['url'],
            'alt' => $item['alt'],
          ];
        }
      }
    }

    $this->configuration['items'] = $clean_items;
  }

  public function build() {

    $items = [];

    foreach ($this->configuration['items'] ?? [] as $item) {

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
