<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a Dermau Stats Block.
 *
 * @Block(
 *   id = "dermau_stats_block",
 *   admin_label = @Translation("Dermau Stats Block"),
 * )
 */
class StatsBlock extends BlockBase {

  public function defaultConfiguration() {
    return [
      'title_1' => '',
      'title_2' => '',
      'description' => '',
      'items' => [],
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {

    $form['title_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título parte 1'),
      '#default_value' => $this->configuration['title_1'],
    ];

    $form['title_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título parte 2 (color)'),
      '#default_value' => $this->configuration['title_2'],
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Descripción'),
      '#default_value' => $this->configuration['description'],
    ];

    $items = $this->configuration['items'] ?? [];

    $form['items'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Items'),
    ];

    for ($i = 0; $i < 4; $i++) {

      $form['items'][$i]['logo'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Logo'),
        '#upload_location' => 'public://stats/',
        '#default_value' => $items[$i]['logo'] ?? NULL,
      ];

      $form['items'][$i]['number'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Número'),
        '#default_value' => $items[$i]['number'] ?? '',
      ];

      $form['items'][$i]['text'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Texto'),
        '#default_value' => $items[$i]['text'] ?? '',
      ];
    }

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {

    $items = $form_state->getValue('items');

    foreach ($items as &$item) {
      if (!empty($item['logo'][0])) {
        $file = File::load($item['logo'][0]);
        if ($file) {
          $file->setPermanent();
          $file->save();
        }
      }
    }

    $this->configuration['title_1'] = $form_state->getValue('title_1');
    $this->configuration['title_2'] = $form_state->getValue('title_2');
    $this->configuration['description'] = $form_state->getValue('description');
    $this->configuration['items'] = $items;
  }

  public function build() {

    $items = [];

    foreach ($this->configuration['items'] as $item) {

      $logo_url = '';

      if (!empty($item['logo'][0])) {
        $file = File::load($item['logo'][0]);
        if ($file) {
          $logo_url = \Drupal::service('file_url_generator')
            ->generateAbsoluteString($file->getFileUri());
        }
      }

      $items[] = [
        'logo' => $logo_url,
        'number' => $item['number'] ?? '',
        'text' => $item['text'] ?? '',
      ];
    }

    return [
      '#theme' => 'dermau_stats_block',
      '#title_1' => $this->configuration['title_1'],
      '#title_2' => $this->configuration['title_2'],
      '#description' => $this->configuration['description'],
      '#items' => $items,
      '#cache' => ['max-age' => 0],
    ];
  }

}
