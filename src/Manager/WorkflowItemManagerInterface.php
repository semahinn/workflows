<?php

namespace Snr\Workflows\Manager;

use Snr\Workflows\WorkflowItem\AbstractGroupInterface;
use Snr\Workflows\WorkflowItem\WorkflowItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Snr\Plugin\Manager\ByPluginClassInterface;
use Snr\Plugin\Manager\PluginManagerInterface;

interface WorkflowItemManagerInterface extends PluginManagerInterface, ByPluginClassInterface {

  /**
   * @return EventDispatcherInterface
   */
  public function getEventDispatcher();

  /**
   * @param array $items_array
   * @param array $context
   * @param AbstractGroupInterface|null $root
   *
   * @return WorkflowItemInterface[]
   * @throws \Exception
   */
  public function createInstances(array $items_array, array $context = [], AbstractGroupInterface $root = null);

}