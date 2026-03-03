<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a Dermau Slider Block.
 *
 * @Block(
 *   id = "dermau_slider_block",
 *   admin_label = @Translation("Dermau Slider Block"),
 * )
 */
class SliderBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'slider')
      ->condition('status', 1)
      ->sort('changed', 'DESC')
      ->accessCheck(TRUE);

    $nids = $query->execute();
    $sliders = [];

    if (!empty($nids)) {
      $nodes = Node::loadMultiple($nids);

      foreach ($nodes as $node) {

        // Imagen (Image field directo)
        $image_url = '';
        $image_alt = '';

        if (!$node->get('field_imagen')->isEmpty()) {
          $file = $node->get('field_imagen')->entity;
          if ($file) {
            $image_url = \Drupal::service('file_url_generator')
              ->generateAbsoluteString($file->getFileUri());

            $image_alt = $node->get('field_imagen')->alt ?? '';
          }
        }

        $sliders[] = [
          'titulo_azul' => $node->get('field_titulo_azul')->value ?? '',
          'titulo_blanco' => $node->get('field_titulo_blanco')->value ?? '',
          'items_texto' => array_column($node->get('field_item_texto')->getValue(), 'value'),
          'texto_cta' => $node->get('field_texto_call_to_action')->value ?? '',
          'url_cta' => !$node->get('field_url_call_to_action')->isEmpty()
            ? $node->get('field_url_call_to_action')->first()->getUrl()->toString()
            : '',
          'image' => [
            'url' => $image_url,
            'alt' => $image_alt,
          ],
        ];
      }
    }

    return [
      '#theme' => 'dermau_slider_block',
      '#sliders' => $sliders,
      '#cache' => [
        'tags' => ['node_list:slider'],
      ],
    ];
  }

}
