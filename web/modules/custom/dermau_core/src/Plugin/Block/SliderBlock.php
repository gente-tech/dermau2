<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;

/**
 * Provides a Dermau Slider Block.
 *
 * @Block(
 *   id = "dermau_slider_block",
 *   admin_label = @Translation("Dermau Slider Block"),
 * )
 */
class SliderBlock extends BlockBase
{

  public function defaultConfiguration()
  {
    return [
      'float_chat_image' => [],
      'float_chat_image_alt' => '',
    ];
  }

  public function blockForm($form, FormStateInterface $form_state)
  {
    $form['float_chat_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Imagen botón flotante'),
      '#upload_location' => 'public://slider-block/',
      '#default_value' => $this->configuration['float_chat_image'] ?? NULL,
      '#upload_validators' => [
        'file_validate_extensions' => ['svg png jpg jpeg webp'],
      ],
    ];

    $form['float_chat_image_alt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Texto alternativo'),
      '#default_value' => $this->configuration['float_chat_image_alt'] ?? '',
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state)
  {
    $float_chat_image = $form_state->getValue('float_chat_image');

    if (!empty($float_chat_image[0])) {
      $file = File::load($float_chat_image[0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
      }
    }

    $this->configuration['float_chat_image'] = $float_chat_image;
    $this->configuration['float_chat_image_alt'] = $form_state->getValue('float_chat_image_alt');
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
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

    $float_chat_image_url = '';
    $float_chat_image_alt = $this->configuration['float_chat_image_alt'] ?? '';

    if (!empty($this->configuration['float_chat_image'][0])) {
      $file = File::load($this->configuration['float_chat_image'][0]);
      if ($file) {
        $float_chat_image_url = \Drupal::service('file_url_generator')
          ->generateAbsoluteString($file->getFileUri());
      }
    }

    return [
      '#theme' => 'dermau_slider_block',
      '#sliders' => $sliders,
      '#float_chat_image' => [
        'url' => $float_chat_image_url,
        'alt' => $float_chat_image_alt,
      ],
      '#cache' => [
        'tags' => ['node_list:slider'],
        'max-age' => 0,
      ],
    ];
  }
}
