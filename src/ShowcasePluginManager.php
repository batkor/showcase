<?php

declare(strict_types=1);

namespace Drupal\showcase;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\Core\Site\Settings;
use Drupal\showcase\Discovery\ShowcaseDiscovery;

/**
 * Defines a plugin manager to deal with Front Matter on twig files.
 *
 * Modules and themes can define Front Matter settings on twig files.
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
    // The showcase id. Set by the plugin system based
    // on the top-level YAML key.
    'id' => '',
    // Optional plugin title.
    'label' => NULL,
    // Default plugin class.
    'class' => ShowcasePluginDefault::class,
    // The route path.
    // Creating new route if path not registered or override already exist.
    // If path NULL to will create block plugin.
    'path' => NULL,
    // Use this options if your template contains html tag.
    'html' => FALSE,
    // The route requirements.
    'requirements' => [],
    // The access to plugin. Override access to route.
    'access' => NULL,
    // The list variables available in template or callable string.
    'data' => [],
    // The paths list to .css file for attach into a plugin.
    'css' => [],
    // The paths list to .js file for attach into a plugin.
    'js' => [],
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
   * The app root directory.
   */
  protected string $appRoot;

  /**
   * Constructs ShowcasePluginManager object.
   */
  public function __construct(
    CacheBackendInterface $cacheBackend,
    ModuleHandlerInterface $moduleHandler,
    ThemeHandlerInterface $themeHandler,
    array $frontMatterSettings,
    string $appRoot,
  ) {
    $this->factory = new ContainerFactory($this);
    $this->moduleHandler = $moduleHandler;
    $this->themeHandler = $themeHandler;
    $this->alterInfo('showcase_info');
    $this->setCacheBackend($cacheBackend, 'showcase_plugins');
    $this->templateDirectories = $frontMatterSettings['directories'];
    $this->appRoot = $appRoot;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery(): ShowcaseDiscovery {
    if (!isset($this->discovery)) {
      $directories = $this->moduleHandler->getModuleDirectories() + $this->themeHandler->getThemeDirectories();
      $directories = \array_map([self::class, 'findDirectories'], $directories);
      $directories += self::getArbitraryDirectories();
      $this->discovery = new ShowcaseDiscovery($directories);
    }

    return $this->discovery;
  }

  public function findDirectories($directory): array {
    return \array_map(static fn ($templateDirectory) =>
      $directory . \DIRECTORY_SEPARATOR . \ltrim($templateDirectory, \DIRECTORY_SEPARATOR),
    $this->templateDirectories);
  }

  /**
   * {@inheritdoc}
   */
  protected function alterDefinitions(&$definitions): void {
    foreach ($definitions as &$def) {
      $def['source_file_relative'] = \str_replace($this->appRoot, '', $def['source_file']);

      if ($this->moduleHandler->moduleExists($def['provider'])) {
        $module = $this->moduleHandler->getModule($def['provider']);
        $def['provider_directory'] = $module->getPath();

        continue;
      }

      if ($this->themeHandler->themeExists($def['provider'])) {
        $theme = $this->themeHandler->getTheme($def['provider']);
        $def['provider_directory'] = $theme->getPath();

        continue;
      }

      if (!self::isArbitraryProvider($def['provider'])) {
        continue;
      }

      $def['provider_directory'] = $def['template_directory'];
    }

    parent::alterDefinitions($definitions);
  }

  /**
   * {@inheritdoc}
   */
  protected function providerExists($provider): bool {
    if ($this->moduleHandler->moduleExists($provider)) {
      return TRUE;
    }

    if ($this->themeHandler->themeExists($provider)) {
      return TRUE;
    }

    return self::isArbitraryProvider($provider);
  }

  /**
   * Returns env.
   */
  public static function getEnv(): ?string {
    return Settings::get('showcase')['env'] ?? NULL;
  }

  /**
   * Returns arbitrary template directory.
   */
  public static function getArbitraryDirectories(): array {
    $directories = [];

    foreach (Settings::get('showcase')['directories'] ?? [] as $dirPath) {
      $directories['showcase:arbitrary:' . $dirPath] = [$dirPath];
    }

    return $directories;
  }

  /**
   * Returns TRUE if provider contains arbitrary directory mask.
   */
  public static function isArbitraryProvider(string $provider): bool {
    return \str_starts_with($provider, 'showcase:arbitrary:');
  }

}
