<?php

namespace Drupal\showcase_example\Showcase;

class ShowcaseExample {

  public function __invoke() {
   return [
     'text' => 'ShowcaseExample callable',
   ];
  }

}
