<?php

namespace Snr\Workflows\WorkflowItemProperty;

use Snr\Workflows\WorkflowItem\CompleteOperationInterface;

class State extends Property implements StateInterface {

  /**
   * {@inheritdoc}
   */
  public function setValue(array $data) {
    $this->value['state'] = CompleteOperationInterface::STATE_ACTIVE;
    if ($this->isCreated()) {
      if (!$this::conditionToCreate($data))
        throw new \Exception("Для определения \"Состояния\" необходимо свойство с ключом 'state'");
    }
    $this->value['state'] = $data['state'];
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function getStateFromOptions(array &$options) {
    return (!empty($options['state']) && is_string($options['state'])) ?
      $options['state'] : null;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return 'Состояние';
  }

  /**
   * {@inheritdoc}
   */
  public static function conditionToCreate(array $data) {
    return array_key_exists('state', $data);
  }

  /**
   * {@inheritdoc}
   */
  public static function getKeys() {
    return ['state'];
  }

}
