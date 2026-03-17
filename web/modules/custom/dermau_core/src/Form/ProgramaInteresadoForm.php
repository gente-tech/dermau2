<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

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
		/*
		-----------------------------------------
		Cargar librería del teléfono
		-----------------------------------------
		*/
		$form['#attached']['library'][] = 'dermau_core/intl_tel_input';
		$form['#attached']['library'][] = 'dermau_core/registro_exitoso_modal';

		/*
		-------------------------------------------------
		Clases del FORM real
		-------------------------------------------------
		*/
		$form['#attributes']['class'][] = 'du-form-register__form';
		$form['#attributes']['novalidate'] = 'novalidate';

		/*
		-------------------------------------------------
		Detectar si estamos dentro de un nodo programa
		-------------------------------------------------
		*/
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

		/*
		-------------------------------------------------
		Hidden del programa actual
		-------------------------------------------------
		*/
		$form['programa'] = [
			'#type' => 'hidden',
			'#value' => $current_program,
		];

		$form['programa_title'] = [
			'#type' => 'hidden',
			'#value' => $current_program_title,
		];

		/*
		-------------------------------------------------
		Contenedor principal de grupos
		-------------------------------------------------
		*/
		$form['group_container'] = [
			'#type' => 'container',
			'#attributes' => [
				'class' => ['du-form-register__form-group-container'],
			],
		];

		/*
		-------------------------------------------------
		GRUPO 1
		-------------------------------------------------
		*/
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

		/*
		-------------------------------------------------
		GRUPO 2
		-------------------------------------------------
		*/
		$form['group_container']['group2'] = [
			'#type' => 'container',
			'#attributes' => [
				'class' => ['du-form-register__form-group'],
			],
		];

		/*
		-------------------------------------------------
		PHONE GROUP
		Se conserva el campo real "indicativo" para no romper submit/validación.
		Se agregan clases y estructura para que el JS/CSS del front lo pueda estilizar.
		-------------------------------------------------
		*/
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
			'#attributes' => [
				'class' => ['selected'],
				'data-value' => '',
			],
			'#value' => '',
		];

		$form['group_container']['group2']['phone_group']['country_ui']['options'] = [
			'#type' => 'html_tag',
			'#tag' => 'div',
			'#attributes' => [
				'class' => ['options'],
			],
			'#value' => '',
		];

		$form['group_container']['group2']['phone_group']['indicativo'] = [
			'#type' => 'select',
			'#title' => $this->t('Indicativo'),
			'#title_display' => 'invisible',
			'#options' => $this->getIndicativos(),
			'#default_value' => '+57',
			'#required' => TRUE,
			'#attributes' => [
				'class' => [
					'du-form-select',
					'du-form-select--indicative',
					'js-country-native-select',
				],
				'id' => 'du-reg-indicative',
				'data-role' => 'country-native-select',
			],
		];

		$form['group_container']['group2']['phone_group']['telefono'] = [
			'#type' => 'tel',
			'#title' => $this->t('Teléfono'),
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#maxlength' => 30,
			'#default_value' => '',
			'#attributes' => [
				'class' => ['du-form-input'],
				'placeholder' => 'Teléfono',
				'id' => 'du-reg-phone',
				'inputmode' => 'numeric',
				'autocomplete' => 'tel',
			],
		];

		/*
		-------------------------------------------------
		CIUDAD
		-------------------------------------------------
		*/
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

		/*
		-------------------------------------------------
		PROFESIÓN
		-------------------------------------------------
		*/
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

		/*
		-------------------------------------------------
		MENSAJE
		-------------------------------------------------
		*/
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

		/*
		-------------------------------------------------
		CONSENTIMIENTO
		Se mantienen clases para que tome estilos actuales.
		-------------------------------------------------
		*/
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

		/*
		-------------------------------------------------
		SUBMIT
		-------------------------------------------------
		*/
		$form['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Contáctame'),
			'#attributes' => [
				'class' => ['du-btn', 'du-btn--primary'],
			],
		];

		/*
		-------------------------------------------------
		MODAL REGISTRO EXITOSO
		No se toca para no romper el flujo actual.
		-------------------------------------------------
		*/
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
		$telefono = preg_replace('/\s+/', '', (string) $form_state->getValue('telefono'));

		if ($programa_id !== (int) $node->id()) {
			$form_state->setErrorByName('programa', $this->t('El programa enviado no coincide con el programa actual.'));
		}

		if ($programa_title === '') {
			$form_state->setErrorByName('programa_title', $this->t('No se pudo identificar el título del programa.'));
		}

		if ($telefono === '' || !preg_match('/^[0-9]{7,15}$/', $telefono)) {
			$form_state->setErrorByName('telefono', $this->t('Ingresa un número de teléfono válido.'));
		}
	}

	public function submitForm(array &$form, FormStateInterface $form_state)
	{
		$request = \Drupal::request();

		$this->database->insert('dermau_programa_interesado')
			->fields([
				'programa_nid' => (int) $form_state->getValue('programa'),
				'programa_title' => trim((string) $form_state->getValue('programa_title')),
				'nombre' => trim((string) $form_state->getValue('nombre')),
				'apellido' => trim((string) $form_state->getValue('apellido')),
				'indicativo' => trim((string) $form_state->getValue('indicativo')),
				'telefono' => trim((string) $form_state->getValue('telefono')),
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

	protected function getIndicativos()
	{
		return [
			'+57' => '+57',
			'+1' => '+1',
			'+34' => '+34',
			'+51' => '+51',
			'+52' => '+52',
			'+54' => '+54',
			'+56' => '+56',
			'+58' => '+58',
		];
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
