<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\enterprise_integrations\Service\HubspotService;
use Drupal\Component\Utility\Html;

class DescargarProgramaForm extends FormBase {

  protected $hubspotService;

  public function getFormId() {
    return 'descargar_programa_form';
  }

  public function __construct(HubspotService $hubspotService){
    $this->hubspotService = $hubspotService;
  }

  public static function create(ContainerInterface $container){
    return new static(
      $container->get('enterprise_integrations.hubspot')
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    // =========================
    // 🔥 OBTENER ALIAS
    // =========================
    $request = \Drupal::request();
    $alias = $request->query->get('programa');

    if (!$alias) {
      return ['#markup' => 'Programa no válido'];
    }

    $alias = ltrim(urldecode($alias), '/');

    // =========================
    // 🔥 FIX REAL: usar repository (NO manager)
    // =========================
    $alias_service = \Drupal::service('path_alias.repository');

    $result = $alias_service->lookupByAlias(
      '/' . $alias,
      \Drupal::languageManager()->getCurrentLanguage()->getId()
    );
    
    if (!$result || empty($result['path']) || !preg_match('/^\/node\/(\d+)$/', $result['path'], $matches)) {
      \Drupal::logger('dermau_core')->error('Alias inválido: @alias', ['@alias' => $alias]);
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

    // TAXONOMÍA
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
              <h3>Muchas gracias</h3>
              <p>Un ejecutivo te contactará para ayudarte con tu proceso de inscripción.</p>
              <p style="font-size:13px;color:#6b7280;">Tu descarga comenzará automáticamente...</p>
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

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $nid = $form_state->get('programa_nid');

    $data = [
      'nombre' => $form_state->getValue('nombre'),
      'apellido' => $form_state->getValue('apellido'),
      'email' => $form_state->getValue('email'),
      'telefono' => $form_state->getValue('telefono'),
      'profesion' => $form_state->getValue('profesion'),
    ];

    try {
      $node = Node::create([
        'type' => 'registro_programa',
        'title' => $data['nombre'] . ' ' . $data['apellido'],
        'field_email' => $data['email'],
        'field_programa' => ['target_id' => $nid],
      ]);
      $node->save();
    }
    catch (\Exception $e) {
      \Drupal::logger('dermau_core')->error($e->getMessage());
    }

    $this->hubspotService->createContact([
      'email' => $data['email'],
      'firstname' => $data['nombre'],
      'lastname' => $data['apellido'],
      'phone' => $data['telefono'],
    ]);

    $programa = Node::load($nid);

    if ($programa && $programa->hasField('field_pdf_registro')) {

      $file = $programa->get('field_pdf_registro')->entity;

      if ($file) {
        $download_url = \Drupal::service('file_url_generator')
          ->generateAbsoluteString($file->getFileUri());

        $form_state->set('download_url', $download_url);
        $form_state->setRebuild(TRUE);
      }
    }
  }

}
