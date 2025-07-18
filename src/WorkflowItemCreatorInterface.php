<?php

namespace Snr\Workflows;

use Snr\Workflows\WorkflowItem\AbstractGroupInterface;
use Snr\Workflows\WorkflowItem\WorkflowItemInterface;

interface WorkflowItemCreatorInterface {

  /**
   * @param array $items_array
   * @param array $context
   * @param AbstractGroupInterface|null $root
   *
   * @return WorkflowItemInterface
   * @throws \Exception
   */
  public function createInstances(array $items_array, array $context = [], AbstractGroupInterface $root = null);

}
