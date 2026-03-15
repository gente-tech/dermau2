<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @Block(
 *   id = "dermau_bar_menu_block",
 *   admin_label = @Translation("DermaU - Barra menú anclas")
 * )
 */
class BarMenuBlock extends BlockBase {

  public function defaultConfiguration() {
    return [
      'items' => [
        [
          'text' => 'Oferta Académica',
          'url' => '#oferta-academica',
          'icon' => 'book',
        ],
        [
          'text' => 'Convenios universitarios',
          'url' => '#convenios-universitarios',
          'icon' => 'cap',
        ],
        [
          'text' => 'Preguntas frecuentes',
          'url' => '#preguntas-frecuentes',
          'icon' => 'question',
        ],
      ],
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $items = $form_state->get('items');

    if ($items === NULL) {
      $items = $this->configuration['items'] ?? [];
      $form_state->set('items', $items);
    }

    $form['items_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'bar-menu-items-wrapper',
      ],
      '#tree' => TRUE,
    ];

    foreach ($items as $delta => $item) {
      $form['items_wrapper']['items'][$delta] = [
        '#type' => 'details',
        '#title' => $this->t('Ítem @num', ['@num' => $delta + 1]),
        '#open' => TRUE,
      ];

      $form['items_wrapper']['items'][$delta]['text'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Texto'),
        '#default_value' => $item['text'] ?? '',
        '#required' => TRUE,
      ];

      $form['items_wrapper']['items'][$delta]['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Enlace o ancla'),
        '#default_value' => $item['url'] ?? '',
        '#required' => TRUE,
        '#description' => $this->t('Ejemplo: #oferta-academica o /pagina-interna'),
      ];

      $form['items_wrapper']['items'][$delta]['icon'] = [
        '#type' => 'select',
        '#title' => $this->t('Ícono'),
        '#default_value' => $item['icon'] ?? 'book',
        '#options' => [
          'book' => $this->t('Libro'),
          'cap' => $this->t('Birrete'),
          'question' => $this->t('Pregunta'),
        ],
        '#required' => TRUE,
      ];

      $form['items_wrapper']['items'][$delta]['remove'] = [
        '#type' => 'submit',
        '#value' => $this->t('Eliminar ítem'),
        '#name' => 'remove_item_' . $delta,
        '#submit' => [[static::class, 'removeItemSubmit']],
        '#ajax' => [
          'callback' => [static::class, 'ajaxRefresh'],
          'wrapper' => 'bar-menu-items-wrapper',
        ],
        '#limit_validation_errors' => [],
        '#item_delta' => $delta,
      ];
    }

    $form['items_wrapper']['actions'] = [
      '#type' => 'actions',
    ];

    $form['items_wrapper']['actions']['add_item'] = [
      '#type' => 'submit',
      '#value' => $this->t('Agregar ítem'),
      '#submit' => [[static::class, 'addItemSubmit']],
      '#ajax' => [
        'callback' => [static::class, 'ajaxRefresh'],
        'wrapper' => 'bar-menu-items-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    return $form;
  }

  public static function addItemSubmit(array &$form, FormStateInterface $form_state) {
    $items = $form_state->get('items') ?? [];

    $submitted_items = $form_state->getValue(['items_wrapper', 'items']);
    if (is_array($submitted_items)) {
      foreach ($submitted_items as $delta => $submitted_item) {
        if (isset($items[$delta])) {
          $items[$delta]['text'] = $submitted_item['text'] ?? '';
          $items[$delta]['url'] = $submitted_item['url'] ?? '';
          $items[$delta]['icon'] = $submitted_item['icon'] ?? 'book';
        }
      }
    }

    $items[] = [
      'text' => '',
      'url' => '',
      'icon' => 'book',
    ];

    $form_state->set('items', $items);
    $form_state->setRebuild(TRUE);
  }

  public static function removeItemSubmit(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $delta = $trigger['#item_delta'];

    $items = $form_state->get('items') ?? [];

    $submitted_items = $form_state->getValue(['items_wrapper', 'items']);
    if (is_array($submitted_items)) {
      foreach ($submitted_items as $index => $submitted_item) {
        if (isset($items[$index])) {
          $items[$index]['text'] = $submitted_item['text'] ?? '';
          $items[$index]['url'] = $submitted_item['url'] ?? '';
          $items[$index]['icon'] = $submitted_item['icon'] ?? 'book';
        }
      }
    }

    if (isset($items[$delta])) {
      unset($items[$delta]);
    }

    $items = array_values($items);

    $form_state->set('items', $items);
    $form_state->setRebuild(TRUE);
  }

  public static function ajaxRefresh(array &$form, FormStateInterface $form_state) {
    return $form['settings']['items_wrapper'] ?? $form['items_wrapper'];
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    $items = $form_state->getValue(['items_wrapper', 'items']) ?? [];
    $clean_items = [];

    foreach ($items as $item) {
      $text = trim($item['text'] ?? '');
      $url = trim($item['url'] ?? '');
      $icon = trim($item['icon'] ?? 'book');

      if ($text !== '' && $url !== '') {
        $clean_items[] = [
          'text' => $text,
          'url' => $url,
          'icon' => $icon,
        ];
      }
    }

    $this->configuration['items'] = $clean_items;
    $form_state->set('items', $clean_items);
  }

  public function build() {
    return [
      '#theme' => 'block_bar_menu',
      '#items' => $this->configuration['items'] ?? [],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
