<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProgramaInteresadoExportForm extends FormBase
{
	protected Connection $database;

	public function __construct(Connection $database)
	{
		$this->database = $database;
	}

	public static function create(ContainerInterface $container)
	{
		return new static(
			$container->get('database')
		);
	}

	public function getFormId()
	{
		return 'dermau_programa_interesado_export_form';
	}

	public function buildForm(array $form, FormStateInterface $form_state)
	{
		$total = (int) $this->database
			->select('dermau_programa_interesado', 'dpi')
			->countQuery()
			->execute()
			->fetchField();

		$form['intro'] = [
			'#type' => 'markup',
			'#markup' => '<p>Desde aquí puedes descargar en Excel los registros almacenados en la tabla <strong>dermau_programa_interesado</strong>.</p>',
		];

		$form['total'] = [
			'#type' => 'item',
			'#title' => $this->t('Total de registros'),
			'#markup' => '<strong>' . $total . '</strong>',
		];

		$export_url = Url::fromRoute('dermau_core.programa_interesado_export_download');

		$form['actions'] = [
			'#type' => 'actions',
		];

		$form['actions']['download'] = Link::fromTextAndUrl(
			$this->t('Descargar Excel'),
			$export_url
		)->toRenderable();

		$form['actions']['download']['#attributes']['class'] = [
			'button',
			'button--primary',
		];

		return $form;
	}

	public function submitForm(array &$form, FormStateInterface $form_state)
	{
		// No se usa submit porque la descarga se hace por route/controller.
	}
}
