<?php

namespace Drupal\showcase\Discovery;

/**
 * Iterates twig files in a directory.
 */
class TwigDirectoryIterator extends \RegexIterator {

  /**
   * Constructors.
   *
   * @param string $path
   *   The path to scan.
   */
  public function __construct($path) {
    $iterator = new \RecursiveDirectoryIterator($path);
    $iterator = new \RecursiveIteratorIterator($iterator);

    parent::__construct($iterator, '/\.html\.twig$/i');
  }

}
