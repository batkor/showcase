<?php

declare(strict_types=1);

namespace Drupal\showcase\EventSubscriber;

use Drupal\Core\Utility\CallableResolver;
use Drupal\showcase\Event\ShowcasePrepareVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

final class ShowcaseCallableVariable implements EventSubscriberInterface {

  public function __construct(
    protected readonly ?CallableResolver $callableResolver,
    protected readonly ?ArgumentResolverInterface $argumentResolver,
    protected readonly ?RequestStack $requestStack,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ShowcasePrepareVariableEvent::class => ['prepareVariable', -100],
    ];
  }

  /**
   * @throws \ReflectionException|\InvalidArgumentException
   */
  public function prepareVariable(ShowcasePrepareVariableEvent $event): void {
    $plugin = $event->getPlugin();
    $data = $plugin->getPluginDefinition()['data'];

    if (!\is_string($data)) {
      return;
    }

    try {
      $dataCallable = $this->callableResolver->getCallableFromDefinition($data);
    }
    catch (\InvalidArgumentException $e) {
      throw new \InvalidArgumentException(\sprintf('Not callable data on plugin %s', $plugin->getPluginId()), 0, $e);
    }

    if (\is_array($dataCallable) && \method_exists(...$dataCallable)) {
      $controllerReflector = new \ReflectionMethod(...$dataCallable);
    }
    elseif (\is_string($dataCallable) && \str_contains($dataCallable, '::')) {
      $controllerReflector = new \ReflectionMethod(...\explode('::', $dataCallable, 2));
    }
    else {
      $controllerReflector = new \ReflectionFunction($dataCallable(...));
    }

    $arguments = $this
      ->argumentResolver
      ->getArguments($this->requestStack->getCurrentRequest(), $dataCallable, $controllerReflector);

    $event->setVariables($dataCallable(...$arguments));
  }

}
