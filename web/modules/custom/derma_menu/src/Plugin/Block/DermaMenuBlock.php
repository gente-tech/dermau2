<?php

namespace Drupal\derma_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;

/**
 * @Block(
 *   id = "derma_menu_block",
 *   admin_label = @Translation("Derma Custom Menu")
 * )
 */
class DermaMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected MenuLinkTreeInterface $menuTree;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MenuLinkTreeInterface $menu_tree
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuTree = $menu_tree;
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu.link_tree')
    );
  }

  public function build() {

    $menu_name = 'main';

    $parameters = new MenuTreeParameters();
    $parameters->setMaxDepth(2);
    $parameters->onlyEnabledLinks();

    $tree = $this->menuTree->load($menu_name, $parameters);

    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];

    $tree = $this->menuTree->transform($tree, $manipulators);

    return [
      '#theme' => 'derma_menu_block',
      '#items' => $tree,
      '#cache' => [
        'contexts' => ['route'],
        'tags' => ['config:system.menu.' . $menu_name],
      ],
    ];
  }
}
