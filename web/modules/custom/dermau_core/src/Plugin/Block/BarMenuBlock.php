<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides DermaU bar menu block.
 *
 * @Block(
 *   id = "dermau_bar_menu_block",
 *   admin_label = @Translation("DermaU - Barra menú anclas")
 * )
 */
class BarMenuBlock extends BlockBase
{

	/**
	 * Temporary form-state key.
	 */
	protected const ITEMS_STATE_KEY = 'dermau_bar_menu_items';

	/**
	 * Allowed icons.
	 */
	protected const ALLOWED_ICONS = [
		'book',
		'cap',
		'question',
	];

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
	 * {@inheritdoc}
	 */
	public function blockForm($form, FormStateInterface $form_state)
	{
		$items = $this->getWorkingItems($form_state);

		$form['items'] = [
			'#type' => 'container',
			'#tree' => TRUE,
			'#prefix' => '<div id="dermau-bar-menu-items-wrapper">',
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
				'#default_value' => $item['text'],
				'#required' => TRUE,
			];

			$form['items'][$delta]['url'] = [
				'#type' => 'textfield',
				'#title' => $this->t('Enlace o ancla'),
				'#default_value' => $item['url'],
				'#required' => TRUE,
				'#description' => $this->t('Ejemplo: #oferta-academica o /pagina-interna'),
			];

			$form['items'][$delta]['icon'] = [
				'#type' => 'select',
				'#title' => $this->t('Ícono'),
				'#default_value' => $item['icon'],
				'#options' => [
					'book' => $this->t('Libro'),
					'cap' => $this->t('Birrete'),
					'question' => $this->t('Pregunta'),
				],
				'#required' => TRUE,
			];

			$form['items'][$delta]['actions'] = [
				'#type' => 'actions',
			];

			$form['items'][$delta]['actions']['remove'] = [
				'#type' => 'submit',
				'#value' => $this->t('Eliminar ítem'),
				'#name' => 'remove_item_' . $delta,
				'#submit' => [[$this, 'removeItemSubmit']],
				'#ajax' => [
					'callback' => [$this, 'ajaxRefresh'],
					'wrapper' => 'dermau-bar-menu-items-wrapper',
				],
				'#limit_validation_errors' => [],
				'#item_delta' => $delta,
			];
		}

		$form['actions'] = [
			'#type' => 'actions',
		];

		$form['actions']['add_item'] = [
			'#type' => 'submit',
			'#value' => $this->t('Agregar ítem'),
			'#submit' => [[$this, 'addItemSubmit']],
			'#ajax' => [
				'callback' => [$this, 'ajaxRefresh'],
				'wrapper' => 'dermau-bar-menu-items-wrapper',
			],
			'#limit_validation_errors' => [],
		];

		return $form;
	}

	/**
	 * Add item submit handler.
	 */
	public function addItemSubmit(array &$form, FormStateInterface $form_state)
	{
		$items = $this->syncItemsFromUserInput($form_state);

		$items[] = [
			'text' => '',
			'url' => '',
			'icon' => 'book',
		];

		$this->setWorkingItems($form_state, $items);
		$form_state->setRebuild(TRUE);
	}

	/**
	 * Remove item submit handler.
	 */
	public function removeItemSubmit(array &$form, FormStateInterface $form_state)
	{
		$items = $this->syncItemsFromUserInput($form_state);

		$trigger = $form_state->getTriggeringElement();
		$delta = isset($trigger['#item_delta']) ? (int) $trigger['#item_delta'] : NULL;

		if ($delta !== NULL && isset($items[$delta])) {
			unset($items[$delta]);
			$items = array_values($items);
		}

		$this->setWorkingItems($form_state, $items);
		$form_state->setRebuild(TRUE);
	}

	/**
	 * Ajax callback.
	 */
	public function ajaxRefresh(array &$form, FormStateInterface $form_state)
	{
		return $form['settings']['items'] ?? $form['items'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit($form, FormStateInterface $form_state)
	{
		$items = $this->extractItemsFromUserInput($form_state);

		if (empty($items)) {
			$items = $this->getWorkingItems($form_state);
		}

		$clean_items = [];

		foreach ($items as $item) {
			if ($item['text'] === '' || $item['url'] === '') {
				continue;
			}

			$clean_items[] = $item;
		}

		$this->configuration['items'] = array_values($clean_items);
		$this->setWorkingItems($form_state, $clean_items);
	}

	/**
	 * {@inheritdoc}
	 */
	public function build()
	{
		return [
			'#theme' => 'block_bar_menu',
			'#items' => $this->normalizeItems($this->configuration['items'] ?? []),
			'#cache' => [
				'max-age' => 0,
			],
		];
	}

	/**
	 * Gets current working items from form state or configuration.
	 */
	protected function getWorkingItems(FormStateInterface $form_state): array
	{
		$items = $form_state->get(static::ITEMS_STATE_KEY);

		if (!is_array($items)) {
			$items = $this->normalizeItems($this->configuration['items'] ?? []);
			$form_state->set(static::ITEMS_STATE_KEY, $items);
		}

		return $items;
	}

	/**
	 * Stores items in form state.
	 */
	protected function setWorkingItems(FormStateInterface $form_state, array $items): void
	{
		$form_state->set(static::ITEMS_STATE_KEY, $this->normalizeItems($items));
	}

	/**
	 * Syncs working state with user input before add/remove operations.
	 */
	protected function syncItemsFromUserInput(FormStateInterface $form_state): array
	{
		$current_items = $this->getWorkingItems($form_state);
		$submitted_items = $this->extractItemsFromUserInput($form_state);

		if (empty($submitted_items)) {
			return $current_items;
		}

		foreach ($current_items as $delta => &$item) {
			if (isset($submitted_items[$delta])) {
				$item['text'] = $submitted_items[$delta]['text'];
				$item['url'] = $submitted_items[$delta]['url'];
				$item['icon'] = $submitted_items[$delta]['icon'];
			}
		}
		unset($item);

		$current_items = $this->normalizeItems($current_items);
		$this->setWorkingItems($form_state, $current_items);

		return $current_items;
	}

	/**
	 * Extracts submitted items from raw user input.
	 *
	 * For block configuration forms, fields usually come nested under "settings".
	 */
	protected function extractItemsFromUserInput(FormStateInterface $form_state): array
	{
		$input = $form_state->getUserInput();
		$items = [];

		if (isset($input['settings']['items']) && is_array($input['settings']['items'])) {
			$items = $input['settings']['items'];
		} elseif (isset($input['items']) && is_array($input['items'])) {
			$items = $input['items'];
		}

		return $this->normalizeItems($items);
	}

	/**
	 * Normalizes items structure.
	 */
	protected function normalizeItems(array $items): array
	{
		$normalized = [];

		foreach ($items as $item) {
			if (!is_array($item)) {
				continue;
			}

			$icon = isset($item['icon']) ? (string) $item['icon'] : 'book';
			if (!in_array($icon, static::ALLOWED_ICONS, TRUE)) {
				$icon = 'book';
			}

			$normalized[] = [
				'text' => isset($item['text']) ? trim((string) $item['text']) : '',
				'url' => isset($item['url']) ? trim((string) $item['url']) : '',
				'icon' => $icon,
			];
		}

		return array_values($normalized);
	}
}
