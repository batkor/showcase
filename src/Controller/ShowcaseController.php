<?php declare(strict_types=1);

namespace Drupal\showcase\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\showcase\ShowcasePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The controller for download lesson archives.
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
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('plugin.manager.showcase'),
    );
  }

  /**
   * Returns download response.
   */
  public function __invoke(Request $request) {
    /** @var \Symfony\Component\Routing\Route $route */
    $route = $request->get('_route_object');
    $def = $route->getOption('showcase');
    /** @var \Drupal\showcase\ShowcasePluginInterface $plugin */
    $plugin = $this
      ->pluginManagerShowcase
      ->createInstance($def['id']);

    return $plugin->build();
  }

}
