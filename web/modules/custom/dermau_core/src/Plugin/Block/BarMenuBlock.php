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
	 * Form state key for temporary items.
	 */
	protected const FORM_STATE_ITEMS_KEY = 'dermau_bar_menu_items';

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
	 * Returns normalized items from configuration.
	 */
	protected function getConfiguredItems(): array
	{
		$items = $this->configuration['items'] ?? [];
		return $this->normalizeItems($items);
	}

	/**
	 * Returns normalized items from temporary form state or configuration.
	 */
	protected function getWorkingItems(FormStateInterface $form_state): array
	{
		$items = $form_state->get(static::FORM_STATE_ITEMS_KEY);

		if (!is_array($items)) {
			$items = $this->getConfiguredItems();
			$form_state->set(static::FORM_STATE_ITEMS_KEY, $items);
		}

		return $this->normalizeItems($items);
	}

	/**
	 * Stores normalized working items in form state.
	 */
	protected function setWorkingItems(FormStateInterface $form_state, array $items): void
	{
		$form_state->set(static::FORM_STATE_ITEMS_KEY, $this->normalizeItems($items));
	}

	/**
	 * Extracts submitted editable values from the block form.
	 *
	 * This intentionally ignores action buttons and only keeps editable fields.
	 */
	protected function extractSubmittedItems(FormStateInterface $form_state): array
	{
		$values = $form_state->getValues();

		// In block config forms, fields may be nested under "settings".
		$items = $values['items'] ?? NULL;

		if ($items === NULL && isset($values['settings']['items'])) {
			$items = $values['settings']['items'];
		}

		if (!is_array($items)) {
			return [];
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

			$normalized[] = [
				'text' => isset($item['text']) ? trim((string) $item['text']) : '',
				'url' => isset($item['url']) ? trim((string) $item['url']) : '',
				'icon' => isset($item['icon']) && in_array($item['icon'], ['book', 'cap', 'question'], TRUE)
					? $item['icon']
					: 'book',
			];
		}

		return array_values($normalized);
	}

	/**
	 * Syncs temporary working items with current submitted field values.
	 *
	 * This preserves user edits before add/remove actions.
	 */
	protected function syncWorkingItemsWithInput(FormStateInterface $form_state): array
	{
		$working_items = $this->getWorkingItems($form_state);
		$submitted_items = $this->extractSubmittedItems($form_state);

		if (empty($submitted_items)) {
			return $working_items;
		}

		foreach ($working_items as $delta => &$item) {
			if (isset($submitted_items[$delta])) {
				$item['text'] = $submitted_items[$delta]['text'] ?? '';
				$item['url'] = $submitted_items[$delta]['url'] ?? '';
				$item['icon'] = $submitted_items[$delta]['icon'] ?? 'book';
			}
		}
		unset($item);

		$working_items = $this->normalizeItems($working_items);
		$this->setWorkingItems($form_state, $working_items);

		return $working_items;
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
				'#title' => $this->t('Ítem @number', ['@number' => $delta + 1]),
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
				'#submit' => [[static::class, 'removeItemSubmit']],
				'#ajax' => [
					'callback' => [static::class, 'ajaxRefresh'],
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
			'#submit' => [[static::class, 'addItemSubmit']],
			'#ajax' => [
				'callback' => [static::class, 'ajaxRefresh'],
				'wrapper' => 'dermau-bar-menu-items-wrapper',
			],
			'#limit_validation_errors' => [],
		];

		return $form;
	}

	/**
	 * Add item submit handler.
	 */
	public static function addItemSubmit(array &$form, FormStateInterface $form_state): void
	{
		/** @var static $block */
		$block = $form_state->getFormObject()->getPlugin();

		$items = $block->syncWorkingItemsWithInput($form_state);

		$items[] = [
			'text' => '',
			'url' => '',
			'icon' => 'book',
		];

		$block->setWorkingItems($form_state, $items);
		$form_state->setRebuild(TRUE);
	}

	/**
	 * Remove item submit handler.
	 */
	public static function removeItemSubmit(array &$form, FormStateInterface $form_state): void
	{
		/** @var static $block */
		$block = $form_state->getFormObject()->getPlugin();

		$items = $block->syncWorkingItemsWithInput($form_state);

		$trigger = $form_state->getTriggeringElement();
		$delta = isset($trigger['#item_delta']) ? (int) $trigger['#item_delta'] : NULL;

		if ($delta !== NULL && array_key_exists($delta, $items)) {
			unset($items[$delta]);
		}

		$block->setWorkingItems($form_state, array_values($items));
		$form_state->setRebuild(TRUE);
	}

	/**
	 * Ajax refresh callback.
	 */
	public static function ajaxRefresh(array &$form, FormStateInterface $form_state)
	{
		return $form['settings']['items'] ?? $form['items'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit($form, FormStateInterface $form_state)
	{
		$items = $this->extractSubmittedItems($form_state);

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
			'#items' => $this->getConfiguredItems(),
			'#cache' => [
				'max-age' => 0,
			],
		];
	}
}
