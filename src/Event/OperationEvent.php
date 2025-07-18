<?php

namespace Snr\Workflows\Event;

use Snr\Workflows\WorkflowItem\OperationInterface;
use Snr\Workflows\WorkflowItem\WorkflowItemInterface;

/**
 * Событие, возникающее при выполнении любого
 * действия с этапом рабочего процесса
 *
 * @see OperationInterface::perform()
 * @see OperationInterface::can()
 */
class OperationEvent extends WorkflowItemEvent {

  const EVENT_NAME = 'workflow_item.operation';

  /**
   * @var string
   *  Тип действия с этапом ('do' или 'can')
   */
  protected $operationEventType;

  /**
   * @var string
   *  Название действия
   */
  protected $operation;

  /**
   * @var string
   *  Дополнительные префикс ('pre' или 'post')
   */
  protected $prefix;

  /**
   * @var array
   *  Необходимые данные
   */
  protected $options;

  /**
   * @param WorkflowItemInterface $workflow_item
   *  Этап рабочего процесса
   *
   * @param string $operation_event_type
   *  Тип действия с этапом ('do' или 'can')
   *
   * @param string $operation
   *  Название действия
   *
   * @param string $prefix
   *  Дополнительные префикс ('pre' или 'post')
   *
   * @param array $options
   *  Необходимые данные
   */
  public function __construct(WorkflowItemInterface $workflow_item,
                              string $operation_event_type,
                              string $operation,
                              string $prefix,
                              array &$options = []) {
    $this->operationEventType = $operation_event_type;
    $this->operation = $operation;
    $this->prefix = $prefix;
    $this->options = $options;
    parent::__construct($workflow_item);
  }

  /**
   * Возвращает тип действия с этапом, для которого используется событие:
   * 'can' - проверка на доступ к выполнению действия
   * 'do' - непосредственно выполнение действия
   *
   * @return string
   *
   * @see OperationInterface::perform()
   * @see OperationInterface::can()
   */
  public function getOperationEventType() {
    return $this->operationEventType;
  }

  /**
   * @return string
   *  Название действия
   */
  public function getOperation() {
    return $this->operation;
  }

  /**
   * @return string
   *  Дополнительные префикс ('pre' или 'post')
   */
  public function getPrefix() {
    return $this->prefix;
  }

  /**
   * @return array
   *  Необходимые данные
   */
  public function getOptions() {
    return $this->options;
  }
  
}