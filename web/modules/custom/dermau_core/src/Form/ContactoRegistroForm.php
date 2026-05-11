<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\enterprise_integrations\Service\MandrillService;
use Drupal\enterprise_integrations\Service\HubspotService;

class ContactoRegistroForm extends FormBase
{

  protected $mandrillService;
  protected $hubspotService;

  public function __construct(MandrillService $mandrillService, HubspotService $hubspotService)
  {
    $this->mandrillService = $mandrillService;
    $this->hubspotService = $hubspotService;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('enterprise_integrations.mandrill'),
      $container->get('enterprise_integrations.hubspot')
    );
  }

  public function getFormId()
  {
    return 'dermau_contacto_registro_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#cache']['max-age'] = 0;

    /*
    -----------------------------------------
    Cargar librería del teléfono
    -----------------------------------------
    */

    $form['#attached']['library'][] = 'dermau_core/intl_tel_input';
    $form['#attached']['library'][] = 'dermau_core/registro_exitoso_modal';
    $form['#attributes']['class'][] = 'du-form-register__form';

    /*
    -------------------------------------------------
    Detectar si estamos dentro de un nodo programa
    -------------------------------------------------
    */

    $node = \Drupal::routeMatch()->getParameter('node');
    $current_program = NULL;

    if ($node && $node->bundle() === 'programa') {
      $current_program = $node->id();
    }

    /*
    -------------------------------------------------
    Cargar programas
    -------------------------------------------------
    */

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'programa')
      ->condition('status', 1)
      ->sort('title')
      ->accessCheck(FALSE);

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);

    $programas = [];

    foreach ($nodes as $programa) {
      $programas[$programa->id()] = $programa->getTitle();
    }

    /*
    -------------------------------------------------
    CONTENEDOR PRINCIPAL
    -------------------------------------------------
    */

    $form['group_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['du-form-register__form-group-container']
      ]
    ];

    /*
    -------------------------------------------------
    GRUPO 1
    -------------------------------------------------
    */

    $form['group_container']['group1'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['du-form-register__form-group']
      ]
    ];

    $form['group_container']['group1']['programa'] = [
      '#type' => 'select',
      '#options' => $programas,
      '#default_value' => $current_program,
      '#empty_option' => $this->t('Selecciona tu programa'),
      '#attributes' => [
        'class' => ['du-form-select'],
        'id' => 'du-reg-program'
      ],
      '#title_display' => 'invisible',
      '#required' => TRUE
    ];

    $form['group_container']['group1']['nombre'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'class' => ['du-form-input'],
        'placeholder' => 'Nombre',
        'id' => 'du-reg-name'
      ],
      '#title_display' => 'invisible',
      '#required' => TRUE
    ];

    $form['group_container']['group1']['apellido'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'class' => ['du-form-input'],
        'placeholder' => 'Apellido',
        'id' => 'du-reg-lastname'
      ],
      '#title_display' => 'invisible',
      '#required' => TRUE
    ];

    $form['group_container']['group1']['email'] = [
      '#type' => 'email',
      '#attributes' => [
        'class' => ['du-form-input'],
        'placeholder' => 'Email',
        'id' => 'du-reg-email'
      ],
      '#title_display' => 'invisible',
      '#required' => TRUE
    ];

    /*
    -------------------------------------------------
    GRUPO 2
    -------------------------------------------------
    */

    $form['group_container']['group2'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['du-form-register__form-group']
      ]
    ];

    /*
    PHONE GROUP
    */

    $form['group_container']['group2']['phone_group'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['du-form-phone-group']
      ]
    ];

    /*
    Campo teléfono (intl-tel-input)
    */

    $form['group_container']['group2']['phone_group']['telefono'] = [
      '#type' => 'tel',
      '#attributes' => [
        'placeholder' => 'Teléfono',
        'id' => 'du-reg-phone',
        'class' => ['du-form-input']
      ],
      '#required' => TRUE,
      '#title_display' => 'invisible'
    ];

    /*
    PAÍS DE NACIONALIDAD
    */

    $form['group_container']['group2']['pais_nacionalidad'] = [
      '#type' => 'select',
      '#options' => $this->getPaisesNacionalidad(),
      '#empty_option' => $this->t('Selecciona tu país de nacionalidad'),
      '#empty_value' => '',
      '#attributes' => [
        'class' => ['du-form-select'],
        'id' => 'du-reg-country'
      ],
      '#required' => TRUE,
      '#title_display' => 'invisible'
    ];
    
    /*
    PROFESION
    */

    $form['group_container']['group2']['profesion'] = [
      '#type' => 'select',
      '#options' => $this->getProfesiones(),
      '#empty_option' => $this->t('Selecciona tu profesión'),
      '#attributes' => [
        'class' => ['du-form-select'],
        'id' => 'du-reg-profesion'
      ],
      '#required' => TRUE,
      '#title_display' => 'invisible'
    ];

    /*
    MENSAJE
    */

    $form['mensaje'] = [
      '#type' => 'textarea',
      '#attributes' => [
        'class' => ['du-form-textarea'],
        'placeholder' => 'Mensaje (opcional)',
        'id' => 'du-reg-message'
      ],
      '#title_display' => 'invisible'
    ];

    /*
    CONSENTIMIENTO
    */

    $form['autorizacion'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Autorizo a DermaU a enviarme información vía email'),
      '#attributes' => [
        'class' => ['du-form-checkbox'],
        'id' => 'du-reg-consent'
      ],
      '#wrapper_attributes' => [
        'class' => ['du-form-label-checkbox']
      ],
      '#required' => TRUE
    ];

    /*
    SUBMIT
    */

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Contáctame'),
      '#attributes' => [
        'class' => ['du-btn', 'du-btn--primary']
      ]
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

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $pais_nacionalidad_tid = $form_state->getValue('pais_nacionalidad');
    $profesion_tid = $form_state->getValue('profesion');

    $pais_nacionalidad_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($pais_nacionalidad_tid);
    $profesion_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($profesion_tid);

    $pais_nacionalidad_nombre = $pais_nacionalidad_term ? $pais_nacionalidad_term->getName() : '';
    $pais_nacionalidad_hubspot = $this->mapPaisNacionalidadToHubspotValue($pais_nacionalidad_nombre);
    $profesion_nombre = $profesion_term ? $profesion_term->getName() : '';

    $programa_id = $form_state->getValue('programa');

    /*
  ---------------------------------
  Datos del formulario
  ---------------------------------
  */

    $nombre = $form_state->getValue('nombre');
    $apellido = $form_state->getValue('apellido');
    $correo_real = $form_state->getValue('email');

    /*
  ---------------------------------
  Generar username único
  ---------------------------------
  */

    $timestamp = date('YmdHis');

    $username = strtolower($nombre . '.' . $apellido . '.' . $timestamp);

    $email_sistema = $username . '@registro.local';

    /*
  ---------------------------------
  Crear usuario Drupal
  ---------------------------------
  */

    $user = User::create([
      'name' => $username,
      'mail' => $email_sistema,
      'status' => 0,
    ]);

    $user->addRole('registro');

    /*
  ---------------------------------
  Guardar campos personalizados
  ---------------------------------
  */

    $user->set('field_correo_real', $correo_real);
    $user->set('field_programa', $form_state->getValue('programa'));
    $user->set('field_telefono', $form_state->getValue('telefono'));
    $user->set('field_pais_de_nacionalidad', $form_state->getValue('pais_nacionalidad'));
    $user->set('field_profesion', $form_state->getValue('profesion'));
    $user->set('field_mensaje', $form_state->getValue('mensaje'));

    $user->save();

    // variables para enviaar correo

    $programa = '';
    $node = Node::load($programa_id);

    if ($node) {
      $programa = $node->getTitle();
    }

    $telefono = trim((string) $form_state->getValue('telefono'));
    $profesion = $profesion_nombre;
    $mensaje = trim((string) $form_state->getValue('mensaje'));

    // Envío de correo.
    $mail_config_key = \Drupal::config('dermau_core.mail_settings')
      ->get('mail_actions.contacto_registro') ?? 'mail_text_1';

    $mail_config_key = trim((string) $mail_config_key);

    // Último respaldo técnico.
    if ($mail_config_key === '') {
      $mail_config_key = 'mail_text_1';
    }

    $config_email = $this->mandrillService->getMessageGroupByKey($mail_config_key);

    if (!$config_email) {
      throw new \RuntimeException(sprintf(
        'No existe la configuración de correo %s.',
        $mail_config_key
      ));
    }

    $template_slug = $config_email['mandrill_template_slug'] ?? '';

    if ($template_slug === '') {
      throw new \RuntimeException(sprintf(
        'La configuración %s no tiene slug de plantilla Mandrill.',
        $mail_config_key
      ));
    }

    //enviando correo
    $subject_tokens = [
      'programa' => $programa,
      'nombre_usuario' => trim($nombre . ' ' . $apellido),
      'nombre' => $nombre,
      'apellido' => $apellido,
      'email' => $correo_real,
    ];

    $subject_resolver = \Drupal::service('enterprise_integrations.token_resolver');

    $subject_config = trim((string) ($config_email['subject'] ?? ''));
    if ($subject_config === '') {
      $subject_config = 'Solicitud de información para programa - [programa]';
    }

    $copy_subject_config = trim((string) ($config_email['copy_subject'] ?? ''));
    if ($copy_subject_config === '') {
      $copy_subject_config = 'Notificación solicitud de información programa - [programa]';
    }

    $subject = $subject_resolver->replace($subject_config, $subject_tokens);
    $copy_subject = $subject_resolver->replace($copy_subject_config, $subject_tokens);

    $result = $this->mandrillService->sendTemplate(
      $template_slug,
      [
        'subject' => $subject,
        'to_email' => $correo_real,
        'to_name' => trim($nombre . ' ' . $apellido),
      ],
      [
        [
          'name' => 'FNAME',
          'content' => trim($nombre . ' ' . $apellido),
        ],
        [
          'name' => 'FPROGRAMA',
          'content' => $programa,
        ],
        [
          'name' => 'FEMAIL',
          'content' => $correo_real,
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
            'subject' => $copy_subject,
            'to_email' => $copy_email,
          ],
          [
            [
              'name' => 'FNAME',
              'content' => trim($nombre . ' ' . $apellido),
            ],
            [
              'name' => 'FPROGRAMA',
              'content' => $programa,
            ],
            [
              'name' => 'FEMAIL',
              'content' => $correo_real,
            ],
          ]
        );
      }
    }

    // Crear o actualizar contacto/interés en HubSpot.
    $categoria_programa = '';

    if (
      $node instanceof Node &&
      $node->hasField('field_tipo_de_programa') &&
      !$node->get('field_tipo_de_programa')->isEmpty() &&
      $node->get('field_tipo_de_programa')->entity
    ) {
      $categoria_programa = $node->get('field_tipo_de_programa')->entity->label();
    }

    $programaId = $node instanceof Node
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
        'email' => $correo_real,
        'firstname' => $nombre,
        'lastname' => $apellido,
        'phone' => $telefono,
        'pais_de_nacionalidad' => $pais_nacionalidad_hubspot,
        'interest_property' => $interestProperty,
        'programa_id' => $programaId,
        'programa_nombre' => $programa,
        'unidad_de_negocio' => 'DermaU',
        'tipo_interaccion' => 'contacto',
      ]);
    }

    if (!$hubspotResult) {
      \Drupal::logger('dermau_core')->error(
        'Error enviando contacto/interés a HubSpot desde ContactoRegistroForm para el correo: @email',
        [
          '@email' => $correo_real,
        ]
      );

      $this->messenger()->addWarning('Ocurrió un error enviando la información a HubSpot.');
    }

    /*
  ---------------------------------
  Obtener PDF del programa
  ---------------------------------
  */

    $node_pdf = Node::load($programa_id);
    $pdf_url = '';

    if ($node_pdf && $node_pdf->hasField('field_pdf_registro')) {

      $file = $node_pdf->get('field_pdf_registro')->entity;

      if ($file) {

        $pdf_url = \Drupal::service('file_url_generator')
          ->generateAbsoluteString($file->getFileUri());
      }
    }

    /*
  ---------------------------------
  Redirección descarga
  ---------------------------------
  */

    $request = \Drupal::request();

    $request->getSession()->set('registro_exitoso', TRUE);
    $current_path = \Drupal::service('path.current')->getPath();

    $form_state->setRedirectUrl(
      Url::fromUserInput($current_path)
    );

    //   if ($pdf_url) {

    //     $form_state->setRedirect(
    //   'dermau_core.descargar',
    //   ['node' => $programa_id]
    // );

    //   }

  }

  protected function getProfesiones()
  {
    $options = [];

    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('profesiones', 0, NULL, TRUE);

    usort($terms, function ($a, $b) {
      return strcmp($a->getName(), $b->getName());
    });

    foreach ($terms as $term) {
      $options[$term->id()] = $term->getName();
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
