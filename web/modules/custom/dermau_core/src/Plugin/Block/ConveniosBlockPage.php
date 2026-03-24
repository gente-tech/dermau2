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
      ->sort('field_orden_visualizacion.value', 'ASC')
      ->execute();

    $convenios = $storage->loadMultiple($ids);

    $data = [];

    foreach ($convenios as $convenio) {

      // Programas relacionados directamente
      $programas = [];
      if (!$convenio->get('field_programas_vinculados_conve')->isEmpty()) {
        $programas_entities = $convenio->get('field_programas_vinculados_conve')->referencedEntities();

        foreach ($programas_entities as $programa) {
          $programas[] = [
            'id' => $programa->id(),
            'title' => $programa->label(),
          ];
        }
      }

      // Docentes relacionados directamente
      $docentes = [];
      if (!$convenio->get('field_docentes_vinculados')->isEmpty()) {
        $docentes_entities = $convenio->get('field_docentes_vinculados')->referencedEntities();

        foreach ($docentes_entities as $docente) {

          $docentes[] = [
            'id' => $docente->id(),
            'title' => $docente->label(),
            'body' => !$docente->get('body')->isEmpty() ? $docente->get('body')->value : '',
          ];
        }
      }

      $data[] = [
        'id' => $convenio->id(),
        'title' => $convenio->label(),
        'ciudad' => $convenio->get('field_ciudad_convenio')->value ?? '',
        'ano' => $convenio->get('field_ano_de_funcacion')->value ?? '',
        'descripcion' => !$convenio->get('field_descripcion_corta_convenio')->isEmpty()
          ? $convenio->get('field_descripcion_corta_convenio')->value
          : '',
        'logo' => !$convenio->get('field_logo')->isEmpty()
          ? file_create_url($convenio->get('field_logo')->entity->getFileUri())
          : '',
        'programas' => $programas,
        'docentes' => $docentes,
      ];
    }

    return $data;
  }

}
