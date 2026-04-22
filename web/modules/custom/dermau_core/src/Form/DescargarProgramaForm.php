<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\enterprise_integrations\Service\HubspotService;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Drupal\Component\Utility\Html;
use Drupal\taxonomy\Entity\Term;

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
    // IMAGEN BACKGROUND
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

    // CSS dinámico correcto
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
    // TAXONOMÍA PROFESIÓN
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

    // =========================
    // CHECK LEGAL
    // =========================
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

    // Descargar PDF
    $programa = Node::load($nid);

    if ($programa && $programa->hasField('field_pdf_registro')) {

      $file = $programa->get('field_pdf_registro')->entity;

      if ($file) {
        $uri = $file->getFileUri();
        $path = \Drupal::service('file_system')->realpath($uri);

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
          ResponseHeaderBag::DISPOSITION_ATTACHMENT,
          $file->getFilename()
        );

        $response->send();
        exit;
      }
    }
  }

}
