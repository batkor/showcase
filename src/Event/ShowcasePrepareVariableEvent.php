<?php

declare(strict_types=1);

namespace Drupal\showcase\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\showcase\ShowcasePluginInterface;

final class ShowcasePrepareVariableEvent extends Event {

  protected array $variables;

  public function __construct(
    protected readonly ShowcasePluginInterface $plugin,
  ) {}

  public function getPlugin(): ShowcasePluginInterface {
    return $this->plugin;
  }

  public function getVariables(): array {
    return $this->variables;
  }

  public function setVariables(array $variables): void {
    $this->variables = $variables;
  }

  public function addVariable(string $key, $value): void {
    $this->variables[$key] = $value;
  }

}
