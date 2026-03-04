<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

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
      '#items' => $items,
      '#cache' => [
        'tags' => ['node_list'],
      ],
    ];
  }

}
