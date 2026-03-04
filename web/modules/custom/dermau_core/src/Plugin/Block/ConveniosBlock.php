<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * @Block(
 *   id = "dermau_convenios_block",
 *   admin_label = @Translation("DermaU - Convenios Universitarios")
 * )
 */
class ConveniosBlock extends BlockBase {

  /**
   * Default configuration.
   */
  public function defaultConfiguration() {
    return [
      'titulo_parte_1' => 'Convenios',
      'titulo_parte_2' => 'Universitarios',
      'descripcion' => '',
    ];
  }

  /**
   * Block configuration form.
   */
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

    return $form;
  }

  /**
   * Save block configuration.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['titulo_parte_1'] = $form_state->getValue('titulo_parte_1');
    $this->configuration['titulo_parte_2'] = $form_state->getValue('titulo_parte_2');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');
  }

  /**
   * Build block output.
   */
  public function build() {

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'convenio')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->accessCheck(TRUE);

    $nids = $query->execute();

    $items = [];

    if (!empty($nids)) {

      $nodes = Node::loadMultiple($nids);
      $file_url_generator = \Drupal::service('file_url_generator');

      foreach ($nodes as $node) {

        if ($node->get('field_logo')->isEmpty()) {
          continue;
        }

        $image = $node->get('field_logo')->entity;
        $image_url = $file_url_generator->generateAbsoluteString($image->getFileUri());

        $link_url = '';

        if (!$node->get('field_link')->isEmpty()) {
          $link_url = $node->get('field_link')->uri;
        }

        $items[] = [
          'logo' => $image_url,
          'url' => $link_url,
          'title' => $node->label(),
        ];
      }
    }

    return [
      '#theme' => 'block_convenios',
      '#titulo_parte_1' => $this->configuration['titulo_parte_1'],
      '#titulo_parte_2' => $this->configuration['titulo_parte_2'],
      '#descripcion' => $this->configuration['descripcion'],
      '#items' => $items,
      '#cache' => [
        'tags' => ['node_list'],
      ],
    ];
  }

}
