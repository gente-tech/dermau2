<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure DermaU mail action settings.
 */
class DermauMailSettingsForm extends ConfigFormBase
{

	/**
	 * {@inheritdoc}
	 */
	public function getFormId(): string
	{
		return 'dermau_core_mail_settings_form';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getEditableConfigNames(): array
	{
		return [
			'dermau_core.mail_settings',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state): array
	{
		$config = $this->config('dermau_core.mail_settings');

		$form['description'] = [
			'#markup' => '<p>Configure qué configuración de correo debe usar cada formulario o acción de DermaU. Los correos disponibles se administran desde Mandrill configuraciones en Enterprise Integrations, por ejemplo: <strong>mail_text_1</strong>, <strong>mail_text_2</strong>, <strong>mail_text_3</strong>.</p>',
		];

		$form['mail_actions'] = [
			'#type' => 'details',
			'#title' => $this->t('Configuración por formulario o acción'),
			'#open' => TRUE,
			'#tree' => TRUE,
		];

		$form['mail_actions']['programa_interesado'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Correo por defecto para preinscripción a programa'),
			'#description' => $this->t('Se usará cuando el programa no tenga configurado el campo Configuración correo preinscripción. Ejemplo: mail_text_1'),
			'#default_value' => $config->get('mail_actions.programa_interesado') ?? 'mail_text_1',
			'#required' => TRUE,
		];

		$form['mail_actions']['descargar_programa'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Correo por defecto para descarga de programa'),
			'#description' => $this->t('Se usará cuando el programa no tenga configurado el campo Configuración correo descarga. Ejemplo: mail_text_2'),
			'#default_value' => $config->get('mail_actions.descargar_programa') ?? 'mail_text_1',
			'#required' => TRUE,
		];

		$form['mail_actions']['contacto_registro'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Formulario de contacto / registro'),
			'#description' => $this->t('Ingrese el nombre de la configuración de correo. Ejemplo: mail_text_3'),
			'#default_value' => $config->get('mail_actions.contacto_registro') ?? 'mail_text_1',
			'#required' => TRUE,
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateForm(array &$form, FormStateInterface $form_state): void
	{
		parent::validateForm($form, $form_state);

		$mail_actions = $form_state->getValue('mail_actions') ?? [];

		foreach ($mail_actions as $action => $mail_config_key) {
			$mail_config_key = trim((string) $mail_config_key);

			if ($mail_config_key === '') {
				$form_state->setErrorByName(
					"mail_actions][$action",
					$this->t('Debe ingresar una configuración de correo válida.')
				);
				continue;
			}

			if (!preg_match('/^mail_text_[0-9]+$/', $mail_config_key)) {
				$form_state->setErrorByName(
					"mail_actions][$action",
					$this->t('El valor debe tener el formato mail_text_N. Ejemplo: mail_text_1.')
				);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state): void
	{
		$mail_actions = $form_state->getValue('mail_actions') ?? [];

		$clean_actions = [];

		foreach ($mail_actions as $action => $mail_config_key) {
			$clean_actions[$action] = trim((string) $mail_config_key);
		}

		$this->configFactory()
			->getEditable('dermau_core.mail_settings')
			->set('mail_actions', $clean_actions)
			->save();

		parent::submitForm($form, $form_state);
	}
}
