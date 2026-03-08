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

    $form['#attributes']['class'][] = 'du-form-register__form';

    /*
    ------------------------
    Cargar programas
    ------------------------
    */

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'programa')
      ->condition('status', 1)
      ->sort('title')
      ->accessCheck(FALSE);

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);

    $programas = [];

    foreach ($nodes as $node) {
      $programas[$node->id()] = $node->getTitle();
    }

    /*
    ------------------------
    GROUP CONTAINER
    ------------------------
    */

    $form['group_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['du-form-register__form-group-container']
      ]
    ];

    /*
    ------------------------
    GROUP 1
    ------------------------
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
      '#empty_option' => $this->t('Seleccione tu programa'),
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

    /*
    ------------------------
    GROUP 2
    ------------------------
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

    $form['group_container']['group2']['phone_group']['country'] = [
      '#type' => 'select',
      '#options' => [
        '+57' => 'Co',
        '+52' => 'Mx',
        '+54' => 'Ar',
        '+56' => 'Ch'
      ],
      '#default_value' => '+57',
      '#attributes' => [
        'id' => 'du-reg-country'
      ],
      '#title_display' => 'invisible'
    ];

    $form['group_container']['group2']['phone_group']['telefono'] = [
      '#type' => 'tel',
      '#attributes' => [
        'placeholder' => 'Teléfono',
        'id' => 'du-reg-phone'
      ],
      '#required' => TRUE,
      '#title_display' => 'invisible'
    ];

    /*
    CITY
    */

    $form['group_container']['group2']['ciudad'] = [
      '#type' => 'select',
      '#options' => [
        '1' => 'Ciudad 1'
      ],
      '#empty_option' => $this->t('Seleccione tu ciudad'),
      '#attributes' => [
        'class' => ['du-form-select'],
        'id' => 'du-reg-city'
      ],
      '#required' => TRUE,
      '#title_display' => 'invisible'
    ];

    /*
    PROFESION
    */

    $form['group_container']['group2']['profesion'] = [
      '#type' => 'select',
      '#options' => [
        '1' => 'Profesión 1'
      ],
      '#empty_option' => $this->t('Seleccione tu profesión'),
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
    CHECKBOX
    */

    $form['autorizacion'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Autorizo a eClass a enviarme información vía email'),
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
        'class' => ['du-btn','du-btn--primary']
      ]
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $nombre = $form_state->getValue('nombre');
    $apellido = $form_state->getValue('apellido');

    $email = strtolower($nombre.'.'.$apellido).'@registro.local';

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
