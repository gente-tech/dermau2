<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\enterprise_integrations\Service\HubspotService;


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

    // Guardar nid en el form_state
    $form_state->set('programa_nid', $nid->id());

    $form['nombre'] = [
      '#type' => 'textfield',
      '#title' => 'Nombre',
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => 'Email',
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Descargar programa',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $nid = $form_state->get('programa_nid');
    $nombre = $form_state->getValue('nombre');
    $email = $form_state->getValue('email');

    // =========================
    // 1. Crear nodo registro
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
    // 2. Enviar a HubSpot
    // =========================
      // Crear usuario en hubspot
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
        $url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());

        // Redirección correcta en Drupal
        $form_state->setRedirectUrl(\Drupal\Core\Url::fromUri($url));
        return;
      }
    }

    // Fallback si no hay PDF
    \Drupal::messenger()->addError('No se encontró el archivo para descargar.');
  }



}
