<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
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

		/*
    -----------------------------------------
    Cargar librería del teléfono
    -----------------------------------------
    */
		$form['#attached']['library'][] = 'dermau_core/intl_tel_input';

		$form['#attributes']['class'][] = 'du-form-register__form';

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
    CONTENEDORES HIDDEN DEL PROGRAMA ACTUAL
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
    CONTENEDOR PRINCIPAL
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
			'#attributes' => [
				'class' => ['du-form-input'],
				'placeholder' => 'Nombre',
				'id' => 'du-reg-name',
			],
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#maxlength' => 150,
		];

		$form['group_container']['group1']['apellido'] = [
			'#type' => 'textfield',
			'#attributes' => [
				'class' => ['du-form-input'],
				'placeholder' => 'Apellido',
				'id' => 'du-reg-lastname',
			],
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#maxlength' => 150,
		];

		/*
    Si luego quieres email, aquí va en este grupo.
    Por ahora no lo agrego porque dijiste guiarse por la imagen.
    */

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
    PHONE GROUP
    */
		$form['group_container']['group2']['phone_group'] = [
			'#type' => 'container',
			'#attributes' => [
				'class' => ['du-form-phone-group'],
			],
		];

		/*
    INDICATIVO
    */
		$form['group_container']['group2']['phone_group']['indicativo'] = [
			'#type' => 'select',
			'#options' => $this->getIndicativos(),
			'#default_value' => '+57',
			'#attributes' => [
				'class' => ['du-form-select'],
				'id' => 'du-reg-indicative',
			],
			'#required' => TRUE,
			'#title_display' => 'invisible',
		];

		/*
    TELÉFONO
    */
		$form['group_container']['group2']['phone_group']['telefono'] = [
			'#type' => 'tel',
			'#attributes' => [
				'placeholder' => 'Teléfono',
				'id' => 'du-reg-phone',
				'class' => ['du-form-input'],
			],
			'#required' => TRUE,
			'#title_display' => 'invisible',
			'#maxlength' => 30,
		];

		/*
    CIUDAD
    */
		$form['group_container']['group2']['ciudad'] = [
			'#type' => 'select',
			'#options' => $this->getCiudades(),
			'#empty_option' => $this->t('Seleccione tu ciudad'),
			'#attributes' => [
				'class' => ['du-form-select'],
				'id' => 'du-reg-city',
			],
			'#required' => TRUE,
			'#title_display' => 'invisible',
		];

		/*
    PROFESION
    */
		$form['group_container']['group2']['profesion'] = [
			'#type' => 'select',
			'#options' => $this->getProfesiones(),
			'#empty_option' => $this->t('Seleccione tu profesión'),
			'#attributes' => [
				'class' => ['du-form-select'],
				'id' => 'du-reg-profesion',
			],
			'#required' => TRUE,
			'#title_display' => 'invisible',
		];

		/*
    MENSAJE
    */
		$form['mensaje'] = [
			'#type' => 'textarea',
			'#attributes' => [
				'class' => ['du-form-textarea'],
				'placeholder' => 'Mensaje (opcional)',
				'id' => 'du-reg-message',
			],
			'#title_display' => 'invisible',
		];

		/*
    CONSENTIMIENTO
    */
		$form['autorizacion'] = [
			'#type' => 'checkbox',
			'#title' => $this->t('Autorizo a eClass a enviarme información vía email'),
			'#attributes' => [
				'class' => ['du-form-checkbox'],
				'id' => 'du-reg-consent',
			],
			'#wrapper_attributes' => [
				'class' => ['du-form-label-checkbox'],
			],
			'#required' => TRUE,
		];

		/*
    SUBMIT
    */
		$form['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Contáctame'),
			'#attributes' => [
				'class' => ['du-btn', 'du-btn--primary'],
			],
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

		$this->messenger()->addStatus($this->t('Tu información fue registrada correctamente.'));
		$form_state->setRedirect('<current>');
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
