<?php

namespace Drupal\Tests\showcase\Functional;

use Drupal\Tests\block\Traits\BlockCreationTrait;
use Drupal\Tests\BrowserTestBase;

final class PluginRenderTest extends BrowserTestBase {

  use BlockCreationTrait {
    placeBlock as drupalPlaceBlock;
  }

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['showcase_example', 'block', 'system'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this
      ->drupalPlaceBlock('showcase_block:showcase_example::showcase-example-block.html.twig', [
        'region' => 'header',
      ]);
  }

  /**
   * Tests a plugin provides route.
   */
  public function testRoutePlugin(): void {
    $this->drupalGet('/example/twig');
    $this->assertSession()->responseContains('showcase_example path');
    // Check attach assets files.
    $this->assertSession()->responseContains('assets/showcase_example/path.css');
    $this->assertSession()->responseContains('bootstrap@5.0.2/dist/css/bootstrap.min.css');
  }

  /**
   * Tests a plugin provides block.
   */
  public function testBlockPlugin(): void {
    $this->drupalGet('/');
    $this->assertSession()->responseContains('Example block');
  }

}
