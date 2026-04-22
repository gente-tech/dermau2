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

    // Validar nodo
    if (!$nid || $nid->bundle() !== 'programa') {
      return [
        '#markup' => 'Programa no válido',
      ];
    }

    // Wrapper para CSS
    $form['#prefix'] = '<div class="descargar-programa-wrapper">';
    $form['#suffix'] = '</div>';

    // Guardar nid
    $form_state->set('programa_nid', $nid->id());

    // Header bonito
    $titulo_programa = Html::escape($nid->getTitle());

    $form['intro'] = [
      '#markup' => '
        <div class="form-header">
          <h2>Descargar programa</h2>
          <p class="programa-titulo">' . $titulo_programa . '</p>
          <div class="form-divider"></div>
          <p class="form-subtitle">Completa tus datos para acceder al contenido académico.</p>
        </div>
      ',
    ];

    $form['nombre'] = [
      '#type' => 'textfield',
      '#title' => 'Nombre completo',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Ingresa tu nombre',
      ],
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => 'Correo electrónico',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'correo@ejemplo.com',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Descargar programa',
      '#attributes' => [
        'class' => ['btn-descargar'],
      ],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $nid = $form_state->get('programa_nid');
    $nombre = $form_state->getValue('nombre');
    $email = $form_state->getValue('email');

    // =========================
    // 1. Guardar registro
    // =========================
    try {
      $node = Node::create([
        'type' => 'registro_programa',
        'title' => $nombre . ' - ' . date('Y-m-d H:i:s'),
        'field_email' => $email,
        'field_programa' => ['target_id' => $nid],
      ]);
      $node->save();
    }
    catch (\Exception $e) {
      \Drupal::logger('dermau_core')->error($e->getMessage());
    }

    // =========================
    // 2. HubSpot
    // =========================
    $hubspotData = [
      'email' => $email,
      'firstname' => $nombre,
      'lastname' => 'Programa ' . $nid,
      'phone' => '',
    ];

    $hubspotResult = $this->hubspotService->createContact($hubspotData);

    if (!$hubspotResult['success']) {
      \Drupal::logger('enterprise_integrations')->warning(
        'No se pudo crear el contacto en HubSpot para %email. Mensaje: %message',
        [
          '%email' => $hubspotData['email'],
          '%message' => $hubspotResult['message'],
        ]
      );
    }

    // =========================
    // 3. Descargar PDF
    // =========================
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

    \Drupal::messenger()->addError('No se encontró el archivo para descargar.');
  }

}
