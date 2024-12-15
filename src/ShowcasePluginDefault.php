<?php

declare(strict_types=1);

namespace Drupal\showcase;

use Drupal\Core\Controller\ControllerResolver;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

/**
 * Default class used for showcases plugins.
 */
final class ShowcasePluginDefault extends PluginBase implements ShowcasePluginInterface, ContainerFactoryPluginInterface {

  /**
   * The app root.
   */
  protected string $root;

  /**
   * The callable resolver.
   */
  protected ?ControllerResolver $controllerResolver;

  /**
   * The argument resolver.
   */
  protected ?ArgumentResolverInterface $argumentResolver;

  /**
   * The request stack.
   */
  protected ?RequestStack $requestStack;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $static = new static($configuration, $plugin_id, $plugin_definition);
    $static->root = $container->getParameter('app.root');
    $static->controllerResolver = $container->get('controller_resolver');
    $static->argumentResolver = $container->get('http_kernel.controller.argument_resolver');
    $static->requestStack = $container->get('request_stack');

    return $static;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    [$provider, $template] = explode(':', $this->getPluginId());

    return $this->pluginDefinition['label'] ?? (string) new TranslatableMarkup('Showcase block. @provider: @template', [
      '@provider' => $provider,
      '@template' => $template,
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
    return [
      '#type' => 'inline_template',
      '#template' => '{{ value|raw }}',
      '#context' => [
        'value' => $this->render(),
      ],
      '#attached' => [
        'library' => [
          'showcase/' . $this->getPluginId(),
        ]
      ],
    ];
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
    template_preprocess($variables, NULL, []);
    $path = ltrim($this->getTemplatePath(), DIRECTORY_SEPARATOR);

    return (string) \twig_render_template($path, $variables);
  }

  /**
   * {@inheritdoc}
   */
  public function getVariables(): array {
    $data = $this->getPluginDefinition()['data'];

    if (is_array($data)) {
      return $data;
    }

    try {
      $dataCallable = $this->controllerResolver->getControllerFromDefinition($data);
    }
    catch (\InvalidArgumentException $e) {
      throw new \InvalidArgumentException(sprintf('Not callable data on plugin %s', $this->getPluginId()), 0, $e);
    }

    if (\is_array($dataCallable) && method_exists(...$dataCallable)) {
      $controllerReflector = new \ReflectionMethod(...$dataCallable);
    }
    elseif (\is_string($dataCallable) && str_contains($dataCallable, '::')) {
      $controllerReflector = new \ReflectionMethod(...explode('::', $dataCallable, 2));
    }
    else {
      $controllerReflector = new \ReflectionFunction($dataCallable(...));
    }

    $arguments = $this
      ->argumentResolver
      ->getArguments($this->requestStack->getCurrentRequest(), $dataCallable, $controllerReflector);

    return $dataCallable(...$arguments);
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
    return str_replace($this->root, '', $this->getPluginDefinition()['source_file']);
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplateDirectory(): string {
    return str_replace($this->root, '', $this->getPluginDefinition()['template_directory']);
  }

  /**
   * {@inheritdoc}
   */
  public function getProviderDirectory(): string {
    return $this->getPluginDefinition()['provider_directory'];
  }

}
