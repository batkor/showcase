<?php

declare(strict_types=1);

namespace Drupal\showcase\EventSubscriber;

use Drupal\Core\Serialization\Yaml;
use Drupal\showcase\Event\ShowcasePrepareVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ShowcaseFileVariable implements EventSubscriberInterface {

  public function __construct(
    protected readonly string $root,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ShowcasePrepareVariableEvent::class => ['prepareVariable', -10],
    ];
  }

  public function prepareVariable(ShowcasePrepareVariableEvent $event): void {
    $data = $event
      ->getPlugin()
      ->getPluginDefinition()['data'];

    if (!\is_string($data)) {
      return;
    }

    if (mb_substr($data, 0, 1) === '/') {
      $path = $this->root . $data;
    }
    else {
      $path = $event
        ->getPlugin()
        ->getProviderDirectory() . '/' . ltrim($data, '/');
    }

    $path = realpath($path);

    if (empty($path)) {
      return;
    }

    if (!file_exists($path)) {
      return;
    }

    $content = file_get_contents($path);

    $variables = match(pathinfo($path, PATHINFO_EXTENSION)) {
      'yml', 'yaml' => Yaml::decode($content),
      'json' => json_decode($content, TRUE),
      default => NULL,
    };

    if (empty($variables)) {
      return;
    }

    foreach ($variables as $key => $value) {
      $event->addVariable($key, $value);
    }

    $event->stopPropagation();
  }

}
