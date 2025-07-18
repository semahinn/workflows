<?php

namespace Snr\Workflows\WorkflowItem;

/**
 * @WorkflowItem(
 *   id = "code",
 *   label = "Блок кода",
 *   description = "Элемент для выполнения блока кода",
 * )
 */
class Code extends WorkflowItem implements CodeInterface
{
  /**
   * @see Group::$autoCompleteFlag
   * @see CompleteOperationInterface::autoCompleteFlag()
   */
  protected $autoCompleteFlag = true;

  /**
   * @var callable
   */
  protected $function;

  protected $callback = [];

  /**
   * @var mixed
   */
  protected $result;

  /**
   * {@inheritdoc}
   */
  public function doCreate(array $data)
  {
    parent::doCreate($data);
    if (isset($data['callback'])) {
      $this->callback = $data['callback'];
      $this->function = function (CodeInterface $item, $context) {
        call_user_func($item->callback, $item, $context);
      };
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getResult()
  {
    return $this->result;
  }

  /**
   * {@inheritdoc}
   */
  public function setFunction($function)
  {
    if (is_callable($function))
      $this->function = $function;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function doCode(array $context)
  {
    $function = $this->function;
    if (!is_callable($function))
      throw new \Exception('Невозможно выполнить элемент с кодом (экземпляр ' . static::class . ') с названием "' .
        $this->getLabel() . '" т.к не установлена функция для исполнения (метод setFunction())');
    return $function($this, $context);
  }

  /**
   * {@inheritdoc}
   */
  protected function doOperation(string $operation, array &$options) {
    // Операция "Complete" (Завершение элемента)
    if ($operation == 'Complete')
      $this->doCode($options);
    parent::doOperation($operation, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function toArray()
  {
    $data = parent::toArray();
    $serializable_callback = [];
    foreach ($this->callback as $value)
    {
      if (is_object($value))
        $serializable_callback[] = get_class($value);
      else
        $serializable_callback[] = $value;
    }

    $data['callback'] = $serializable_callback;
    return $data;
  }

}