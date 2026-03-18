<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides an Indicators Block.
 *
 * @Block(
 *   id = "dermau_indicators_block",
 *   admin_label = @Translation("Dermau Indicators Block"),
 * )
 */
class IndicatorsBlock extends BlockBase
{

	public function defaultConfiguration()
	{
		return [
			'heading_main' => '',
			'heading_highlight' => '',
			'body_text' => '',
			'indicators' => [],
		];
	}

	public function blockForm($form, FormStateInterface $form_state)
	{

		$form['heading_main'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Título principal'),
			'#default_value' => $this->configuration['heading_main'],
		];

		$form['heading_highlight'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Título destacado'),
			'#default_value' => $this->configuration['heading_highlight'],
		];

		$form['body_text'] = [
			'#type' => 'textarea',
			'#title' => $this->t('Descripción'),
			'#default_value' => $this->configuration['body_text'],
		];

		$items = $this->configuration['indicators'] ?? [];

		$form['indicators'] = [
			'#type' => 'fieldset',
			'#title' => $this->t('Indicadores'),
		];

		for ($i = 0; $i < 4; $i++) {

			$form['indicators'][$i]['icon'] = [
				'#type' => 'managed_file',
				'#title' => $this->t('Icono'),
				'#upload_location' => 'public://indicators/',
				'#default_value' => $items[$i]['icon'] ?? NULL,
			];

			$form['indicators'][$i]['value'] = [
				'#type' => 'textfield',
				'#title' => $this->t('Valor'),
				'#default_value' => $items[$i]['value'] ?? '',
			];

			$form['indicators'][$i]['label'] = [
				'#type' => 'textfield',
				'#title' => $this->t('Texto'),
				'#default_value' => $items[$i]['label'] ?? '',
			];
		}

		return $form;
	}

	public function blockSubmit($form, FormStateInterface $form_state)
	{

		$items = $form_state->getValue('indicators');

		foreach ($items as &$item) {
			if (!empty($item['icon'][0])) {
				$file = File::load($item['icon'][0]);
				if ($file) {
					$file->setPermanent();
					$file->save();
				}
			}
		}

		$this->configuration['heading_main'] = $form_state->getValue('heading_main');
		$this->configuration['heading_highlight'] = $form_state->getValue('heading_highlight');
		$this->configuration['body_text'] = $form_state->getValue('body_text');
		$this->configuration['indicators'] = $items;
	}

	public function build()
	{

		$items = [];

		foreach ($this->configuration['indicators'] as $item) {

			$icon_url = '';

			if (!empty($item['icon'][0])) {
				$file = File::load($item['icon'][0]);
				if ($file) {
					$icon_url = \Drupal::service('file_url_generator')
						->generateAbsoluteString($file->getFileUri());
				}
			}

			$items[] = [
				'icon' => $icon_url,
				'value' => $item['value'] ?? '',
				'label' => $item['label'] ?? '',
			];
		}

		return [
			'#theme' => 'dermau_indicators_block',
			'#heading_main' => $this->configuration['heading_main'],
			'#heading_highlight' => $this->configuration['heading_highlight'],
			'#body_text' => $this->configuration['body_text'],
			'#indicators' => $items,
			'#cache' => ['max-age' => 0],
		];
	}
}
