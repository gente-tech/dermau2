<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * @Block(
 *   id = "dermau_faq_block",
 *   admin_label = @Translation("DermaU - Preguntas frecuentes")
 * )
 */
class FaqBlock extends BlockBase {

  public function defaultConfiguration() {
    return [
      'titulo_parte_1' => 'Preguntas',
      'titulo_parte_2' => 'frecuentes',
      'descripcion' => 'Encuentra respuestas rápidas sobre nuestros programas, modalidad de estudio y procesos de inscripción en DermaU.',
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

    $form['descripcion'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Descripción'),
      '#default_value' => $this->configuration['descripcion'],
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['titulo_parte_1'] = $form_state->getValue('titulo_parte_1');
    $this->configuration['titulo_parte_2'] = $form_state->getValue('titulo_parte_2');
    $this->configuration['descripcion'] = $form_state->getValue('descripcion');
  }

  public function build() {

    $categorias = [];
    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('categoria_preguntas_frecuentes');

    foreach ($terms as $term_data) {

      $term = Term::load($term_data->tid);

      $query = \Drupal::entityQuery('node')
        ->condition('type', 'pregunta_frecuente')
        ->condition('status', 1)
        ->condition('field_categoria', $term->id())
        ->accessCheck(TRUE);

      $nids = $query->execute();
      $items = [];

      if (!empty($nids)) {

        $nodes = Node::loadMultiple($nids);

        foreach ($nodes as $node) {
          $items[] = [
            'pregunta' => $node->label(),
            'respuesta' => $node->get('field_respuesta')->value ?? '',
          ];
        }
      }

      $categorias[] = [
        'id' => \Drupal::service('transliteration')->transliterate($term->getName(), 'es'),
        'nombre' => $term->getName(),
        'preguntas' => $items,
      ];
    }

    return [
      '#theme' => 'block_faq',
      '#titulo_parte_1' => $this->configuration['titulo_parte_1'],
      '#titulo_parte_2' => $this->configuration['titulo_parte_2'],
      '#descripcion' => $this->configuration['descripcion'],
      '#categorias' => $categorias,
      '#attached' => [
        'library' => [
          'dermau_core/faq',
        ],
      ],
      '#cache' => [
        'tags' => ['node_list', 'taxonomy_term_list'],
      ],
    ];
  }

}
