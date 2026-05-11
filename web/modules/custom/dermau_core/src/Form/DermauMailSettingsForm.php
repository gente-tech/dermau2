<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class DermauMailSettingsForm extends ConfigFormBase
{

	public function getFormId(): string
	{
		return 'dermau_core_mail_settings_form';
	}

	protected function getEditableConfigNames(): array
	{
		return [
			'dermau_core.mail_settings',
		];
	}

	public function buildForm(array $form, FormStateInterface $form_state): array
	{
		$form['intro'] = [
			'#markup' => '<p>Desde este formulario se configurará qué plantilla, asunto y copia interna usa cada acción de correo de DermaU.</p>',
		];

		return parent::buildForm($form, $form_state);
	}

	public function submitForm(array &$form, FormStateInterface $form_state): void
	{
		parent::submitForm($form, $form_state);
	}
}
