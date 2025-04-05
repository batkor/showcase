<?php

declare(strict_types=1);

namespace Drupal\showcase\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\showcase\ShowcasePluginInterface;
use Drupal\showcase\ShowcasePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a showcase block.
 *
 * @Block(
 *   id = "showcase_block",
 *   admin_label = @Translation("Showcase block"),
 *   deriver = "\Drupal\showcase\Plugin\Derivative\ShowcaseBlockDerivative",
 * )
 */
final class ShowcaseBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Showcase plugin manager.
   */
  protected ?ShowcasePluginManager $pluginManagerShowcase;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $static = new self($configuration, $plugin_id, $plugin_definition);
    $static->pluginManagerShowcase = $container->get('plugin.manager.showcase');

    return $static;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['message'] = [
      '#markup' => $this->t('Use template "@path" to render block', [
        '@path' => $this->getPluginInstance()->getTemplatePath(),
      ]),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build['content'] = $this
      ->getPluginInstance()
      ->build();

    return $build;
  }

  public function getPluginInstance(): ShowcasePluginInterface {
    $id = \str_replace('showcase_block:', '', $this->getPluginId());
    $plugin = $this
      ->pluginManagerShowcase
      ->createInstance($id);

    \assert($plugin instanceof ShowcasePluginInterface);

    return $plugin;
  }

}
