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

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'titulo_parte_1' => 'Convenios',
      'titulo_parte_2' => 'Universitarios',
      'descripcion' => 'Nuestros convenios fortalecen el aprendizaje y aseguran que cada programa cumpla con estándares educativos.',
      'items' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Importante para estructuras anidadas.
    $form['#tree'] = TRUE;

    $form['titulo_parte_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título resaltado (dentro del <span>)'),
      '#default_value' => $this->configuration['titulo_parte_1'] ?? '',
      '#required' => TRUE,
    ];

    $form['titulo_parte_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título normal (fuera del <span>)'),
      '#default_value' => $this->configuration['titulo_parte_2'] ?? '',
      '#required' => TRUE,
    ];

    $form['descripcion'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Descripción'),
      '#default_value' => $this->configuration['descripcion'] ?? '',
    ];

    // Cantidad de items (persistente en el form_state).
    $items_count = $form_state->get('items_count');
    if ($items_count === NULL) {
      $saved = $this->configuration['items'] ?? [];
      $items_count = max(1, count($saved));
      $form_state->set('items_count', $items_count);
    }

    $form['items_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Logos (ilimitados)'),
      '#prefix' => '<div id="items-wrapper">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    $saved_items = $this->configuration['items'] ?? [];

    for ($i = 0; $i < $items_count; $i++) {
      $form['items_wrapper'][$i] = [
        '#type' => 'details',
        '#title' => $this->t('Logo @n', ['@n' => $i + 1]),
        '#open' => TRUE,
      ];

      $form['items_wrapper'][$i]['logo'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Logo'),
        '#upload_location' => 'public://convenios/',
        '#default_value' => $saved_items[$i]['logo'] ?? [],
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg svg webp'],
        ],
        '#description' => $this->t('Sube el logo y asegúrate de presionar "Upload".'),
      ];

      $form['items_wrapper'][$i]['url'] = [
        '#type' => 'url',
        '#title' => $this->t('URL'),
        '#default_value' => $saved_items[$i]['url'] ?? '',
        '#required' => FALSE,
      ];

      $form['items_wrapper'][$i]['alt'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Texto alternativo (alt)'),
        '#default_value' => $saved_items[$i]['alt'] ?? '',
        '#required' => FALSE,
      ];
    }

    // Botón AJAX para agregar.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['add_item'] = [
      '#type' => 'submit',
      '#value' => $this->t('Agregar otro logo'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addMoreCallback',
        'wrapper' => 'items-wrapper',
      ],
      // CLAVE: evitar validación completa (si no, el AJAX falla).
      '#limit_validation_errors' => [],
    ];

    return $form;
  }

  /**
   * AJAX submit: agrega un item.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $count = (int) $form_state->get('items_count');
    $form_state->set('items_count', $count + 1);
    $form_state->setRebuild(TRUE);
  }

  /**
   * AJAX callback.
   */
  public function addMoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['items_wrapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['titulo_parte_1'] = $form_state->getValue('titulo_parte_1');
    $this->configuration['titulo_parte_2'] = $form_state->getValue('titulo_parte_2');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');

    $items = $form_state->getValue('items_wrapper') ?? [];
    $clean_items = [];

    foreach ($items as $item) {
      $fids = $item['logo'] ?? [];
      $fid = !empty($fids[0]) ? (int) $fids[0] : 0;

      // Solo guardar filas que tengan logo.
      if ($fid > 0) {
        $file = File::load($fid);
        if ($file) {
          // Clave para que no se borre por cron.
          $file->setPermanent();
          $file->save();

          $clean_items[] = [
            'logo' => [$fid],
            'url' => $item['url'] ?? '',
            'alt' => $item['alt'] ?? '',
          ];
        }
      }
    }

    $this->configuration['items'] = $clean_items;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $items = [];

    foreach ($this->configuration['items'] ?? [] as $item) {
      $fid = !empty($item['logo'][0]) ? (int) $item['logo'][0] : 0;
      if ($fid > 0) {
        $file = File::load($fid);
        if ($file) {
          $items[] = [
            'logo' => file_create_url($file->getFileUri()),
            'url' => $item['url'] ?? '',
            'alt' => $item['alt'] ?? '',
          ];
        }
      }
    }

    return [
      '#theme' => 'block_convenios',
      '#titulo_parte_1' => $this->configuration['titulo_parte_1'] ?? '',
      '#titulo_parte_2' => $this->configuration['titulo_parte_2'] ?? '',
      '#descripcion' => $this->configuration['descripcion'] ?? '',
      '#items' => $items,
      '#attached' => [
        'library' => [
          'dermau_core/convenios-swiper',
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
