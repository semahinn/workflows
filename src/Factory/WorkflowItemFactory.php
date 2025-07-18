<?php

namespace Snr\Workflows\Factory;

use Snr\Plugin\Factory\DefaultFactory;
use Snr\Workflows\WorkflowItem\WorkflowItemInterface;

class WorkflowItemFactory extends DefaultFactory implements WorkflowItemFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    $plugin_definition = $this->discovery->getDefinition($plugin_id);
    $plugin_class = static::getPluginClass($plugin_id, $plugin_definition, $this->interface);

    $this->preCreate($plugin_class, $configuration);
    $instance = $this->doCreate($plugin_class, $configuration);
    $this->postCreate($instance, $configuration);

    return $instance;
  }
  
  /**
   * @param string $plugin_class
   * @param array $configuration
   *
   * @return void
   */
  protected function preCreate(string $plugin_class, array &$configuration) {
    if (method_exists($plugin_class, 'preCreate')) {
      $plugin_class::preCreate($configuration);
    }
  }
  
  /**
   * @param string $plugin_class
   * @param array $configuration
   *
   * @return WorkflowItemInterface
   */
  protected function doCreate(string $plugin_class, array $configuration) {
    $instance = new $plugin_class($configuration, $this);
    if (method_exists($instance, 'doCreate')) {
      $instance->doCreate($configuration);
    }
    return $instance;
  }
  
  /**
   * @param WorkflowItemInterface $instance
   * @param array $configuration
   *
   * @return void
   */
  protected function postCreate(WorkflowItemInterface $instance, array $configuration) {
    if (method_exists($instance, 'postCreate')) {
      $instance->postCreate($configuration);
    }
  }
  
}