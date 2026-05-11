<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\enterprise_integrations\Service\HubspotService;
use Drupal\enterprise_integrations\Service\MandrillService;
use Drupal\Component\Utility\Html;
use Drupal\node\NodeInterface;

class DescargarProgramaForm extends FormBase
{

  protected $hubspotService;
  protected $mandrillService;

  public function getFormId()
  {
    return 'descargar_programa_form';
  }

  public function __construct(HubspotService $hubspotService, MandrillService $mandrillService)
  {
    $this->hubspotService = $hubspotService;
    $this->mandrillService = $mandrillService;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('enterprise_integrations.hubspot'),
      $container->get('enterprise_integrations.mandrill')
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    // =========================
    // 🔥 OBTENER ALIAS
    // =========================
    $request = \Drupal::request();
    $alias = $request->query->get('programa');
    $form['#cache']['contexts'][] = 'url.query_args:programa';

    if (!$alias) {
      return ['#markup' => 'Programa no válido'];
    }

    $alias = ltrim(urldecode($alias), '/');

    // =========================
    // 🔥 FIX REAL: usar repository (NO manager)
    // =========================
    $internal_path = \Drupal::service('path_alias.manager')
      ->getPathByAlias('/' . $alias);

    if (!preg_match('/^\/node\/(\d+)$/', $internal_path, $matches)) {
      \Drupal::logger('dermau_core')->error('Alias no resuelto: @alias => @path', [
        '@alias' => $alias,
        '@path' => $internal_path,
      ]);
      return ['#markup' => 'Programa no válido'];
    }

    $nid = $matches[1];
    $node = Node::load($nid);

    if (!$node || $node->bundle() !== 'programa') {
      return ['#markup' => 'Programa no válido'];
    }

    // Guardar NID
    $form_state->set('programa_nid', $nid);

    // =========================
    // BACKGROUND
    // =========================
    $image_url = '';

    if (!$node->get('field_imagen_programa')->isEmpty()) {
      $file = $node->get('field_imagen_programa')->first()->entity;
      if ($file) {
        $image_url = \Drupal::service('file_url_generator')
          ->generateAbsoluteString($file->getFileUri());
      }
    }

    if (empty($image_url)) {
      $image_url = '/themes/custom/tu_tema/images/default-programa.jpg';
    }

    $form['#attached']['html_head'][] = [
      [
        '#tag' => 'style',
        '#value' => '.programa-background { background-image: url("' . $image_url . '"); }',
      ],
      'programa-background-style'
    ];

    $form['#prefix'] = '
      <div class="programa-background">
        <div class="programa-overlay">
          <div class="descargar-programa-wrapper">
    ';

    $form['#suffix'] = '
          </div>
        </div>
      </div>
    ';

    $titulo_programa = Html::escape($node->getTitle());

    // HEADER
    $form['intro'] = [
      '#markup' => '
        <div class="form-header">
          <h2>Descargar programa</h2>
          <p class="programa-titulo">' . $titulo_programa . '</p>
          <div class="form-divider"></div>
          <p class="form-subtitle">Un ejecutivo te contactará para ayudarte con tu proceso de inscripción.</p>
        </div>
      ',
    ];

    // CAMPOS
    $form['nombre'] = [
      '#type' => 'textfield',
      '#title' => 'Nombre(s)',
      '#required' => TRUE,
    ];

    $form['apellido'] = [
      '#type' => 'textfield',
      '#title' => 'Apellido(s)',
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => 'Correo electrónico',
      '#required' => TRUE,
    ];

    $form['telefono'] = [
      '#type' => 'textfield',
      '#title' => 'Teléfono',
      '#required' => TRUE,
    ];

    // TAXONOMÍA - País de nacionalidad.
    $form['pais_nacionalidad'] = [
      '#type' => 'select',
      '#title' => 'País de nacionalidad',
      '#options' => $this->getPaisesNacionalidad(),
      '#required' => TRUE,
      '#empty_option' => 'Selecciona tu país de nacionalidad',
      '#empty_value' => '',
    ];

    // TAXONOMÍA - Profesión.
    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('profesiones');

    $options = ['' => 'Selecciona tu profesión'];

    foreach ($terms as $term) {
      $options[$term->tid] = $term->name;
    }

    $form['profesion'] = [
      '#type' => 'select',
      '#title' => 'Profesión',
      '#options' => $options,
      '#required' => TRUE,
      '#empty_option' => 'Selecciona tu profesión',
      '#empty_value' => '',
    ];

    $form['privacidad'] = [
      '#type' => 'checkbox',
      '#title' => 'He leído y acepto el Aviso de privacidad',
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Solicitar información',
      '#attributes' => ['class' => ['btn-descargar']],
    ];

    // =========================
    // MODAL
    // =========================
    $download_url = $form_state->get('download_url');

    if ($download_url) {

      $form['modal'] = [
        '#markup' => '
          <div id="modal-descarga" class="custom-modal">
            <div class="modal-content">
              <span class="modal-close" id="modal-close">&times;</span>
              <h3>Agradecemos tu interés en el programa.</h3>
              <p>Nos contactaremos, vía correo electrónico, para brindarte más información sobre el proceso de inscripción.</p>
              <p style="font-size:13px;color:#6b7280;">La descarga del programa comenzará automáticamente...</p>
            </div>
          </div>
        ',
      ];

      $form['#attached']['html_head'][] = [
        [
          '#tag' => 'script',
          '#value' => '
            window.addEventListener("load", function(){

              var modal = document.getElementById("modal-descarga");
              var closeBtn = document.getElementById("modal-close");
              var downloadUrl = "' . $download_url . '";

              function forceDownload(url) {
                var a = document.createElement("a");
                a.href = url;
                a.setAttribute("download", "");
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
              }

              if(modal){
                modal.style.display = "flex";
              }

              setTimeout(function(){
                forceDownload(downloadUrl);
              }, 2000);

              if(closeBtn){
                closeBtn.addEventListener("click", function(){
                  modal.style.display = "none";
                });
              }

              window.addEventListener("click", function(e){
                if(e.target === modal){
                  modal.style.display = "none";
                }
              });

            });
          ',
        ],
        'modal-script'
      ];
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $nid = $form_state->get('programa_nid');

    $data = [
      'nombre' => $form_state->getValue('nombre'),
      'apellido' => $form_state->getValue('apellido'),
      'email' => $form_state->getValue('email'),
      'telefono' => $form_state->getValue('telefono'),
      'pais_nacionalidad' => $form_state->getValue('pais_nacionalidad'),
      'profesion' => $form_state->getValue('profesion'),
    ];

    try {
      $node = Node::create([
        'type' => 'registro_programa',
        'title' => $data['nombre'] . ' ' . $data['apellido'],
        'field_email' => $data['email'],
        'field_programa' => ['target_id' => $nid],
        'field_pais_de_nacionalidad' => ['target_id' => $data['pais_nacionalidad']],
      ]);
      $node->save();
    } catch (\Exception $e) {
      \Drupal::logger('dermau_core')->error($e->getMessage());
    }

    // Cargar programa una sola vez para correo, HubSpot y descarga.
    $programa = Node::load($nid);
    $programa_nombre = $programa instanceof Node ? $programa->getTitle() : '';

    $nombre = trim((string) $data['nombre']);
    $apellido = trim((string) $data['apellido']);
    $email = trim((string) $data['email']);
    $telefono = trim((string) $data['telefono']);

    $pais_nacionalidad_tid = $data['pais_nacionalidad'];
    $pais_nacionalidad_term = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->load($pais_nacionalidad_tid);

    $pais_nacionalidad_nombre = $pais_nacionalidad_term ? $pais_nacionalidad_term->getName() : '';
    $pais_nacionalidad_hubspot = $this->mapPaisNacionalidadToHubspotValue($pais_nacionalidad_nombre);

    // Envío de correo.
    $node = NULL;
    $mail_config_key = '';

    $programa_path = \Drupal::request()->query->get('programa');

    \Drupal::logger('dermau_core')->notice('DEBUG descarga correo - query programa: @programa', [
      '@programa' => $programa_path ?: '[VACIO]',
    ]);

    if (!empty($programa_path)) {
      $programa_path = '/' . ltrim((string) $programa_path, '/');

      $internal_path = \Drupal::service('path_alias.manager')
        ->getPathByAlias($programa_path);

      \Drupal::logger('dermau_core')->notice('DEBUG descarga correo - internal path: @path', [
        '@path' => $internal_path ?: '[VACIO]',
      ]);

      if (preg_match('/^\/node\/(\d+)$/', $internal_path, $matches)) {
        $node = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->load((int) $matches[1]);
      }
    }

    \Drupal::logger('dermau_core')->notice('DEBUG descarga correo - resolved node type: @type', [
      '@type' => is_object($node) ? get_class($node) : gettype($node),
    ]);

    if ($node instanceof NodeInterface) {
      \Drupal::logger('dermau_core')->notice('DEBUG descarga correo - node id: @nid, bundle: @bundle', [
        '@nid' => $node->id(),
        '@bundle' => $node->bundle(),
      ]);

      \Drupal::logger('dermau_core')->notice('DEBUG descarga correo - has field_correo_descarga: @has_field', [
        '@has_field' => $node->hasField('field_correo_descarga') ? 'SI' : 'NO',
      ]);

      if ($node->hasField('field_correo_descarga')) {
        \Drupal::logger('dermau_core')->notice('DEBUG descarga correo - field empty: @empty, raw value: @value', [
          '@empty' => $node->get('field_correo_descarga')->isEmpty() ? 'SI' : 'NO',
          '@value' => $node->get('field_correo_descarga')->value ?? '[NULL]',
        ]);
      }
    }

    if (
      $node instanceof NodeInterface &&
      $node->bundle() === 'programa' &&
      $node->hasField('field_correo_descarga') &&
      !$node->get('field_correo_descarga')->isEmpty()
    ) {
      $mail_config_key = trim((string) $node->get('field_correo_descarga')->value);
    }

    \Drupal::logger('dermau_core')->notice('DEBUG descarga correo - mail_config_key desde programa: @key', [
      '@key' => $mail_config_key !== '' ? $mail_config_key : '[VACIO]',
    ]);

    // Si el programa no tiene correo configurado, usar el valor por defecto.
    if ($mail_config_key === '') {
      $mail_config_key = \Drupal::config('dermau_core.mail_settings')
        ->get('mail_actions.descargar_programa') ?? 'mail_text_1';

      $mail_config_key = trim((string) $mail_config_key);
    }

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

    // enviando correo

    $this->mandrillService->sendTemplate(
      $template_slug,
      [
        'subject' => 'Descarga programa - ' . $programa_nombre,
        'to_email' => $email,
        'to_name' => trim($nombre . ' ' . $apellido),
      ],
      [
        [
          'name' => 'FNAME',
          'content' => trim($nombre . ' ' . $apellido),
        ],
        [
          'name' => 'FPROGRAMA',
          'content' => $programa_nombre,
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
            'subject' => 'Notificación descarga programa - ' . $programa_nombre,
            'to_email' => $copy_email,
          ],
          [
            [
              'name' => 'FNAME',
              'content' => trim($nombre . ' ' . $apellido),
            ],
            [
              'name' => 'FPROGRAMA',
              'content' => $programa_nombre,
            ],
            [
              'name' => 'FEMAIL',
              'content' => $email,
            ],
          ]
        );
      }
    }

    // crear usuario en hubspot

    $categoria_programa = '';

    if (
      $programa instanceof Node &&
      $programa->hasField('field_tipo_de_programa') &&
      !$programa->get('field_tipo_de_programa')->isEmpty() &&
      $programa->get('field_tipo_de_programa')->entity
    ) {
      $categoria_programa = $programa->get('field_tipo_de_programa')->entity->label();
    }

    $interestProperty = '';

    switch (mb_strtolower(trim($categoria_programa))) {
      case 'webinar':
        $interestProperty = 'prospectos_webinar';
        break;

      case 'curso':
        $interestProperty = 'prospectos_cursos';
        break;

      case 'diplomado':
        $interestProperty = 'prospectos_diplomados';
        break;

      case 'programas especiales':
        $interestProperty = 'prospectos_programas_especiales';
        break;
    }

    if ($interestProperty) {
      $this->hubspotService->createOrUpdateContactWithInterest([
        'email' => $email,
        'firstname' => $nombre,
        'lastname' => $apellido,
        'phone' => $telefono,
        'pais_de_nacionalidad' => $pais_nacionalidad_hubspot,
        'interest_property' => $interestProperty,
        'programa_id' => $nid,
        'programa_nombre' => $programa_nombre,
        'unidad_de_negocio' => 'DermaU',
        'tipo_interaccion' => 'descarga_programa',
      ]);
    } else {
      \Drupal::logger('dermau_core')->warning(
        'No se pudo determinar la propiedad HubSpot de prospecto para el programa @nid. Categoría detectada: @categoria',
        [
          '@nid' => $nid,
          '@categoria' => $categoria_programa,
        ]
      );
    }


    if ($programa instanceof Node && $programa->hasField('field_pdf_registro')) {

      $file = $programa->get('field_pdf_registro')->entity;

      if ($file) {
        $download_url = \Drupal::service('file_url_generator')
          ->generateAbsoluteString($file->getFileUri());

        $form_state->set('download_url', $download_url);
        $form_state->setRebuild(TRUE);
      }
    }
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
