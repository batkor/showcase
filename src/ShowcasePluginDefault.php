<?php

declare(strict_types=1);

namespace Drupal\showcase;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\showcase\Event\ShowcasePrepareVariableEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Default class used for showcases plugins.
 */
final class ShowcasePluginDefault extends PluginBase implements ShowcasePluginInterface, ContainerFactoryPluginInterface {

  protected string $root;

  protected ?EventDispatcherInterface $eventDispatcher;

  protected string $placeholderToken;

  protected ?CacheableMetadata $cacheableMetadata;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $static = new self($configuration, $plugin_id, $plugin_definition);
    $static->root = $container->getParameter('app.root');
    $static->eventDispatcher = $container->get('event_dispatcher');

    return $static;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    if (!empty($this->pluginDefinition['label'])) {
      return $this->pluginDefinition['label'];
    }

    return (string) new TranslatableMarkup('Template: @template', [
      '@provider' => $this->getPluginDefinition()['provider'] ?? '',
      '@template' => '.../' . \basename($this->getTemplatePath()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function isHtml(): bool {
    return $this->getPluginDefinition()['html'] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [
      '#type' => 'inline_template',
      '#template' => '{{ value|raw }}',
      '#context' => [
        'value' => $this->render(),
      ],
      '#attached' => [
        'library' => [
          'showcase/' . $this->getPluginId(),
        ],
      ],
    ];

    if ($this->isHtml()) {
      $types = [
        'styles' => 'css',
        'scripts' => 'js',
        'scripts_bottom' => 'js-bottom',
        'head' => 'head',
      ];

      foreach ($types as $type => $name) {
        $placeholder = '<' . $name . '-placeholder token="' . $this->getPlaceholderToken() . '">';
        $build['#attached']['html_response_attachment_placeholders'][$type] = $placeholder;
      }
    }

    $this->cacheableMetadata->applyTo($build);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function render(): string {
    include_once $this->root . '/core/themes/engines/twig/twig.engine';

    $variables = [
      'data' => $this->getVariables(),
      'theme_hook_original' => 'NOT USE HOOK',
      'directory' => $this->getProviderDirectory(),
    ];

    $this->cacheableMetadata = CacheableMetadata::createFromRenderArray($variables['data']);

    if ($this->isHtml()) {
      $variables['placeholder_token'] = $this->getPlaceholderToken();
    }

    \template_preprocess($variables, NULL, []);
    $path = \ltrim($this->getTemplatePath(), \DIRECTORY_SEPARATOR);

    return (string) \twig_render_template($path, $variables);
  }

  /**
   * {@inheritdoc}
   */
  public function getVariables(): array {
    $event = new ShowcasePrepareVariableEvent($this);
    $this->eventDispatcher->dispatch($event);

    return $event->getVariables();
  }

  /**
   * {@inheritdoc}
   */
  public function access(): bool {
    if (empty($this->getPluginDefinition()['access'])) {
      return TRUE;
    }

    if ($this->getPluginDefinition()['access'] === 'all') {
      return TRUE;
    }

    return $this->getPluginDefinition()['access'] === ShowcasePluginManager::getEnv();
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplatePath(): string {
    if (ShowcasePluginManager::isArbitraryProvider($this->getPluginDefinition()['provider'])) {
      return $this->getPluginDefinition()['source_file'];
    }

    return $this->getPluginDefinition()['source_file_relative'];
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplateDirectory(): string {
    return \str_replace($this->root, '', $this->getPluginDefinition()['template_directory']);
  }

  /**
   * {@inheritdoc}
   */
  public function getProviderDirectory(): string {
    return $this->getPluginDefinition()['provider_directory'];
  }

  public function getPlaceholderToken(): string {
    if (isset($this->placeholderToken)) {
      return $this->placeholderToken;
    }

    $this->placeholderToken = Crypt::randomBytesBase64(55);

    return $this->placeholderToken;
  }

}
