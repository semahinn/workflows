<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\Access\AccessResultForbidden;

trait CompleteOperationTrait {

  use OperationTrait;

  /**
   * @var bool
   *  Флаг, определяющий что этап "Автоматически завершаемый"
   */
  protected $autoCompleteFlag = false;

  /**
   * {@inheritdoc}
   */
  public function setAutoCompleteFlag(bool $flag) {
    $this->autoCompleteFlag = $flag;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function autoCompleteFlag() {
    return $this->autoCompleteFlag;
  }

  /**
   * {@inheritdoc}
   */
  public function getAutoCompleteOptions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function complete(array $options) {
    $this->perform('Complete', $options);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function canComplete(array $options) {
    return $this->can('Complete', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function isElementReadyForAutoComplete(array $options = []) {
    // Только если этот элемент "Автоматически завершаемый"
    if ($this->autoCompleteFlag() && !($this->canComplete($options) instanceof AccessResultForbidden))
      return true;
    return false;
  }

}