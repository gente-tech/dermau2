<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProgramaInteresadoForm extends FormBase
{
	protected $database;

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
		return 'dermau_programa_interesado_form';
	}

	public function buildForm(array $form, FormStateInterface $form_state)
	{
		$form['#attached']['library'][] = 'dermau_core/intl_tel_input';
		$form['#attached']['library'][] = 'dermau_core/registro_exitoso_modal';

		$form['#attributes']['class'][] = 'du-form-register__form';
		$form['#attributes']['novalidate'] = 'novalidate';

		$node = \Drupal::routeMatch()->getParameter('node');
		$current_program = NULL;
		$current_program_title = '';

		if ($node instanceof NodeInterface && $node->bundle() === 'programa') {
			$current_program = (int) $node->id();
			$current_program_title = $node->getTitle();
		}

		if (!$current_program) {
			return [
				'#markup' => $this->t('Este formulario solo puede usarse dentro de la interna de un programa.'),
			];
		}

		$form['programa'] = [
			'#type' => 'hidden',
			'#value' => $current_program,
		];

		$form['programa_title'] = [
			'#type' => 'hidden',
			'#value' => $current_program_title,
		];

		$form['group_container'] = [
			'#type' => 'container',
			'#attributes' => [
				'class' => ['du-form-register__form-group-container'],
			],
		];

		$form['group_container']['group1'] = [
			'#type' => 'container',
			'#attributes' => [
				'class' => ['du-form-register__form-group'],
			],
		];

		$form['group_container']['group1']['nombre'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Nombre'),
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#maxlength' => 150,
			'#attributes' => [
				'class' => ['du-form-input'],
				'placeholder' => 'Nombre',
				'id' => 'du-reg-name',
			],
		];

		$form['group_container']['group1']['apellido'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Apellido'),
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#maxlength' => 150,
			'#attributes' => [
				'class' => ['du-form-input'],
				'placeholder' => 'Apellido',
				'id' => 'du-reg-lastname',
			],
		];

		$form['group_container']['group2'] = [
			'#type' => 'container',
			'#attributes' => [
				'class' => ['du-form-register__form-group'],
			],
		];

		$form['group_container']['group2']['phone_group'] = [
			'#type' => 'container',
			'#attributes' => [
				'class' => ['du-form-phone-group'],
			],
		];

		$form['group_container']['group2']['phone_group']['country_ui'] = [
			'#type' => 'container',
			'#attributes' => [
				'class' => ['select-country'],
				'id' => 'select-country',
			],
		];

		$form['group_container']['group2']['phone_group']['country_ui']['selected'] = [
			'#type' => 'html_tag',
			'#tag' => 'div',
			'#value' => '',
			'#attributes' => [
				'class' => ['selected'],
				'data-value' => 'co',
			],
		];

		$form['group_container']['group2']['phone_group']['country_ui']['options'] = [
			'#type' => 'html_tag',
			'#tag' => 'div',
			'#value' => '',
			'#attributes' => [
				'class' => ['options'],
			],
		];

		$form['group_container']['group2']['phone_group']['indicativo'] = [
			'#type' => 'hidden',
			'#value' => '+57',
			'#attributes' => [
				'id' => 'du-reg-indicative',
			],
		];

		$form['group_container']['group2']['phone_group']['indicativo_display'] = [
			'#type' => 'html_tag',
			'#tag' => 'span',
			'#value' => '+57',
			'#attributes' => [
				'class' => ['du-form-phone-prefix'],
				'id' => 'du-reg-indicative-display',
			],
		];

		$form['group_container']['group2']['phone_group']['telefono'] = [
			'#type' => 'tel',
			'#title' => $this->t('Teléfono'),
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#maxlength' => 30,
			'#attributes' => [
				'class' => ['du-form-input', 'du-form-input--phone'],
				'placeholder' => 'Teléfono',
				'id' => 'du-reg-phone',
				'inputmode' => 'numeric',
				'autocomplete' => 'tel-national',
			],
		];

		$form['group_container']['group2']['ciudad'] = [
			'#type' => 'select',
			'#title' => $this->t('Ciudad'),
			'#title_display' => 'invisible',
			'#options' => $this->getCiudades(),
			'#empty_option' => $this->t('Seleccione tu ciudad'),
			'#empty_value' => '',
			'#required' => TRUE,
			'#attributes' => [
				'class' => ['du-form-select'],
				'id' => 'du-reg-city',
			],
		];

		$form['group_container']['group2']['profesion'] = [
			'#type' => 'select',
			'#title' => $this->t('Profesión'),
			'#title_display' => 'invisible',
			'#options' => $this->getProfesiones(),
			'#empty_option' => $this->t('Seleccione tu profesión'),
			'#empty_value' => '',
			'#required' => TRUE,
			'#attributes' => [
				'class' => ['du-form-select'],
				'id' => 'du-reg-profesion',
			],
		];

		$form['mensaje'] = [
			'#type' => 'textarea',
			'#title' => $this->t('Mensaje'),
			'#title_display' => 'invisible',
			'#attributes' => [
				'class' => ['du-form-textarea'],
				'placeholder' => 'Mensaje (opcional)',
				'id' => 'du-reg-message',
				'rows' => 5,
			],
		];

		$form['autorizacion'] = [
			'#type' => 'checkbox',
			'#title' => $this->t('Autorizo a eClass a enviarme información vía email'),
			'#required' => TRUE,
			'#attributes' => [
				'class' => ['du-form-checkbox'],
				'id' => 'du-reg-consent',
			],
			'#wrapper_attributes' => [
				'class' => ['du-form-label-checkbox'],
			],
		];

		$form['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Contáctame'),
			'#attributes' => [
				'class' => ['du-btn', 'du-btn--primary'],
			],
		];

		$form['modal_registro_exitoso'] = [
			'#theme' => 'dermau_modal_registro_exitoso',
			'#weight' => 999,
		];

		return $form;
	}

	public function validateForm(array &$form, FormStateInterface $form_state)
	{
		$node = \Drupal::routeMatch()->getParameter('node');

		if (!$node instanceof NodeInterface || $node->bundle() !== 'programa') {
			$form_state->setErrorByName('programa', $this->t('No se pudo identificar el programa actual.'));
			return;
		}

		$programa_id = (int) $form_state->getValue('programa');
		$programa_title = trim((string) $form_state->getValue('programa_title'));
		$indicativo = trim((string) $form_state->getValue('indicativo'));
		$telefono = preg_replace('/\D+/', '', (string) $form_state->getValue('telefono'));

		if ($programa_id !== (int) $node->id()) {
			$form_state->setErrorByName('programa', $this->t('El programa enviado no coincide con el programa actual.'));
		}

		if ($programa_title === '') {
			$form_state->setErrorByName('programa_title', $this->t('No se pudo identificar el título del programa.'));
		}

		if ($indicativo === '') {
			$form_state->setErrorByName('indicativo', $this->t('No se pudo determinar el indicativo del país.'));
		}

		if ($telefono === '' || !preg_match('/^[0-9]{7,15}$/', $telefono)) {
			$form_state->setErrorByName('telefono', $this->t('Ingresa un número de teléfono válido.'));
		}
	}

	public function submitForm(array &$form, FormStateInterface $form_state)
	{
		$request = \Drupal::request();

		$indicativo = trim((string) $form_state->getValue('indicativo'));
		$telefono = trim((string) $form_state->getValue('telefono'));
		$telefono = preg_replace('/\D+/', '', $telefono);

		$this->database->insert('dermau_programa_interesado')
			->fields([
				'programa_nid' => (int) $form_state->getValue('programa'),
				'programa_title' => trim((string) $form_state->getValue('programa_title')),
				'nombre' => trim((string) $form_state->getValue('nombre')),
				'apellido' => trim((string) $form_state->getValue('apellido')),
				'indicativo' => $indicativo,
				'telefono' => $telefono,
				'ciudad' => trim((string) $form_state->getValue('ciudad')),
				'profesion' => trim((string) $form_state->getValue('profesion')),
				'mensaje' => trim((string) $form_state->getValue('mensaje')),
				'autorizacion' => (int) $form_state->getValue('autorizacion'),
				'ip' => $request->getClientIp(),
				'user_agent' => mb_substr((string) $request->headers->get('User-Agent'), 0, 512),
				'created' => \Drupal::time()->getRequestTime(),
			])
			->execute();

		$current_path = \Drupal::service('path.current')->getPath();
		$current_query = $request->query->all();
		$current_query['registro_exitoso'] = 1;

		$form_state->setRedirectUrl(
			Url::fromUserInput($current_path, [
				'query' => $current_query,
			])
		);
	}

	protected function getCiudades()
	{
		return [
			'Bogotá' => 'Bogotá',
			'Medellín' => 'Medellín',
			'Cali' => 'Cali',
			'Barranquilla' => 'Barranquilla',
			'Cartagena' => 'Cartagena',
			'Bucaramanga' => 'Bucaramanga',
			'Pereira' => 'Pereira',
			'Manizales' => 'Manizales',
			'Santa Marta' => 'Santa Marta',
			'Cúcuta' => 'Cúcuta',
			'Otra' => 'Otra',
		];
	}

	protected function getProfesiones()
	{
		return [
			'Dermatólogo(a)' => 'Dermatólogo(a)',
			'Médico(a) general' => 'Médico(a) general',
			'Residente' => 'Residente',
			'Enfermero(a)' => 'Enfermero(a)',
			'Estudiante' => 'Estudiante',
			'Otro profesional de la salud' => 'Otro profesional de la salud',
			'Otra' => 'Otra',
		];
	}
}
