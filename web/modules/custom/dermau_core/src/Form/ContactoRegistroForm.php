<?php

namespace Drupal\dermau_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Url;

class ContactoRegistroForm extends FormBase {

  public function getFormId() {
    return 'dermau_contacto_registro_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    /*
    -----------------------------------------
    Cargar librería del teléfono
    -----------------------------------------
    */

    $form['#attached']['library'][] = 'dermau_core/intl_tel_input';

    $form['#attributes']['class'][] = 'du-form-register__form';

    /*
    -------------------------------------------------
    Detectar si estamos dentro de un nodo programa
    -------------------------------------------------
    */

    $node = \Drupal::routeMatch()->getParameter('node');
    $current_program = NULL;

    if ($node && $node->bundle() === 'programa') {
      $current_program = $node->id();
    }

    /*
    -------------------------------------------------
    Cargar programas
    -------------------------------------------------
    */

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'programa')
      ->condition('status', 1)
      ->sort('title')
      ->accessCheck(FALSE);

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);

    $programas = [];

    foreach ($nodes as $programa) {
      $programas[$programa->id()] = $programa->getTitle();
    }

    /*
    -------------------------------------------------
    CONTENEDOR PRINCIPAL
    -------------------------------------------------
    */

    $form['group_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['du-form-register__form-group-container']
      ]
    ];

    /*
    -------------------------------------------------
    GRUPO 1
    -------------------------------------------------
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
      '#default_value' => $current_program,
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
    -------------------------------------------------
    GRUPO 2
    -------------------------------------------------
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

    /*
    Campo teléfono (intl-tel-input)
    */

    $form['group_container']['group2']['phone_group']['telefono'] = [
  '#type' => 'tel',
  '#attributes' => [
    'placeholder' => 'Teléfono',
    'id' => 'du-reg-phone',
    'class' => ['du-form-input']
  ],
  '#required' => TRUE,
  '#title_display' => 'invisible'
];

    /*
    CIUDAD
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
    CONSENTIMIENTO
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

    $programa_id = $form_state->getValue('programa');

    /*
    ---------------------------------
    Crear usuario bloqueado
    ---------------------------------
    */

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

    /*
    ---------------------------------
    Obtener PDF del programa
    ---------------------------------
    */

    $node = Node::load($programa_id);
    $pdf_url = '';

    if ($node && $node->hasField('field_pdf_registro')) {

      $file = $node->get('field_pdf_registro')->entity;

      if ($file) {
        $pdf_url = file_create_url($file->getFileUri());
      }

    }

    /*
    ---------------------------------
    Redirigir a descarga
    ---------------------------------
    */

    if ($pdf_url) {

      $form_state->setRedirectUrl(
        Url::fromUri($pdf_url)
      );

    }

  }

}
