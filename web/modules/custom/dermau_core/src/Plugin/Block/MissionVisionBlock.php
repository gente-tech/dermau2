<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Mission Vision Block.
 *
 * @Block(
 *   id = "dermau_mission_vision_block",
 *   admin_label = @Translation("Dermau Mission Vision Block"),
 * )
 */
class MissionVisionBlock extends BlockBase
{

	public function defaultConfiguration()
	{
		return [
			'mission_title_normal' => '',
			'mission_title_color' => '',
			'mission_description' => '',
			'vision_title_normal' => '',
			'vision_title_color' => '',
			'vision_description' => '',
		];
	}

	public function blockForm($form, FormStateInterface $form_state)
	{
		$form['mission'] = [
			'#type' => 'fieldset',
			'#title' => $this->t('Misión'),
		];

		$form['mission']['mission_title_normal'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Título normal'),
			'#default_value' => $this->configuration['mission_title_normal'],
		];

		$form['mission']['mission_title_color'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Título color'),
			'#default_value' => $this->configuration['mission_title_color'],
		];

		$form['mission']['mission_description'] = [
			'#type' => 'textarea',
			'#title' => $this->t('Descripción'),
			'#default_value' => $this->configuration['mission_description'],
		];

		$form['vision'] = [
			'#type' => 'fieldset',
			'#title' => $this->t('Visión'),
		];

		$form['vision']['vision_title_normal'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Título normal'),
			'#default_value' => $this->configuration['vision_title_normal'],
		];

		$form['vision']['vision_title_color'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Título color'),
			'#default_value' => $this->configuration['vision_title_color'],
		];

		$form['vision']['vision_description'] = [
			'#type' => 'textarea',
			'#title' => $this->t('Descripción'),
			'#default_value' => $this->configuration['vision_description'],
		];

		return $form;
	}

	public function blockSubmit($form, FormStateInterface $form_state)
	{
		$this->configuration['mission_title_normal'] = $form_state->getValue('mission_title_normal');
		$this->configuration['mission_title_color'] = $form_state->getValue('mission_title_color');
		$this->configuration['mission_description'] = $form_state->getValue('mission_description');
		$this->configuration['vision_title_normal'] = $form_state->getValue('vision_title_normal');
		$this->configuration['vision_title_color'] = $form_state->getValue('vision_title_color');
		$this->configuration['vision_description'] = $form_state->getValue('vision_description');
	}

	public function build()
	{
		return [
			'#theme' => 'dermau_mission_vision_block',
			'#mission_title_normal' => $this->configuration['mission_title_normal'],
			'#mission_title_color' => $this->configuration['mission_title_color'],
			'#mission_description' => $this->configuration['mission_description'],
			'#vision_title_normal' => $this->configuration['vision_title_normal'],
			'#vision_title_color' => $this->configuration['vision_title_color'],
			'#vision_description' => $this->configuration['vision_description'],
			'#cache' => ['max-age' => 0],
		];
	}
}
