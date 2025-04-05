<?php

declare(strict_types=1);

namespace Drupal\showcase\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('plugin.manager.showcase'),
    );
  }

  /**
   * Main callback.
   */
  public function __invoke(Request $request): string|array {
    $route = $request->get('_route_object');
    \assert($route instanceof Route);

    $def = $route->getOption('showcase');
    $plugin = $this
      ->pluginManagerShowcase
      ->createInstance($def['id']);

    \assert($plugin instanceof ShowcasePluginInterface);

    return $plugin->isHtml() ? new Response($plugin->render())
      : $plugin->build();
  }

}
