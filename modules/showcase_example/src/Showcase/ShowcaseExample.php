<?php

declare(strict_types=1);

namespace Drupal\showcase_example\Showcase;

final class ShowcaseExample {

  public function __invoke(): array {
    return [
      'text' => 'ShowcaseExample callable',
    ];
  }

}
