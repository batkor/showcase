<?php declare(strict_types = 1);

namespace Drupal\showcase\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $static = new static($configuration, $plugin_id, $plugin_definition);
    $static->pluginManagerShowcase = $container->get('plugin.manager.showcase');

    return $static;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $id = str_replace('showcase_block:', '', $this->getPluginId());
    /** @var \Drupal\showcase\ShowcasePluginInterface $plugin */
    $plugin = $this
      ->pluginManagerShowcase
      ->createInstance($id);

    $form['message'] = [
      '#markup' => $this->t('Use template "@path" to render block', [
        '@path' => $plugin->getTemplatePath(),
      ]),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $id = str_replace('showcase_block:', '', $this->getPluginId());
    /** @var \Drupal\showcase\ShowcasePluginInterface $plugin */
    $plugin = $this
      ->pluginManagerShowcase
      ->createInstance($id);
    $build['content'] = $plugin->build();

    return $build;
  }

}
