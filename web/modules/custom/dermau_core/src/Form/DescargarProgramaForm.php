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

    if (!$nid || $nid->bundle() !== 'programa') {
      return ['#markup' => 'Programa no válido'];
    }

    $form_state->set('programa_nid', $nid->id());

    // =========================
    // 🔥 IMAGEN (CORRECTO PARA MEDIA)
    // =========================
    $image_url = '';

    if ($nid->hasField('field_imagen_programa') && !$nid->get('field_imagen_programa')->isEmpty()) {

      $media = $nid->get('field_imagen_programa')->entity;

      if ($media && $media->hasField('field_media_image')) {

        $file = $media->get('field_media_image')->entity;

        if ($file) {
          $image_url = \Drupal::service('file_url_generator')
            ->generateAbsoluteString($file->getFileUri());
        }
      }
    }

    // Fallback seguro
    if (empty($image_url)) {
      $image_url = '/themes/custom/tu_tema/images/default-programa.jpg';
    }

    // =========================
    // WRAPPER CON BACKGROUND
    // =========================
    $form['#prefix'] = '<div class="programa-background" style="background-image:url(' . $image_url . ')">
                          <div class="programa-overlay">
                            <div class="descargar-programa-wrapper">';
    $form['#suffix'] = '      </div>
                          </div>
                        </div>';

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
      '#attributes' => ['placeholder' => 'Ingresa tu nombre'],
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => 'Correo electrónico',
      '#required' => TRUE,
      '#attributes' => ['placeholder' => 'correo@ejemplo.com'],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Descargar programa',
      '#attributes' => ['class' => ['btn-descargar']],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $nid = $form_state->get('programa_nid');
    $nombre = $form_state->getValue('nombre');
    $email = $form_state->getValue('email');

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

    $hubspotData = [
      'email' => $email,
      'firstname' => $nombre,
      'lastname' => 'Programa ' . $nid,
      'phone' => '',
    ];

    $this->hubspotService->createContact($hubspotData);

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
