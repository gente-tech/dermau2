<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

class ContactoRegistroForm extends FormBase {

  public function getFormId() {
    return 'dermau_contacto_registro_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    // Cargar programas (tipo de contenido "programa")
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'programa')
      ->condition('status', 1)
      ->accessCheck(TRUE);
      ->sort('title');

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);

    $programas = [];

    foreach ($nodes as $node) {
      $programas[$node->id()] = $node->getTitle();
    }

    $form['programa'] = [
      '#type' => 'select',
      '#title' => $this->t('Programa'),
      '#options' => $programas,
      '#required' => TRUE,
      '#empty_option' => $this->t('- Seleccione un programa -'),
    ];

    $form['nombre'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#required' => TRUE,
    ];

    $form['apellido'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Apellido'),
      '#required' => TRUE,
    ];

    $form['telefono'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Teléfono'),
      '#required' => TRUE,
    ];

    $form['ciudad'] = [
      '#type' => 'select',
      '#title' => $this->t('Ciudad'),
      '#options' => [
        'bogota' => 'Bogotá',
        'medellin' => 'Medellín',
        'cali' => 'Cali',
      ],
      '#required' => TRUE,
    ];

    $form['profesion'] = [
      '#type' => 'select',
      '#title' => $this->t('Profesión'),
      '#options' => [
        'dermatologo' => 'Dermatólogo',
        'medico' => 'Médico',
        'estetica' => 'Medicina estética',
      ],
      '#required' => TRUE,
    ];

    $form['mensaje'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mensaje (opcional)'),
    ];

    $form['autorizacion'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Autorizo a eClass a enviarme información vía email'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Contáctame'),
      '#attributes' => [
        'class' => ['dermau-submit'],
      ],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $nombre = $form_state->getValue('nombre');
    $apellido = $form_state->getValue('apellido');
    $telefono = $form_state->getValue('telefono');

    // Generar email temporal si no existe en el formulario
    $email = strtolower($nombre . '.' . $apellido) . '@registro.local';

    $user = User::create([
      'name' => $email,
      'mail' => $email,
      'status' => 0,
    ]);

    // Rol registro
    $user->addRole('registro');

    $user->save();

    $this->messenger()->addMessage($this->t('Tu solicitud fue enviada correctamente.'));
  }

}
