<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Component\Utility\NestedArray;
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
	 * Temporary key in form state.
	 */
	private const STATE_KEY = 'dermau_bar_menu_items_state';

	/**
	 * Allowed icon machine names.
	 */
	private const ALLOWED_ICONS = [
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
		$items = $this->getStateItems($form_state);

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
				'#attributes' => [
					'data-item-delta' => (string) $delta,
				],
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
	 * Add item AJAX submit.
	 */
	public function addItemSubmit(array &$form, FormStateInterface $form_state)
	{
		$items = $this->getSubmittedItemsOrState($form_state);

		$items[] = [
			'text' => '',
			'url' => '',
			'icon' => 'book',
		];

		$this->setStateItems($form_state, $items);
		$form_state->setRebuild(TRUE);
	}

	/**
	 * Remove item AJAX submit.
	 */
	public function removeItemSubmit(array &$form, FormStateInterface $form_state)
	{
		$items = $this->getSubmittedItemsOrState($form_state);

		$trigger = $form_state->getTriggeringElement();
		$delta = NULL;

		if (isset($trigger['#attributes']['data-item-delta'])) {
			$delta = (int) $trigger['#attributes']['data-item-delta'];
		}

		if ($delta !== NULL && array_key_exists($delta, $items)) {
			unset($items[$delta]);
			$items = array_values($items);
		}

		$this->setStateItems($form_state, $items);
		$form_state->setRebuild(TRUE);
	}

	/**
	 * AJAX wrapper refresh.
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
		$items = $this->extractSubmittedItems($form_state);

		if (empty($items)) {
			$items = $this->getStateItems($form_state);
		}

		$clean_items = [];

		foreach ($items as $item) {
			if ($item['text'] === '' || $item['url'] === '') {
				continue;
			}

			$clean_items[] = $item;
		}

		$this->configuration['items'] = array_values($clean_items);
		$this->setStateItems($form_state, $clean_items);
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
	private function getStateItems(FormStateInterface $form_state): array
	{
		$items = $form_state->get(self::STATE_KEY);

		if (!is_array($items)) {
			$items = $this->normalizeItems($this->configuration['items'] ?? []);
			$form_state->set(self::STATE_KEY, $items);
		}

		return $items;
	}

	/**
	 * Stores normalized working items into form state.
	 */
	private function setStateItems(FormStateInterface $form_state, array $items): void
	{
		$form_state->set(self::STATE_KEY, $this->normalizeItems($items));
	}

	/**
	 * Returns submitted items if present, otherwise current state items.
	 */
	private function getSubmittedItemsOrState(FormStateInterface $form_state): array
	{
		$submitted = $this->extractSubmittedItems($form_state);

		if (!empty($submitted)) {
			return $submitted;
		}

		return $this->getStateItems($form_state);
	}

	/**
	 * Extracts items from raw user input.
	 */
	private function extractSubmittedItems(FormStateInterface $form_state): array
	{
		$input = $form_state->getUserInput();

		$items = NestedArray::getValue($input, ['settings', 'items']);

		if (!is_array($items)) {
			$items = NestedArray::getValue($input, ['items']);
		}

		if (!is_array($items)) {
			return [];
		}

		$normalized = [];

		foreach ($items as $delta => $item) {
			if (!is_array($item)) {
				continue;
			}

			$normalized[$delta] = [
				'text' => isset($item['text']) ? trim((string) $item['text']) : '',
				'url' => isset($item['url']) ? trim((string) $item['url']) : '',
				'icon' => isset($item['icon']) ? (string) $item['icon'] : 'book',
			];
		}

		return $this->normalizeItems($normalized);
	}

	/**
	 * Normalizes item structure.
	 */
	private function normalizeItems(array $items): array
	{
		$normalized = [];

		foreach ($items as $item) {
			if (!is_array($item)) {
				continue;
			}

			$icon = isset($item['icon']) ? (string) $item['icon'] : 'book';
			if (!in_array($icon, self::ALLOWED_ICONS, TRUE)) {
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
