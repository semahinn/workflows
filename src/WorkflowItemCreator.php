<?php

namespace Snr\Workflows;

use Snr\Workflows\Manager\WorkflowItemManagerInterface;
use Snr\Workflows\WorkflowItem\AbstractGroupInterface;
use Snr\Workflows\WorkflowItem\WorkflowItemInterface;

class WorkflowItemCreator implements WorkflowItemCreatorInterface {

  /**
   * @var WorkflowItemManagerInterface
   */
  protected $workflowItemPluginManager;

  public function __construct(WorkflowItemManagerInterface $workflowItemPluginManager) {
    $this->workflowItemPluginManager = $workflowItemPluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstances(array $items_array, array $context = [], AbstractGroupInterface $root = null) {
    $instances = [];
    foreach ($items_array as $item) {
      if (!(isset($item['type']) && is_string($item['type']))) {
        $interface = WorkflowItemInterface::class;
        $message = "Невозможно создать экземпляр этапа/маршрута " .
          "({$interface}), т.к. тип элемента не указан (св-во \"type\")";
        throw new \InvalidArgumentException($message);
      }

      if ($definition = $this->workflowItemPluginManager->getDefinition($item['type'], true)) {
        unset($item['type']);
        $item['context'] = $context;
        if ($root) $item['root_when_create'] = $root;
        $instances[] = $this->workflowItemPluginManager->createInstance(
          $definition['type'], $item);
      }
    }
    return $instances;
  }

}
