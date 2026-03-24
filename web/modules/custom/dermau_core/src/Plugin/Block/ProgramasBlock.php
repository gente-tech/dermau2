<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block for featured home programs.
 *
 * @Block(
 *   id = "dermau_programas_block",
 *   admin_label = @Translation("Dermau Programas Block"),
 * )
 */
class ProgramasBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The file url generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * Constructs a ProgramasBlock object.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    FileUrlGeneratorInterface $file_url_generator
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $programas = [];
    $cache_tags = ['node_list', 'taxonomy_term_list'];

    // Obtener términos para filtros.
    $terms = $this->entityTypeManager
      ->getStorage('taxonomy_term')
      ->loadTree('tipos_de_programas');

    // Query de nodos tipo programa con destacado home activo.
    $nids = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'programa')
      ->condition('status', 1)
      ->condition('field_destacado_home.value', 1)
      ->sort('created', 'DESC')
      ->execute();

    if (!empty($nids)) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

      foreach ($nodes as $node) {
        $image_url = '';
        $image_alt = $node->getTitle();

        if (!$node->get('field_imagen_programa')->isEmpty() && $node->get('field_imagen_programa')->entity) {
          $image = $node->get('field_imagen_programa')->first();
          $file = $image->entity;

          if ($file) {
            $image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
          }

          if (!empty($image->alt)) {
            $image_alt = $image->alt;
          }
        }

        $tipo_programa = '';
        $tipo_programa_id = NULL;
        if (!$node->get('field_tipo_de_programa')->isEmpty() && $node->get('field_tipo_de_programa')->entity) {
          $tipo_term = $node->get('field_tipo_de_programa')->entity;
          $tipo_programa = $tipo_term->label();
          $tipo_programa_id = $tipo_term->id();
          $cache_tags = Cache::mergeTags($cache_tags, $tipo_term->getCacheTags());
        }

        $universidad = '';
        $universidad_id = NULL;
        if (!$node->get('field_universidad_del_programa')->isEmpty() && $node->get('field_universidad_del_programa')->entity) {
          $universidad_node = $node->get('field_universidad_del_programa')->entity;
          $universidad = $universidad_node->label();
          $universidad_id = $universidad_node->id();
          $cache_tags = Cache::mergeTags($cache_tags, $universidad_node->getCacheTags());
        }

        $programa = [
          'nid' => $node->id(),
          'title' => $node->getTitle(),
          'url' => $node->toUrl()->toString(),
          'body' => $node->get('body')->value ?? '',
          'descripcion_corta' => $node->get('field_descripcion_corta')->value ?? '',
          'descripcion_general' => $node->get('field_descripcion_general')->value ?? '',
          'destacado' => (bool) $node->get('field_destacado')->value,
          'destacado_home' => (bool) $node->get('field_destacado_home')->value,
          'duracion_programa' => $node->get('field_duracion_programa')->value ?? '',
          'cupos' => !$node->get('field_cupos')->isEmpty() ? (int) $node->get('field_cupos')->value : NULL,
          'cupos_label' => !$node->get('field_cupos')->isEmpty()
            ? $node->get('field_cupos')->value . ' cupos'
            : 'Cupos limitados',
          'fecha_inicio' => $node->get('field_fecha')->value ?? '',
          'id_video_youtube' => $node->get('field_id_video_youtube')->value ?? '',
          'image_url' => $image_url,
          'image_alt' => $image_alt,
          'inversion_precio_regular' => $node->get('field_inversion_precio_regular')->value ?? '',
          'metodologia' => !$node->get('field_metodologia')->isEmpty() && $node->get('field_metodologia')->entity
            ? $node->get('field_metodologia')->entity->label()
            : '',
          'modulos_count' => !$node->get('field_modulos')->isEmpty()
            ? $node->get('field_modulos')->count()
            : 0,
          'objetivos_de_aprendizaje' => $node->get('field_objetivos_de_aprendizaje')->value ?? '',
          'pdf_registro' => !$node->get('field_pdf_registro')->isEmpty() && $node->get('field_pdf_registro')->entity
            ? $this->fileUrlGenerator->generateAbsoluteString($node->get('field_pdf_registro')->entity->getFileUri())
            : '',
          'plan_de_estudios' => !$node->get('field_plan_de_estudios')->isEmpty() && $node->get('field_plan_de_estudios')->entity
            ? $this->fileUrlGenerator->generateAbsoluteString($node->get('field_plan_de_estudios')->entity->getFileUri())
            : '',
          'publico_objetivo' => $node->get('field_publico_objetivo')->value ?? '',
          'requisitos' => $node->get('field_requisitos')->value ?? '',
          'tags_programa' => !$node->get('field_tags_programa')->isEmpty()
            ? array_column($node->get('field_tags_programa')->getValue(), 'value')
            : [],
          'tipo_programa' => $tipo_programa,
          'tipo_programa_id' => $tipo_programa_id,
          'universidad' => $universidad,
          'universidad_id' => $universidad_id,
          'node' => $node,
        ];

        $programas[] = $programa;
        $cache_tags = Cache::mergeTags($cache_tags, $node->getCacheTags());

      }
    }

    return [
      '#theme' => 'dermau_programas_block',
      '#terms' => $terms,
      '#programas' => $programas,
      '#attached' => [
        'library' => [
          'dermau_core/programas-filter',
        ],
      ],
      '#cache' => [
        'tags' => $cache_tags,
        'contexts' => ['url.path', 'languages:language_interface', 'user.permissions'],
      ],
    ];
  }

}
