<?php

declare(strict_types=1);

namespace Drupal\showcase\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\Renderer;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\showcase\ShowcasePluginInterface;
use Drupal\showcase\ShowcasePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

/**
 * Implements controller for plugins.
 */
final class ShowcaseController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Constructors.
   */
  public function __construct(
    protected readonly ?ShowcasePluginManager $pluginManagerShowcase,
    protected readonly ?Renderer $renderer,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('plugin.manager.showcase'),
      $container->get('renderer'),
    );
  }

  /**
   * Main callback.
   */
  public function __invoke(Request $request): Response|array {
    $route = $request->get('_route_object');
    \assert($route instanceof Route);

    $def = $route->getOption('showcase');
    $plugin = $this
      ->pluginManagerShowcase
      ->createInstance($def['id']);

    \assert($plugin instanceof ShowcasePluginInterface);

    if ($plugin->isHtml()) {
      $response = new HtmlResponse();
      $response->addCacheableDependency(Url::createFromRequest($request));
      $context = new RenderContext();

      $build = $this
        ->renderer
        ->executeInRenderContext($context, static fn () => $plugin->build());

      if (!$context->isEmpty()) {
        BubbleableMetadata::createFromRenderArray($build)
          ->merge($context->pop())
          ->applyTo($build);
      }

      $content = $this->renderer->renderRoot($build);

      $response
        ->setContent($content)
        ->setAttachments($build['#attached']);

      return $response;
    }

    return $plugin->build();
  }

}
