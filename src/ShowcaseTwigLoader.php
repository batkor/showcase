<?php

declare(strict_types=1);

namespace Drupal\showcase;

use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

/**
 * Loads templates from the filesystem.
 */
final class ShowcaseTwigLoader extends FilesystemLoader {

  /**
   * {@inheritdoc}
   */
  protected function findTemplate($name, $throw = TRUE) {
    $allowed = FALSE;
    $absolutePath = "/$name";

    foreach (ShowcasePluginManager::getArbitraryDirectories() as $directories) {
      foreach ($directories as $directory) {
        $directory = \realpath($directory);

        if (\str_starts_with($absolutePath, $directory)) {
          $allowed = TRUE;
          break;
        }
      }
    }

    if (!$allowed) {
      return NULL;
    }

    if (!\str_ends_with($name, '.html.twig')) {
      if (!$throw) {
        return NULL;
      }

      $extension = \pathinfo($name, \PATHINFO_EXTENSION);

      throw new LoaderError(\sprintf('Template %s has an invalid file extension (%s). Allowed only file ending ".html.twig".', $name, $extension));
    }

    return $this->cache[$name] = $absolutePath;
  }

}
