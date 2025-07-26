<?php

namespace Snr\Workflows;

class Dependency implements DependencyInterface {

  /**
   * @var array
   */
  protected $services = [];

  public function __construct(array $services = []) {
    // workflow_item_manager - экземпляр WorkflowItemManagerInterface
    // user_storage - экземпляр UserStorageInterface
    $this->services = $services;
  }

  /**
   * {@inheritdoc}
   */
  public function getService($id) {
    if (isset($this->services[$id])) {
      return $this->services[$id];
    }
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkflowItemManager() {
    return $this->services['workflow_item_manager'];
  }

  /**
   * {@inheritdoc}
   */
  public function getUserStorage() {
    return $this->services['user_storage'];
  }

}