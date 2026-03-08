
<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

class ContactoRegistroForm extends FormBase {

  public function getFormId() {
    return 'dermau_contacto_registro_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['programa'] = [
      '#type' => 'select',
      '#title' => $this->t('Programa'),
      '#options' => [
        '1' => 'Programa 1 - Derma U',
        '2' => 'Programa 2',
      ],
      '#required' => TRUE,
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
    ];

    $form['ciudad'] = [
      '#type' => 'select',
      '#title' => $this->t('Ciudad'),
      '#options' => [
        'bogota' => 'Bogotá',
        'medellin' => 'Medellín',
      ],
    ];

    $form['profesion'] = [
      '#type' => 'select',
      '#title' => $this->t('Profesión'),
      '#options' => [
        'dermatologo' => 'Dermatólogo',
        'estetica' => 'Medicina estética',
      ],
    ];

    $form['mensaje'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mensaje'),
    ];

    $form['autorizacion'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Autorizo a eClass a enviarme información vía email'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Contáctame'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $email = strtolower($form_state->getValue('nombre')) . '@temp.local';

    $user = User::create([
      'name' => $email,
      'mail' => $email,
      'status' => 0,
    ]);

    $user->addRole('registro');
    $user->save();

    $this->messenger()->addMessage('Solicitud enviada correctamente.');
  }

}
