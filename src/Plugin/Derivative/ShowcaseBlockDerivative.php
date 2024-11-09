<?php

namespace Drupal\showcase\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\showcase\ShowcasePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides block plugin definitions for all found twig templates.
 *
 * @see \Drupal\showcase\Plugin\Block\ShowcaseBlock
 */
class ShowcaseBlockDerivative extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Constructs.
   */
  public function __construct(
    protected readonly string $basePluginId,
    protected readonly ?ShowcasePluginManager $pluginManagerShowcase
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('plugin.manager.showcase')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    foreach ($this->pluginManagerShowcase->getDefinitions() as $def) {
      if (!empty($def['path'])) {
        continue;
      }

      /** @var \Drupal\showcase\ShowcasePluginInterface $plugin */
      $plugin = $this
        ->pluginManagerShowcase
        ->createInstance($def['id']);
      $this->derivatives[$def['id']] = [
        'category' => 'Showcase',
        'admin_label' => $plugin->label(),
      ] + $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
