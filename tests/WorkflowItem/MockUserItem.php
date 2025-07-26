<?php

namespace Snr\Workflows\Tests\WorkflowItem;

use Snr\Plugin\Exception\PluginException;
use Snr\Workflows\DependencySingleton;
use Snr\Workflows\WorkflowItem\UserItem;
use Snr\Workflows\WorkflowItem\WorkflowItemInterface;

/**
 * @WorkflowItem(
 *   id = "mock_user_item",
 *   label = "Этап пользователя",
 *   description = "Описывает задачу пользователя",
 * )
 */
class MockUserItem extends UserItem {

  /**
   * @param array $data
   * @return WorkflowItemInterface
   * @throws PluginException
   */
  public static function create(array $data) {
    $workflow_item_manager = DependencySingleton::getInstance()->getWorkflowItemManager();
    $definition = $workflow_item_manager->getDefinitionByPluginClass(static::class);
    return $workflow_item_manager->createInstance($definition['id'], $data);
  }

}