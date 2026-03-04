<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * @Block(
 *   id = "dermau_convenios_block",
 *   admin_label = @Translation("DermaU - Convenios Universitarios (desde contenido)")
 * )
 */
class ConveniosBlock extends BlockBase {

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

      foreach ($nodes as $node) {

        if (!$node->hasField('field_logo') || $node->get('field_logo')->isEmpty()) {
          continue;
        }

        $image = $node->get('field_logo')->entity;
        $image_url = file_create_url($image->getFileUri());

        $link_url = '';

        if ($node->hasField('field_link') && !$node->get('field_link')->isEmpty()) {
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
      '#items' => $items,
      '#attached' => [
        'library' => [
          'dermau_core/convenios-swiper',
        ],
      ],
      '#cache' => [
        'tags' => ['node_list'],
      ],
    ];
  }

}
