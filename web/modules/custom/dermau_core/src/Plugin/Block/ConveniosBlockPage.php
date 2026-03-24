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

          if ($programa->hasField('field_tipo_de_programa') && !$programa->get('field_tipo_de_programa')->isEmpty()) {
            $tipo = $programa->get('field_tipo_de_programa')->entity->label();
          }

          $programas[] = [
            'id' => $programa->id(),
            'title' => $programa->label(),
            'tipo' => $tipo,
          ];
        }
      }

      // ========================
      // DOCENTES
      // ========================
      $docentes = [];

      if (!$convenio->get('field_docentes_vinculados')->isEmpty()) {
        foreach ($convenio->get('field_docentes_vinculados')->referencedEntities() as $docente) {

          $descripcion = '';
          if ($docente->hasField('body') && !$docente->get('body')->isEmpty()) {
            $descripcion = $docente->get('body')->value;
          }

          $imagen = '';
          if ($docente->hasField('field_imagen') && !$docente->get('field_imagen')->isEmpty()) {
            $imagen = \Drupal::service('file_url_generator')
              ->generateAbsoluteString(
                $docente->get('field_imagen')->entity->getFileUri()
              );
          }

          $docentes[] = [
            'id' => $docente->id(),
            'title' => $docente->label(),
            'body' => $descripcion,
            'imagen' => $imagen,
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
