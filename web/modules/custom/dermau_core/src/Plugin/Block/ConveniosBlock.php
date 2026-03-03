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

    $form['items'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Logos'),
      '#upload_location' => 'public://convenios/',
      '#multiple' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg svg webp'],
      ],
      '#default_value' => array_column($this->configuration['items'], 'fid'),
      '#description' => $this->t('Puedes subir múltiples logos.'),
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['titulo_parte_1'] = $form_state->getValue('titulo_parte_1');
    $this->configuration['titulo_parte_2'] = $form_state->getValue('titulo_parte_2');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');

    $fids = $form_state->getValue('items');
    $clean_items = [];

    if (!empty($fids)) {
      foreach ($fids as $fid) {

        $file = File::load($fid);

        if ($file) {
          $file->setPermanent();
          $file->save();

          $clean_items[] = [
            'fid' => $fid,
            'url' => '',
            'alt' => '',
          ];
        }
      }
    }

    $this->configuration['items'] = $clean_items;
  }

  public function build() {

    $items = [];

    foreach ($this->configuration['items'] ?? [] as $item) {

      $file = File::load($item['fid']);

      if ($file) {
        $items[] = [
          'logo' => file_create_url($file->getFileUri()),
          'url' => '',
          'alt' => '',
        ];
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
