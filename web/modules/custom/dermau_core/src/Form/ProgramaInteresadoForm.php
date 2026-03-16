<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProgramaInteresadoForm extends FormBase
{

	protected Connection $database;
	protected RouteMatchInterface $routeMatch;
	protected RequestStack $requestStack;

	public function __construct(
		Connection $database,
		RouteMatchInterface $route_match,
		RequestStack $request_stack
	) {
		$this->database = $database;
		$this->routeMatch = $route_match;
		$this->requestStack = $request_stack;
	}

	public static function create(ContainerInterface $container)
	{
		return new static(
			$container->get('database'),
			$container->get('current_route_match'),
			$container->get('request_stack')
		);
	}

	public function getFormId(): string
	{
		return 'dermau_programa_interesado_form';
	}

	public function buildForm(array $form, FormStateInterface $form_state): array
	{
		$node = $this->routeMatch->getParameter('node');

		if (!$node instanceof NodeInterface || $node->bundle() !== 'programa') {
			return [
				'#markup' => $this->t('Este formulario solo puede mostrarse dentro de la interna de un programa.'),
			];
		}

		$form['#attributes']['class'][] = 'du-form-register__form';

		$form['programa_nid'] = [
			'#type' => 'hidden',
			'#value' => (int) $node->id(),
		];

		$form['programa_title'] = [
			'#type' => 'hidden',
			'#value' => (string) $node->label(),
		];

		$form['nombre'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Nombre'),
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#maxlength' => 150,
			'#attributes' => [
				'placeholder' => $this->t('Nombre'),
			],
		];

		$form['apellido'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Apellido'),
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#maxlength' => 150,
			'#attributes' => [
				'placeholder' => $this->t('Apellido'),
			],
		];

		$form['telefono_wrapper'] = [
			'#type' => 'container',
			'#attributes' => [
				'class' => ['du-phone-wrapper'],
			],
		];

		$form['telefono_wrapper']['indicativo'] = [
			'#type' => 'select',
			'#title' => $this->t('Indicativo'),
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#options' => $this->getIndicativos(),
			'#default_value' => '+57',
		];

		$form['telefono_wrapper']['telefono'] = [
			'#type' => 'tel',
			'#title' => $this->t('Teléfono'),
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#maxlength' => 30,
			'#attributes' => [
				'placeholder' => $this->t('Teléfono'),
			],
		];

		$form['ciudad'] = [
			'#type' => 'select',
			'#title' => $this->t('Ciudad'),
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#empty_option' => $this->t('Selecciona tu ciudad'),
			'#options' => $this->getCiudades(),
		];

		$form['profesion'] = [
			'#type' => 'select',
			'#title' => $this->t('Profesión'),
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#empty_option' => $this->t('Selecciona tu profesión'),
			'#options' => $this->getProfesiones(),
		];

		$form['mensaje'] = [
			'#type' => 'textarea',
			'#title' => $this->t('Mensaje'),
			'#title_display' => 'invisible',
			'#required' => FALSE,
			'#attributes' => [
				'placeholder' => $this->t('Mensaje'),
			],
		];

		$form['autorizacion'] = [
			'#type' => 'checkbox',
			'#title' => $this->t('Autorizo a eClass a enviarme información vía email'),
			'#required' => TRUE,
		];

		$form['actions'] = [
			'#type' => 'actions',
		];

		$form['actions']['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Contáctame'),
		];

		return $form;
	}

	public function validateForm(array &$form, FormStateInterface $form_state): void
	{
		$current_node = $this->routeMatch->getParameter('node');

		if (!$current_node instanceof NodeInterface || $current_node->bundle() !== 'programa') {
			$form_state->setErrorByName('programa_nid', $this->t('No se pudo determinar el programa actual.'));
			return;
		}

		$programa_nid = (int) $form_state->getValue('programa_nid');
		$programa_title = trim((string) $form_state->getValue('programa_title'));
		$telefono = preg_replace('/\s+/', '', (string) $form_state->getValue('telefono'));

		if ($programa_nid !== (int) $current_node->id()) {
			$form_state->setErrorByName('programa_nid', $this->t('El programa enviado no coincide con el programa actual.'));
		}

		if ($programa_title === '') {
			$form_state->setErrorByName('programa_title', $this->t('No se pudo identificar el título del programa.'));
		}

		if ($telefono === '' || !preg_match('/^[0-9]{7,15}$/', $telefono)) {
			$form_state->setErrorByName('telefono', $this->t('Ingresa un número de teléfono válido.'));
		}
	}

	public function submitForm(array &$form, FormStateInterface $form_state): void
	{
		$request = $this->requestStack->getCurrentRequest();

		$this->database->insert('dermau_programa_interesado')
			->fields([
				'programa_nid' => (int) $form_state->getValue('programa_nid'),
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

	protected function getIndicativos(): array
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

	protected function getCiudades(): array
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

	protected function getProfesiones(): array
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
