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
          if ($docente->hasField('body') && !$docente->get('body')->isEmpty()) {
            $descripcion = $docente->get('body')->value;
          }

          // imagen
          $imagen = '';
          if ($docente->hasField('field_imagen') && !$docente->get('field_imagen')->isEmpty()) {
            $imagen = \Drupal::service('file_url_generator')
              ->generateAbsoluteString(
                $docente->get('field_imagen')->entity->getFileUri()
              );
          }

          // especialidad
          $cargo = '';
          if ($docente->hasField('field_especialidad') && !$docente->get('field_especialidad')->isEmpty()) {
            $cargo = $docente->get('field_especialidad')->value;
          }

          // universidad
          $universidad = '';
          if ($docente->hasField('field_universidad') && !$docente->get('field_universidad')->isEmpty()) {
            $universidad = $docente->get('field_universidad')->value;
          }

          // ciudad
          $ciudad = '';
          if ($docente->hasField('field_ciudad') && !$docente->get('field_ciudad')->isEmpty()) {
            $ciudad = $docente->get('field_ciudad')->value;
          }

          // email
          $email = '';
          if ($docente->hasField('field_email') && !$docente->get('field_email')->isEmpty()) {
            $email = $docente->get('field_email')->value;
          }

          // redes
          $linkedin = '';
          if ($docente->hasField('field_linkedin') && !$docente->get('field_linkedin')->isEmpty()) {
            $linkedin = $docente->get('field_linkedin')->uri;
          }

          $facebook = '';
          if ($docente->hasField('field_facebook') && !$docente->get('field_facebook')->isEmpty()) {
            $facebook = $docente->get('field_facebook')->uri;
          }

          $instagram = '';
          if ($docente->hasField('field_instagram') && !$docente->get('field_instagram')->isEmpty()) {
            $instagram = $docente->get('field_instagram')->uri;
          }

          $web = '';
          if ($docente->hasField('field_web') && !$docente->get('field_web')->isEmpty()) {
            $web = $docente->get('field_web')->uri;
          }

          // PROGRAMAS DEL DOCENTE
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
