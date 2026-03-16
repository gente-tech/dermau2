<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @Block(
 *   id = "dermau_bar_menu_block",
 *   admin_label = @Translation("DermaU - Barra menú anclas")
 * )
 */
class BarMenuBlock extends BlockBase
{

	/**
	 * {@inheritdoc}
	 */
	public function defaultConfiguration()
	{
		return [
			'items' => [
				[
					'text' => 'Oferta Académica',
					'url' => '#oferta-academica',
					'icon' => 'book',
				],
				[
					'text' => 'Convenios universitarios',
					'url' => '#convenios-universitarios',
					'icon' => 'cap',
				],
				[
					'text' => 'Preguntas frecuentes',
					'url' => '#preguntas-frecuentes',
					'icon' => 'question',
				],
			],
		];
	}

	/**
	 * Obtiene los items enviados en el form.
	 */
	protected static function getSubmittedItems(FormStateInterface $form_state)
	{
		$items = $form_state->getValue(['settings', 'items']);

		if ($items === NULL) {
			$items = $form_state->getValue('items');
		}

		return is_array($items) ? $items : [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockForm($form, FormStateInterface $form_state)
	{
		$items = $form_state->get('bar_menu_items');

		if ($items === NULL) {
			$items = $this->configuration['items'] ?? [];
			$form_state->set('bar_menu_items', $items);
		}

		$form['items'] = [
			'#type' => 'container',
			'#tree' => TRUE,
			'#prefix' => '<div id="bar-menu-items-wrapper">',
			'#suffix' => '</div>',
		];

		foreach ($items as $delta => $item) {
			$form['items'][$delta] = [
				'#type' => 'details',
				'#title' => $this->t('Ítem @num', ['@num' => $delta + 1]),
				'#open' => TRUE,
			];

			$form['items'][$delta]['text'] = [
				'#type' => 'textfield',
				'#title' => $this->t('Texto'),
				'#default_value' => $item['text'] ?? '',
				'#required' => TRUE,
			];

			$form['items'][$delta]['url'] = [
				'#type' => 'textfield',
				'#title' => $this->t('Enlace o ancla'),
				'#default_value' => $item['url'] ?? '',
				'#required' => TRUE,
				'#description' => $this->t('Ejemplo: #oferta-academica o /pagina-interna'),
			];

			$form['items'][$delta]['icon'] = [
				'#type' => 'select',
				'#title' => $this->t('Ícono'),
				'#default_value' => $item['icon'] ?? 'book',
				'#options' => [
					'book' => $this->t('Libro'),
					'cap' => $this->t('Birrete'),
					'question' => $this->t('Pregunta'),
				],
				'#required' => TRUE,
			];

			$form['items'][$delta]['remove'] = [
				'#type' => 'submit',
				'#value' => $this->t('Eliminar ítem'),
				'#name' => 'remove_item_' . $delta,
				'#submit' => [[static::class, 'removeItemSubmit']],
				'#ajax' => [
					'callback' => [static::class, 'ajaxRefresh'],
					'wrapper' => 'bar-menu-items-wrapper',
				],
				'#limit_validation_errors' => [],
				'#item_delta' => $delta,
			];
		}

		$form['add_item'] = [
			'#type' => 'submit',
			'#value' => $this->t('Agregar ítem'),
			'#submit' => [[static::class, 'addItemSubmit']],
			'#ajax' => [
				'callback' => [static::class, 'ajaxRefresh'],
				'wrapper' => 'bar-menu-items-wrapper',
			],
			'#limit_validation_errors' => [],
		];

		return $form;
	}

	/**
	 * Agrega un item.
	 */
	public static function addItemSubmit(array &$form, FormStateInterface $form_state)
	{
		$items = $form_state->get('bar_menu_items') ?? [];

		$submitted_items = static::getSubmittedItems($form_state);
		if (!empty($submitted_items)) {
			$items = $submitted_items;
		}

		$items[] = [
			'text' => '',
			'url' => '',
			'icon' => 'book',
		];

		$form_state->set('bar_menu_items', array_values($items));
		$form_state->setRebuild(TRUE);
	}

	/**
	 * Elimina un item.
	 */
	public static function removeItemSubmit(array &$form, FormStateInterface $form_state)
	{
		$trigger = $form_state->getTriggeringElement();
		$delta = $trigger['#item_delta'];

		$items = static::getSubmittedItems($form_state);

		if (empty($items)) {
			$items = $form_state->get('bar_menu_items') ?? [];
		}

		if (isset($items[$delta])) {
			unset($items[$delta]);
		}

		$items = array_values($items);

		$form_state->set('bar_menu_items', $items);
		$form_state->setRebuild(TRUE);
	}

	/**
	 * Callback AJAX.
	 */
	public static function ajaxRefresh(array &$form, FormStateInterface $form_state)
	{
		if (isset($form['settings']['items'])) {
			return $form['settings']['items'];
		}

		return $form['items'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit($form, FormStateInterface $form_state)
	{
		$items = $form_state->getValue('items');

		if ($items === NULL) {
			$items = $form_state->getValue(['settings', 'items']);
		}

		$items = is_array($items) ? $items : [];

		$clean_items = [];

		foreach ($items as $item) {
			$text = trim($item['text'] ?? '');
			$url = trim($item['url'] ?? '');
			$icon = trim($item['icon'] ?? 'book');

			if ($text !== '' && $url !== '') {
				$clean_items[] = [
					'text' => $text,
					'url' => $url,
					'icon' => $icon,
				];
			}
		}

		$this->configuration['items'] = $clean_items;
		$form_state->set('bar_menu_items', $clean_items);
	}

	/**
	 * {@inheritdoc}
	 */
	public function build()
	{
		return [
			'#theme' => 'block_bar_menu',
			'#items' => $this->configuration['items'] ?? [],
			'#cache' => [
				'max-age' => 0,
			],
		];
	}
}
