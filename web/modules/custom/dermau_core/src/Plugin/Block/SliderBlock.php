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
 *   admin_label = @Translation("Dermau Slider Block")
 * )
 */
class SliderBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state)
  {
    $form = parent::blockForm($form, $form_state);

    $current_file = NULL;
    if (!empty($this->configuration['float_chat_image_fid'])) {
      $current_file = File::load($this->configuration['float_chat_image_fid']);
    }

    $form['float_chat_image'] = [
      '#type' => 'file',
      '#title' => $this->t('Imagen botón flotante'),
      '#description' => $this->t('Sube un archivo svg, png, jpg, jpeg o webp.'),
    ];

    if ($current_file) {
      $form['float_chat_image_current'] = [
        '#type' => 'item',
        '#title' => $this->t('Archivo actual'),
        '#markup' => $current_file->getFilename(),
      ];
    }

    $form['float_chat_image_alt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Texto alternativo'),
      '#default_value' => $this->configuration['float_chat_image_alt'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state)
  {
    parent::blockSubmit($form, $form_state);

    $validators = [
      'file_validate_extensions' => ['svg png jpg jpeg webp'],
    ];

    $files = file_save_upload('float_chat_image', $validators, 'public://slider-block/', 0);

    if ($files && is_array($files)) {
      $file = reset($files);

      if ($file instanceof File) {
        $file->setPermanent();
        $file->save();

        $this->configuration['float_chat_image_fid'] = $file->id();
      }
    }

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
    $file_url_generator = \Drupal::service('file_url_generator');

    if (!empty($nids)) {
      $nodes = Node::loadMultiple($nids);

      foreach ($nodes as $node) {
        $image_url = '';
        $image_alt = '';

        if (!$node->get('field_imagen')->isEmpty()) {
          $file = $node->get('field_imagen')->entity;
          if ($file) {
            $image_url = $file_url_generator->generateAbsoluteString($file->getFileUri());
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

    if (!empty($this->configuration['float_chat_image_fid'])) {
      $file = File::load($this->configuration['float_chat_image_fid']);
      if ($file) {
        $float_chat_image_url = $file_url_generator->generateAbsoluteString($file->getFileUri());
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
