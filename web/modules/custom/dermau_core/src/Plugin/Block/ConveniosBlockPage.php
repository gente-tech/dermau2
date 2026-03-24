<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides Convenios Block Page.
 *
 * @Block(
 *   id = "dermau_convenios_block_page",
 *   admin_label = @Translation("Dermau Convenios Block Page")
 * )
 */
class ConveniosBlockPage extends BlockBase {

  public function build() {
    return [
      '#theme' => 'dermau_convenios_block_page',
      '#convenios' => $this->getConvenios(),
      '#cache' => ['max-age' => 0],
    ];
  }

  private function getConvenios() {

    $storage = \Drupal::entityTypeManager()->getStorage('node');

    $ids = \Drupal::entityQuery('node')
      ->condition('type', 'convenio')
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->execute();

    $convenios = $storage->loadMultiple($ids);

    $data = [];

    foreach ($convenios as $convenio) {

      // ========================
      // PROGRAMAS
      // ========================
      $programas = [];

      if (!$convenio->get('field_programas_vinculados_conve')->isEmpty()) {
        foreach ($convenio->get('field_programas_vinculados_conve')->referencedEntities() as $programa) {

          $tipo = '';
          $tipo_clase = 'tag--diplomado';

          if ($programa->hasField('field_tipo_de_programa') && !$programa->get('field_tipo_de_programa')->isEmpty()) {
            $tipo = $programa->get('field_tipo_de_programa')->entity->label();
            $tipo_clase = 'tag--' . strtolower(str_replace(' ', '-', $tipo));
          }

          $programas[] = [
            'id' => $programa->id(),
            'title' => $programa->label(),
            'tipo' => $tipo,
            'tipo_clase' => $tipo_clase,
          ];
        }
      }

      // ========================
      // DOCENTES
      // ========================
      $docentes = [];

      if (!$convenio->get('field_docentes_vinculados')->isEmpty()) {
        foreach ($convenio->get('field_docentes_vinculados')->referencedEntities() as $docente) {

          // descripcion
          $descripcion = '';
          if ($docente->hasField('field_perfil_profesional') && !$docente->get('field_perfil_profesional')->isEmpty()) {
            $descripcion = $docente->get('field_perfil_profesional')->value;
          }

          // imagen
          $imagen = '';
          if ($docente->hasField('field_foto_docente') && !$docente->get('field_foto_docente')->isEmpty()) {
            $imagen = \Drupal::service('file_url_generator')
              ->generateAbsoluteString(
                $docente->get('field_foto_docente')->entity->getFileUri()
              );
          }

          // especialidad
          $cargo = '';
          if ($docente->hasField('field_especialidad') && !$docente->get('field_especialidad')->isEmpty()) {
            $cargo = $docente->get('field_especialidad')->entity->label();
          }

          // universidad
          $universidad = '';
          if ($docente->hasField('field_universidad') && !$docente->get('field_universidad')->isEmpty()) {
            $universidad = $docente->get('field_universidad')->entity->label();
          }

          // ciudad
          $ciudad = '';
          if ($docente->hasField('field_ciudad') && !$docente->get('field_ciudad')->isEmpty()) {
            $ciudad = $docente->get('field_ciudad')->entity->label();
          }

          // email
          $email = '';
          if ($docente->hasField('field_correo_electronico') && !$docente->get('field_correo_electronico')->isEmpty()) {
            $email = $docente->get('field_correo_electronico')->value;
          }

          // redes
          $linkedin = !$docente->get('field_linkedin')->isEmpty()
            ? $docente->get('field_linkedin')->uri
            : '';

          $facebook = !$docente->get('field_facebook')->isEmpty()
            ? $docente->get('field_facebook')->uri
            : '';

          $instagram = !$docente->get('field_instagram')->isEmpty()
            ? $docente->get('field_instagram')->uri
            : '';

          // pagina web
          $web = '';
          if ($docente->hasField('field_pagina_web') && !$docente->get('field_pagina_web')->isEmpty()) {
            $web = $docente->get('field_pagina_web')->value;
          }

          // PROGRAMAS DOCENTE
          $programas_docente = [];

          if ($docente->hasField('field_programas_vinculados') && !$docente->get('field_programas_vinculados')->isEmpty()) {
            foreach ($docente->get('field_programas_vinculados')->referencedEntities() as $prog) {

              $tipo = '';
              $tipo_clase = 'tag--diplomado';

              if ($prog->hasField('field_tipo_de_programa') && !$prog->get('field_tipo_de_programa')->isEmpty()) {
                $tipo = $prog->get('field_tipo_de_programa')->entity->label();
                $tipo_clase = 'tag--' . strtolower(str_replace(' ', '-', $tipo));
              }

              $programas_docente[] = [
                'titulo' => $prog->label(),
                'tipo' => $tipo,
                'tipo_clase' => $tipo_clase,
              ];
            }
          }

          $docentes[] = [
            'id' => $docente->id(),
            'title' => $docente->label(),
            'descripcion' => $descripcion,
            'image' => $imagen,
            'cargo' => $cargo,
            'universidad' => $universidad,
            'ciudad' => $ciudad,
            'email' => $email,
            'linkedin' => $linkedin,
            'facebook' => $facebook,
            'instagram' => $instagram,
            'web' => $web,
            'programas' => $programas_docente,
          ];
        }
      }

      // ========================
      // LOGO
      // ========================
      $logo = '';
      if (!$convenio->get('field_logo')->isEmpty()) {
        $logo = \Drupal::service('file_url_generator')
          ->generateAbsoluteString(
            $convenio->get('field_logo')->entity->getFileUri()
          );
      }

      // ========================
      // DATA FINAL
      // ========================
      $data[] = [
        'id' => $convenio->id(),
        'title' => $convenio->label(),
        'ciudad' => $convenio->get('field_ciudad_convenio')->value ?? '',
        'ano' => $convenio->get('field_ano_de_funcacion')->value ?? '',
        'descripcion' => !$convenio->get('field_descripcion_corta_convenio')->isEmpty()
          ? $convenio->get('field_descripcion_corta_convenio')->value
          : '',
        'logo' => $logo,
        'programas' => $programas,
        'docentes' => $docentes,
      ];
    }

    return $data;
  }

}
