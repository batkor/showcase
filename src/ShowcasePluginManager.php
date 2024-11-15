<?php

declare(strict_types=1);

namespace Drupal\showcase;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\showcase\Discovery\ShowcaseDiscovery;

/**
 * Defines a plugin manager to deal with Front Matter on twig files.
 *
 * Modules  and themes can define Front Matter settings on twig files.
 *
 * Example Front Matter syntax:
 * @code
 * {#---
 * {
 *   "path": "/example/twig",
 *   "data": {
 *     "text": "Example text"
 *   }
 * }
 * ---#}
 *
 * <div>
 *   {{ data.text }}
 * </div>
 * @endcode
 *
 * @see \Drupal\showcase\ShowcasePluginDefault
 * @see \Drupal\showcase\ShowcasePluginInterface
 */
final class ShowcasePluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  protected $defaults = [
    // The showcase id. Set by the plugin system based on the top-level YAML key.
    'id' => '',
    // Optional plugin title.
    'label' => NULL,
    // Default plugin class.
    'class' => ShowcasePluginDefault::class,
    // The route path. Creating new route if path not registered or override already exist.
    // If path NULL to will create block plugin.
    'path' => NULL,
    // The route requirements.
    'requirements' => [],
    // The access to plugin. Override access to route.
    'access' => NULL,
    // The list variables available in template or callable string.
    'data' => [],
  ];

  /**
   * Theme handler.
   */
  protected ThemeHandlerInterface $themeHandler;

  /**
   * The directories list for find templates on module and themes.
   */
  protected array $templateDirectories;

  /**
   * Constructs ShowcasePluginManager object.
   */
  public function __construct(
    CacheBackendInterface $cacheBackend,
    ModuleHandlerInterface $moduleHandler,
    ThemeHandlerInterface $themeHandler,
    array $frontMatterSettings,
  ) {
    $this->factory = new ContainerFactory($this);
    $this->moduleHandler = $moduleHandler;
    $this->themeHandler = $themeHandler;
    $this->alterInfo('showcase_info');
    $this->setCacheBackend($cacheBackend, 'showcase_plugins');
    $this->templateDirectories = $frontMatterSettings['directories'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery(): ShowcaseDiscovery {
    if (!isset($this->discovery)) {
      $directories = $this->moduleHandler->getModuleDirectories() + $this->themeHandler->getThemeDirectories();

      $directories = array_map(function ($dir) {
        return array_map(function ($templateDirectory) use ($dir) {
          return $dir . DIRECTORY_SEPARATOR . ltrim($templateDirectory, DIRECTORY_SEPARATOR);
        }, $this->templateDirectories);
      }, $directories);

      $this->discovery = new ShowcaseDiscovery($directories);
    }

    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterDefinitions(&$definitions) {
    foreach ($definitions as &$def) {
      if ($this->moduleHandler->moduleExists($def['provider'])) {
        $module = $this->moduleHandler->getModule($def['provider']);
        $def['provider_directory'] = $module->getPath();
      }
      if ($this->themeHandler->themeExists($def['provider'])) {
        $theme = $this->themeHandler->getTheme($def['provider']);
        $def['provider_directory'] = $theme->getPath();
      }
    }

    parent::alterDefinitions($definitions);
  }

  /**
   * {@inheritdoc}
   */
  protected function providerExists($provider): bool {
    return $this->moduleHandler->moduleExists($provider) || $this->themeHandler->themeExists($provider);
  }

}
