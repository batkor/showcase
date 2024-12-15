<?php

declare(strict_types=1);

namespace Drupal\showcase;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for showcase plugins.
 */
interface ShowcasePluginInterface extends PluginInspectionInterface {

  /**
   * Returns plugin label.
   */
  public function label(): string;

  /**
   * Returns renderable array.
   *
   * @return array
   */
  public function build(): array;

  /**
   * Render plugin.
   *
   * @return string
   *   The string contains html.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Throwable
   * @throws \Twig\Error\RuntimeError
   */
  public function render(): string;

  /**
   * Returns plugin variables list for use on template.
   *
   * @throws \ReflectionException
   */
  public function getVariables(): array;

  /**
   * Check access to plugin.
   */
  public function access(): bool;

  /**
   * Returns relative path to template file.
   */
  public function getTemplatePath(): string;

  /**
   * Returns relative path to template directory.
   */
  public function getTemplateDirectory(): string;

  /**
   * Returns relative path to provider directory.
   */
  public function getProviderDirectory(): string;

  /**
   * Returns TRUE if plugin need override full page.
   */
  public function isHtml(): bool;

}
