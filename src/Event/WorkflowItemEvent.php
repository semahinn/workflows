<?php

namespace Snr\Workflows\Event;

use Snr\Workflows\WorkflowItem\WorkflowItemInterface;
use Symfony\Component\EventDispatcher\Event;

class WorkflowItemEvent extends Event {

  /**
   * @var WorkflowItemInterface
   */
  protected $workflowItem;

  public function __construct(WorkflowItemInterface $workflow_item) {
    $this->workflowItem = $workflow_item;
  }

  /**
   * @return WorkflowItemInterface
   */
  public function getWorkflowItem() {
    return $this->workflowItem;
  }
  
}