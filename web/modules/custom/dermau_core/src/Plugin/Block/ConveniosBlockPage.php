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
      ->execute();

    $convenios = $storage->loadMultiple($ids);

    $data = [];

    foreach ($convenios as $convenio) {

      $programas = $this->getProgramasByConvenio($convenio->id());

      $data[] = [
        'id' => $convenio->id(),
        'title' => $convenio->label(),
        'body' => $convenio->body->value ?? '',
        'programas' => $programas,
      ];
    }

    return $data;
  }

  private function getProgramasByConvenio($convenio_id) {

    $storage = \Drupal::entityTypeManager()->getStorage('node');

    $ids = \Drupal::entityQuery('node')
      ->condition('type', 'programa')
      ->condition('status', 1)
      ->condition('field_universidad', $convenio_id)
      ->execute();

    $programas = $storage->loadMultiple($ids);

    $data = [];

    foreach ($programas as $programa) {

      $docentes = $this->getDocentesByPrograma($programa->id());

      $data[] = [
        'id' => $programa->id(),
        'title' => $programa->label(),
        'docentes' => $docentes,
      ];
    }

    return $data;
  }

  private function getDocentesByPrograma($programa_id) {

    $storage = \Drupal::entityTypeManager()->getStorage('node');

    $ids = \Drupal::entityQuery('node')
      ->condition('type', 'docente')
      ->condition('status', 1)
      ->condition('field_programa', $programa_id)
      ->execute();

    $docentes = $storage->loadMultiple($ids);

    $data = [];

    foreach ($docentes as $docente) {

      $data[] = [
        'id' => $docente->id(),
        'title' => $docente->label(),
        'body' => $docente->body->value ?? '',
      ];
    }

    return $data;
  }

}
