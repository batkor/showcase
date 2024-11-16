<?php

namespace Drupal\showcase\Discovery;

use Drupal\Component\Discovery\DiscoverableInterface;
use Drupal\Component\FileCache\FileCacheFactory;
use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Component\Plugin\Discovery\DiscoveryTrait;

class ShowcaseDiscovery implements DiscoveryInterface, DiscoverableInterface {

  use DiscoveryTrait;

  /**
   * The directories list for find templates.
   */
  protected array $directories;

  /**
   * {@inheritdoc}
   */
  public function __construct($directories) {
    $this->directories = $directories;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $plugins = $this->findAll();
    $definitions = [];

    foreach ($plugins as $list) {
      foreach ($list as $id => $definition) {
        $definitions[$id] = $definition;
      }
    }

    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function findAll() {
    $all = [];
    $files = $this->findFiles();
    $pathFiles = array_keys($files);
    $cache = FileCacheFactory::get('showcase:twig:front_matter');

    foreach ($cache->getMultiple($pathFiles) as $file => $data) {
      $all[$data['provider']][$data['id']] = $data;
      unset($files[$file]);
    }

    foreach ($files as $file => $providerData) {
      try {
        $parseResult = ShowcaseFrontMatter::create(file_get_contents($file))->parse();
      }
      catch (\Exception $e) {
        throw new \Exception(sprintf('Failed parse front matter on "%s" template. Parse error: %s', $file, $e->getMessage()));
      }

      if (empty($parseResult['data'])) {
        continue;
      }

      $data = [
        'id' => $providerData['provider'] . ':' . str_replace(DIRECTORY_SEPARATOR, ':', $providerData['relative_path']),
        'provider' => $providerData['provider'],
        'template_directory' => $providerData['directory'],
        'source_file' => $file,
      ];
      $all[$data['provider']][$data['id']] = $data + $parseResult['data'];
      $cache->set($file, $all[$data['provider']][$data['id']]);
    }

    return $all;
  }

  /**
   * Returns an array of file paths, keyed by provider.
   */
  public function findFiles(): array {
    $files = [];

    foreach ($this->directories as $provider => $directories) {
      foreach ($directories as $directory) {
        if (is_dir($directory)) {
          $iterator = new TwigDirectoryIterator($directory);

          /** @var \SplFileInfo $fileInfo */
          foreach ($iterator as $fileInfo) {
            $absolutePath = realpath($fileInfo->getPathname());
            $files[$absolutePath] = [
              'provider' => $provider,
              'directory' => realpath($directory),
              'relative_path' => str_replace($directory, '', $fileInfo->getPathname()),
            ];
          }
        }
      }
    }

    return $files;
  }

}
