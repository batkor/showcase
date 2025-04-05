<?php

declare(strict_types=1);

namespace Drupal\showcase\EventSubscriber;

use Drupal\showcase\Event\ShowcasePrepareVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ShowcaseVariable implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ShowcasePrepareVariableEvent::class => 'prepareVariable',
    ];
  }

  public function prepareVariable(ShowcasePrepareVariableEvent $event): void {
    $data = $event
      ->getPlugin()
      ->getPluginDefinition()['data'];

    $event->setVariables($data);
  }

}
