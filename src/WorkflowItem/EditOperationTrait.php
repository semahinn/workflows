<?php

namespace Snr\Workflows\WorkflowItem;

trait EditOperationTrait {

  use OperationTrait;

  /**
   * {@inheritdoc}
   */
  public function edit(array $options) {
    $this->perform('Edit', $options);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function canEdit(array $options) {
    return $this->can('Edit', $options);
  }
  
}