<?php

namespace Snr\Workflows\Event;

use Snr\Workflows\WorkflowItem\WorkflowItemInterface;

class GetAllowedPluginsEvent extends WorkflowItemEvent {

  const EVENT_NAME = 'workflow_item.get_allowed_items';

  /**
   * @var array
   */
  protected $allowed;

  public function __construct(WorkflowItemInterface $workflow_item) {
    parent::__construct($workflow_item);
    $plugin_definitions = $workflow_item->getPluginManager()->getDefinitions();
    foreach ($plugin_definitions as $definition)
      $this->allowed[$definition['id']] = $definition['id'];
  }

  /**
   * @return array
   */
  public function getAllowed() {
    return $this->allowed;
  }

  /**
   * @param array $allowed
   *
   * @return static
   */
  public function setAllowed(array $allowed) {
    $this->allowed = array_combine($allowed, $allowed);
    return $this;
  }

}