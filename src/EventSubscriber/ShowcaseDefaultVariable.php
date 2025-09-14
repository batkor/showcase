<?php

declare(strict_types=1);

namespace Drupal\showcase\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\showcase\Event\ShowcasePrepareVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class ShowcaseDefaultVariable implements EventSubscriberInterface {

  public function __construct(
    protected readonly ?RouteMatchInterface $routeMatch,
    protected readonly ?RequestStack $requestStack,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ShowcasePrepareVariableEvent::class => 'prepareVariable',
    ];
  }

  public function prepareVariable(ShowcasePrepareVariableEvent $event): void {
    foreach ($this->routeMatch->getParameters() as $key => $value) {
      $event->addVariable($key, $value);
    }

    $event->addVariable('query', $this->requestStack->getCurrentRequest()->query->all());
  }

}
