<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
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

  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $nid = NULL) {

    if (!$nid || $nid->bundle() !== 'programa') {
      return ['#markup' => 'Programa no válido'];
    }

    $form_state->set('programa_nid', $nid->id());

    // =========================
    // BACKGROUND
    // =========================
    $image_url = '';

    if (!$nid->get('field_imagen_programa')->isEmpty()) {
      $file = $nid->get('field_imagen_programa')->first()->entity;
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

    $titulo_programa = Html::escape($nid->getTitle());

    // =========================
    // HEADER
    // =========================
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

    // =========================
    // CAMPOS
    // =========================
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

    // =========================
    // PROFESIÓN (TAXONOMÍA)
    // =========================
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
    // MODAL (SI YA ENVIÓ)
    // =========================
    $download_url = $form_state->get('download_url');

    if ($download_url) {
      $form['modal'] = [
        '#markup' => '
          <div id="modal-descarga" class="custom-modal">
            <div class="modal-content">
              <h3>Muchas gracias</h3>
              <p>Un ejecutivo te contactará para ayudarte con tu proceso de inscripción.</p>
              <button class="btn-descargar" id="btn-descargar-pdf">Descargar ahora</button>
            </div>
          </div>

          <script>
            document.addEventListener("DOMContentLoaded", function(){
              var modal = document.getElementById("modal-descarga");
              modal.style.display = "flex";

              document.getElementById("btn-descargar-pdf").addEventListener("click", function(){
                window.location.href = "' . $download_url . '";
              });
            });
          </script>
        ',
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

    // Guardar nodo
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

    // HubSpot
    $this->hubspotService->createContact([
      'email' => $data['email'],
      'firstname' => $data['nombre'],
      'lastname' => $data['apellido'],
      'phone' => $data['telefono'],
    ]);

    // =========================
    // GENERAR URL DE DESCARGA
    // =========================
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
