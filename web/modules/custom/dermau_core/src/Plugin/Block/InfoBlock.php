<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a Dermau Info Block.
 *
 * @Block(
 *   id = "dermau_info_block",
 *   admin_label = @Translation("Dermau Info Block")
 * )
 */
class InfoBlock extends BlockBase
{

	/**
	 * {@inheritdoc}
	 */
	public function defaultConfiguration()
	{
		return [
			'bg_color' => '',
			'titulo_normal' => '',
			'titulo_resaltado' => '',
			'descripcion' => '',
			'imagen' => [],
			'imagen_alt' => '',
			'label_btn' => '',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockForm($form, FormStateInterface $form_state)
	{
		$form['bg_color'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Color de fondo'),
			'#default_value' => $this->configuration['bg_color'] ?? '',
		];

		$form['titulo_normal'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Título normal'),
			'#default_value' => $this->configuration['titulo_normal'] ?? '',
		];

		$form['titulo_resaltado'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Título resaltado'),
			'#default_value' => $this->configuration['titulo_resaltado'] ?? '',
		];

		$form['descripcion'] = [
			'#type' => 'textarea',
			'#title' => $this->t('Descripción'),
			'#default_value' => $this->configuration['descripcion'] ?? '',
		];

		$form['imagen'] = [
			'#type' => 'managed_file',
			'#title' => $this->t('Imagen'),
			'#upload_location' => 'public://info-block/',
			'#default_value' => $this->configuration['imagen'] ?? [],
			'#multiple' => FALSE,
		];

		$form['imagen_alt'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Alt de la imagen'),
			'#default_value' => $this->configuration['imagen_alt'] ?? '',
		];
		
		$form['label_btn'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Label del botón'),
			'#default_value' => $this->configuration['label_btn'] ?? '',
		];

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit($form, FormStateInterface $form_state)
	{
		\Drupal::logger('dermau_info_block')->notice('<pre>@data</pre>', [
			'@data' => print_r($form_state->getValues(), TRUE),
		]);
		$imagen = $form_state->getValue('imagen');

		if (empty($imagen) && !empty($this->configuration['imagen'])) {
			$imagen = $this->configuration['imagen'];
		}

		if (!empty($imagen[0])) {
			$file = File::load($imagen[0]);

			if ($file) {
				$file->setPermanent();
				$file->save();
			}
		}

		$this->configuration['bg_color'] = $form_state->getValue('bg_color');
		$this->configuration['titulo_normal'] = $form_state->getValue('titulo_normal');
		$this->configuration['titulo_resaltado'] = $form_state->getValue('titulo_resaltado');
		$this->configuration['descripcion'] = $form_state->getValue('descripcion');
		$this->configuration['imagen'] = $imagen ?: [];
		$this->configuration['imagen_alt'] = $form_state->getValue('imagen_alt');
		$this->configuration['label_btn'] = $form_state->getValue('label_btn');
	}

	/**
	 * {@inheritdoc}
	 */
	public function build()
	{
		$image_url = '';
		$image_alt = $this->configuration['imagen_alt'] ?? '';

		if (!empty($this->configuration['imagen'][0])) {
			$file = File::load($this->configuration['imagen'][0]);

			if ($file) {
				$image_url = \Drupal::service('file_url_generator')
					->generateAbsoluteString($file->getFileUri());
			}
		}

		return [
			'#theme' => 'dermau_info_block',
			'#bg_color' => $this->configuration['bg_color'] ?? '',
			'#titulo_normal' => $this->configuration['titulo_normal'] ?? '',
			'#titulo_resaltado' => $this->configuration['titulo_resaltado'] ?? '',
			'#descripcion' => $this->configuration['descripcion'] ?? '',
			'#label_btn' => $this->configuration['label_btn'] ?? '',
			'#imagen' => [
				'url' => $image_url,
				'alt' => $image_alt,
			],
			'#cache' => [
				'max-age' => 0,
			],
		];
	}
}
