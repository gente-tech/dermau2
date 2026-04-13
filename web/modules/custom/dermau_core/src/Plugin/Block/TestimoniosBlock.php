<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * @Block(
 *   id = "dermau_testimonios_block",
 *   admin_label = @Translation("DermaU - Testimonios")
 * )
 */
class TestimoniosBlock extends BlockBase {

  public function defaultConfiguration() {
    return [
      'titulo_parte_1' => 'Somos la Plataforma de Educación Dermatológica',
      'titulo_parte_2' => 'Líder en Colombia',
      'texto_boton' => 'Conoce más sobre',
      'texto_boton_highlight' => 'DermaU',
      'url_boton' => '#',
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {

    $form['titulo_parte_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título parte 1'),
      '#default_value' => $this->configuration['titulo_parte_1'],
      '#required' => TRUE,
    ];

    $form['titulo_parte_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título resaltado'),
      '#default_value' => $this->configuration['titulo_parte_2'],
      '#required' => TRUE,
    ];

    $form['texto_boton'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Texto botón'),
      '#default_value' => $this->configuration['texto_boton'],
    ];

    $form['texto_boton_highlight'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Texto botón resaltado'),
      '#default_value' => $this->configuration['texto_boton_highlight'],
    ];

    $form['url_boton'] = [
      '#type' => 'url',
      '#title' => $this->t('URL botón'),
      '#default_value' => $this->configuration['url_boton'],
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['titulo_parte_1'] = $form_state->getValue('titulo_parte_1');
    $this->configuration['titulo_parte_2'] = $form_state->getValue('titulo_parte_2');
    $this->configuration['texto_boton'] = $form_state->getValue('texto_boton');
    $this->configuration['texto_boton_highlight'] = $form_state->getValue('texto_boton_highlight');
    $this->configuration['url_boton'] = $form_state->getValue('url_boton');
  }

  public function build() {

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'testimonio')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->accessCheck(TRUE);

    $nids = $query->execute();

    $items = [];
    $file_url_generator = \Drupal::service('file_url_generator');

    if (!empty($nids)) {

      $nodes = Node::loadMultiple($nids);

      foreach ($nodes as $node) {

        $foto = '';

        if (!$node->get('field_foto')->isEmpty()) {
          $image = $node->get('field_foto')->entity;
          $foto = $file_url_generator->generateAbsoluteString($image->getFileUri());
        }

        $items[] = [
          'foto' => $foto,
          'nombre' => $node->get('field_nombre')->value ?? '',
          'cargo' => $node->get('field_cargo_persona')->value ?? '',
          'testimonio' => $node->get('field_testimonio')->value ?? '',
        ];
      }
    }

    return [
      '#theme' => 'block_testimonios',
      '#titulo_parte_1' => $this->configuration['titulo_parte_1'],
      '#titulo_parte_2' => $this->configuration['titulo_parte_2'],
      '#texto_boton' => $this->configuration['texto_boton'],
      '#texto_boton_highlight' => $this->configuration['texto_boton_highlight'],
      '#url_boton' => $this->configuration['url_boton'],
      '#items' => $items,
      '#attached' => [
        'library' => [
          'dermau_core/testimonios-swiper',
        ],
      ],
      '#cache' => [
        'tags' => ['node_list'],
      ],
    ];
  }

}
