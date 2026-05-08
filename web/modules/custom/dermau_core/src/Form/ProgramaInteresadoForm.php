<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\enterprise_integrations\Service\MandrillService;
use Drupal\enterprise_integrations\Service\HubspotService;

class ProgramaInteresadoForm extends FormBase
{
	protected $database;
	protected $mandrillService;
	protected $hubspotService;

	public function __construct(Connection $database, MandrillService $mandrillService, HubspotService $hubspotService)
	{
		$this->database = $database;
		$this->mandrillService = $mandrillService;
		$this->hubspotService = $hubspotService;
	}

	public static function create(ContainerInterface $container)
	{
		return new static(
			$container->get('database'),
			$container->get('enterprise_integrations.mandrill'),
			$container->get('enterprise_integrations.hubspot')
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

		$form['programa'] = [
			'#type' => 'hidden',
			'#value' => $current_program,
		];

		$form['programa_title'] = [
			'#type' => 'hidden',
			'#value' => $current_program_title,
		];

		$form['indicativo'] = [
			'#type' => 'hidden',
			'#default_value' => '+57',
			'#attributes' => [
				'id' => 'du-reg-indicative',
			],
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

		$form['group_container']['group1']['email'] = [
			'#type' => 'email',
			'#title' => $this->t('Email'),
			'#title_display' => 'invisible',
			'#required' => TRUE,
			'#maxlength' => 150,
			'#attributes' => [
				'class' => ['du-form-input'],
				'placeholder' => 'Email',
				'id' => 'du-reg-email',
				'autocomplete' => 'email',
			],
		];

		$form['group_container']['group2'] = [
			'#type' => 'container',
			'#attributes' => [
				'class' => ['du-form-register__form-group'],
			],
		];

		$form['group_container']['group2']['telefono'] = [
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
			'#prefix' => '<div class="du-form-phone-group">',
			'#suffix' => '</div>',
		];

		$form['group_container']['group2']['pais_nacionalidad'] = [
			'#type' => 'select',
			'#title' => $this->t('País de nacionalidad'),
			'#title_display' => 'invisible',
			'#options' => $this->getPaisesNacionalidad(),
			'#empty_option' => $this->t('Selecciona tu país de nacionalidad'),
			'#empty_value' => '',
			'#required' => TRUE,
			'#attributes' => [
				'class' => ['du-form-select'],
				'id' => 'du-reg-country',
			],
		];

		$form['group_container']['group2']['ciudad'] = [
			'#type' => 'select',
			'#title' => $this->t('Ciudad'),
			'#title_display' => 'invisible',
			'#options' => $this->getCiudades(),
			'#empty_option' => $this->t('Selecciona tu ciudad'),
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
			'#empty_option' => $this->t('Selecciona tu profesión'),
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
			'#title' => $this->t('Autorizo a DermaU a enviarme información vía email'),
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
			'#value' => $this->t('Pre- Inscribirme'),
			'#attributes' => [
				'class' => ['du-btn', 'full', 'du-btn--primary'],
			],
		];

		$session = \Drupal::request()->getSession();
		$mostrar_modal = (bool) $session->get('registro_exitoso', FALSE);

		if ($mostrar_modal) {
			$session->remove('registro_exitoso');
		}

		$form['modal_registro_exitoso'] = [
			'#theme' => 'dermau_modal_registro_exitoso',
			'#mostrar_modal' => $mostrar_modal,
			'#weight' => 999,
			'#cache' => [
				'max-age' => 0,
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
		$indicativo = trim((string) $form_state->getValue('indicativo'));
		$telefono = preg_replace('/\D+/', '', (string) $form_state->getValue('telefono'));
		$email = trim((string) $form_state->getValue('email'));

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

		if ($email === '' || !\Drupal::service('email.validator')->isValid($email)) {
			$form_state->setErrorByName('email', $this->t('Ingresa un correo electrónico válido.'));
		}
	}

	public function submitForm(array &$form, FormStateInterface $form_state)
	{
		$request = \Drupal::request();

		$ciudad_tid = $form_state->getValue('ciudad');
		$pais_nacionalidad_tid = $form_state->getValue('pais_nacionalidad');
		$profesion_tid = $form_state->getValue('profesion');

		$ciudad_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($ciudad_tid);
		$pais_nacionalidad_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($pais_nacionalidad_tid);
		$profesion_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($profesion_tid);

		$ciudad_nombre = $ciudad_term ? $ciudad_term->getName() : '';
		$pais_nacionalidad_nombre = $pais_nacionalidad_term ? $pais_nacionalidad_term->getName() : '';
		$pais_nacionalidad_hubspot = $this->mapPaisNacionalidadToHubspotValue($pais_nacionalidad_nombre);
		$profesion_nombre = $profesion_term ? $profesion_term->getName() : '';

		$indicativo = trim((string) $form_state->getValue('indicativo'));
		$telefono = preg_replace('/\D+/', '', trim((string) $form_state->getValue('telefono')));

		$this->database->insert('dermau_programa_interesado')
			->fields([
				'programa_nid' => (int) $form_state->getValue('programa'),
				'programa_title' => trim((string) $form_state->getValue('programa_title')),
				'nombre' => trim((string) $form_state->getValue('nombre')),
				'apellido' => trim((string) $form_state->getValue('apellido')),
				'email' => trim((string) $form_state->getValue('email')),
				'indicativo' => $indicativo,
				'telefono' => $telefono,
				'ciudad' => $ciudad_nombre,
				'pais_nacionalidad' => $pais_nacionalidad_nombre,
				'profesion' => $profesion_nombre,
				'mensaje' => trim((string) $form_state->getValue('mensaje')),
				'autorizacion' => (int) $form_state->getValue('autorizacion'),
				'ip' => $request->getClientIp(),
				'user_agent' => mb_substr((string) $request->headers->get('User-Agent'), 0, 512),
				'created' => \Drupal::time()->getRequestTime(),
			])
			->execute();

		// Variables para envío de correo
		$nombre = trim((string) $form_state->getValue('nombre'));
		$apellido = trim((string) $form_state->getValue('apellido'));
		$email = trim((string) $form_state->getValue('email'));
		$telefono = $indicativo . ' ' . $telefono;
		$programa = trim((string) $form_state->getValue('programa_title'));
		$mensaje = trim((string) $form_state->getValue('mensaje'));

		// Envío de correo
		$config_email = $this->mandrillService->getMessageGroupByKey('mail_text_1');

		if (!$config_email) {
			throw new \RuntimeException('No existe la configuración de correo mail_text_1.');
		}

		$template_slug = $config_email['mandrill_template_slug'] ?? '';

		if ($template_slug === '') {
			throw new \RuntimeException('La configuración mail_text_1 no tiene slug de plantilla Mandrill.');
		}

		$result = $this->mandrillService->sendTemplate(
			$template_slug,
			[
				'subject' => 'Preinscripción programa - ' . $programa,
				'to_email' => $email,
				'to_name' => $nombre . ' ' . $apellido,
			],
			[
				[
					'name' => 'FNAME',
					'content' => $nombre . ' ' . $apellido,
				],
				[
					'name' => 'FPROGRAMA',
					'content' => $programa,
				],
				[
					'name' => 'FEMAIL',
					'content' => $email,
				],
			]
		);

		// Envío copia oculta de correo, en caso exista.
		if (
			!empty($config_email['send_copy']) &&
			!empty($config_email['copy_template_slug']) &&
			!empty($config_email['copy_emails']) &&
			is_array($config_email['copy_emails'])
		) {
			foreach ($config_email['copy_emails'] as $copy_email) {
				$copy_email = trim((string) $copy_email);

				if ($copy_email === '') {
					continue;
				}

				$this->mandrillService->sendTemplate(
					$config_email['copy_template_slug'],
					[
						'subject' => 'Notificación preinscripción programa - ' . $programa,
						'to_email' => $copy_email,
					],
					[
						[
							'name' => 'FNAME',
							'content' => $nombre . ' ' . $apellido,
						],
						[
							'name' => 'FPROGRAMA',
							'content' => $programa,
						],
						[
							'name' => 'FEMAIL',
							'content' => $email,
						],
					]
				);
			}
		}

		// obtener categoria del programa
		$node = \Drupal::routeMatch()->getParameter('node');

		$categoria_programa = '';

		if (
			$node instanceof NodeInterface &&
			$node->hasField('field_tipo_de_programa') &&
			!$node->get('field_tipo_de_programa')->isEmpty() &&
			$node->get('field_tipo_de_programa')->entity
		) {
			$categoria_programa = $node->get('field_tipo_de_programa')->entity->label();
		}

		// Crear usuario en HubSpot.
		$programaId = $node instanceof NodeInterface
			? (string) $node->id()
			: '';

		$interestProperty = '';

		switch (mb_strtolower(trim($categoria_programa))) {

			case 'webinar':
				$interestProperty = 'interesados_webinar';
				break;

			case 'curso':
				$interestProperty = 'interesados_cursos';
				break;

			case 'diplomado':
				$interestProperty = 'interesados_diplomados';
				break;

			case 'programas especiales':
				$interestProperty = 'interesados_programas_especiales';
				break;
		}

		$hubspotResult = NULL;

		if ($interestProperty) {

			$hubspotResult = $this->hubspotService->createOrUpdateContactWithInterest([
				'email' => $email,
				'firstname' => $nombre,
				'lastname' => $apellido,
				'phone' => $telefono,
				'pais_de_nacionalidad' => $pais_nacionalidad_hubspot,
				'interest_property' => $interestProperty,
				'programa_id' => $programaId,
				'programa_nombre' => $programa,
				'unidad_de_negocio' => 'DermaU',
				'tipo_interaccion' => 'preinscripcion_programa',
			]);
		}

		if (!$hubspotResult) {

			$this->loggerFactory->get('dermau_core')->error(
				'Error enviando contacto/interés a HubSpot para el correo: @email',
				[
					'@email' => $email,
				]
			);

			$this->messenger()->addWarning('Ocurrió un error enviando la información a HubSpot.');
		}

		$request->getSession()->set('registro_exitoso', TRUE);
		$current_path = \Drupal::service('path.current')->getPath();

		// Redireccionar
		$form_state->setRedirectUrl(
			Url::fromUserInput($current_path)
		);
	}

	protected function getCiudades()
	{
		$options = [];

		$terms = \Drupal::entityTypeManager()
			->getStorage('taxonomy_term')
			->loadTree('ciudades', 0, NULL, TRUE);

		// Ordenar alfabéticamente por nombre
		usort($terms, function ($a, $b) {
			return strcmp($a->getName(), $b->getName());
		});

		foreach ($terms as $term) {
			$options[$term->id()] = $term->getName();
		}

		return $options;
	}

	protected function getProfesiones()
	{
		$options = [];

		$terms = \Drupal::entityTypeManager()
			->getStorage('taxonomy_term')
			->loadTree('profesiones');

		foreach ($terms as $term) {
			$options[$term->tid] = $term->name;
		}

		return $options;
	}

	protected function getPaisesNacionalidad()
	{
		$options = [];

		$terms = \Drupal::entityTypeManager()
			->getStorage('taxonomy_term')
			->loadTree('pais_de_nacionalidad', 0, NULL, TRUE);

		usort($terms, function ($a, $b) {
			return strcmp($a->getName(), $b->getName());
		});

		foreach ($terms as $term) {
			$options[$term->id()] = $term->getName();
		}

		return $options;
	}

	protected function mapPaisNacionalidadToHubspotValue(string $pais): string
	{
		$map = [
			'Colombia' => 'colombia',
			'Perú' => 'peru',
			'Ecuador' => 'ecuador',
			'Panamá' => 'panama',
			'México' => 'mexico',
		];

		return $map[$pais] ?? '';
	}
}
