<?php

declare(strict_types=1);

namespace Drupal\showcase\EventSubscriber;

use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\showcase\Controller\ShowcaseController;
use Drupal\showcase\ShowcasePluginInterface;
use Drupal\showcase\ShowcasePluginManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Route;

/**
 * Route subscriber.
 */
final class ShowcaseRouteSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a ShowcaseRouteSubscriber object.
   */
  public function __construct(
    protected readonly ShowcasePluginManager $pluginManagerShowcase,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[RoutingEvents::ALTER] = ['alterRoutes', -500];
    $events[KernelEvents::REQUEST] = ['alterRequest'];

    return $events;
  }

  /**
   * Alter request.
   */
  public function alterRequest(RequestEvent $event): void {
    $route = $event->getRequest()->get('_route_object');
    \assert($route instanceof Route);

    $def = $route->getOption('showcase');

    if (empty($def)) {
      return;
    }

    $plugin = $this
      ->pluginManagerShowcase
      ->createInstance($def['id']);

    if (!$plugin instanceof ShowcasePluginInterface) {
      return;
    }

    if (!$plugin->access()) {
      throw new NotFoundHttpException();
    }
  }

  /**
   * Override and add routes.
   */
  public function alterRoutes(RouteBuildEvent $event): void {
    $collection = $event->getRouteCollection();
    $defs = $this
      ->pluginManagerShowcase
      ->getDefinitions();
    $foundIds = [];

    foreach ($collection as $route) {
      foreach ($defs as $id => $def) {
        if ($route->getPath() !== $def['path']) {
          continue;
        }

        $foundIds[] = $id;
        $route->setOption('origin_controller', $route->getDefault('_controller'));
        $route->setDefault('_controller', ShowcaseController::class);
        $requirements = $route->getRequirements();
        $route->setOption('showcase', $def);

        foreach ($def['requirements'] as $key => $requirement) {
          $requirements[$key] = $requirement;
        }

        $route->addRequirements($requirements);
      }
    }

    foreach ($defs as $id => $def) {
      if (\in_array($id, $foundIds)) {
        continue;
      }

      if (empty($def['path'])) {
        continue;
      }

      $requirements = $def['requirements'] + [
        '_access' => 'TRUE',
      ];

      $defaults = [
        '_controller' => ShowcaseController::class,
      ];

      if (!empty($def['label'])) {
        $defaults['_title'] = $def['label'];
      }

      $options = [
        'showcase' => $def,
      ];

      $route = new Route($def['path'], $defaults, $requirements, $options);
      $name = \preg_replace('/[-.:?*<>"\'\/\\\\]/', '_', $id);
      $collection->add($name, $route);
    }
  }

}
