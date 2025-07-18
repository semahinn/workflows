<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\PassivateTrait;

/**
 * @WorkflowItem(
 *   id = "if_else",
 *   label = "Условный блок",
 *   description = "Условный блок",
 * )
 */
class IfElseItem extends Group implements IfElseItemInterface {

  use PassivateTrait;

  protected $current_add = 'true_item';
  protected $itemIds = [];

  /**
   * @var callable
   */
  protected $condition_function;

  protected $condition_callback = [];

  public function doCreate(array $data) {
    parent::doCreate($data);
    foreach (['true_item', 'false_item'] as $key)
    {
      if (!empty($data[$key]))
      {
        // TODO: Нужно добавить проверки, что true_item и false_item идентификаторы корректны
        $this->itemIds[$key] = $data[$key];
      }
    }
    if (isset($data['condition_callback']))
    {
      $this->condition_callback = $data['condition_callback'];
      $this->condition_function = function (IfElseItemInterface $item, $context) {
        return call_user_func($item->condition_callback, $item, $context);
      };
    }
    // IfElseItem (Условие) - всегда параллельная группа,
    //  т.к. если выполняется true_item или false_item - то условие должно тоже завершиться
    $this->groupType = AbstractGroupInterface::TYPE_PARALLEL;
    $this->groupOperator = 'or';
  }

  /**
   * {@inheritdoc}
   */
  public function setGroupType(string $type, array $options = []) {
    // IfElseItem (Условие) - всегда параллельная группа
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setCondition($function)
  {
    if (is_callable($function))
      $this->condition_function = $function;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setTrueItem(WorkflowItemInterface $item)
  {
    $this->current_add = 'true_item';
    if (isset($this->itemIds[$this->current_add]))
      $this->removeItem($this->itemIds[$this->current_add]);
    $this->addItem($item);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setFalseItem(WorkflowItemInterface $item)
  {
    $this->current_add = 'false_item';
    if (isset($this->itemIds[$this->current_add]))
      $this->removeItem($this->itemIds[$this->current_add]);
    $this->addItem($item);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTrueItem()
  {
    if (isset($this->itemIds['true_item']))
      return $this->getItem($this->itemIds['true_item']);
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getFalseItem()
  {
    if (isset($this->itemIds['false_item']))
      return $this->getItem($this->itemIds['false_item']);
    return null;
  }

  protected function doAddItem(array &$options) {
    /**
     * @var $item WorkflowItemInterface
     */
    $item = $options['item'];
    //parent::doAddItem($options);
    $this->itemIds[$this->current_add] = $item->id();
    $this->current_add = 'true_item';
  }

  public function getCurrentElement(array $options = []) {

    if ($this->condition($options))
    {
      $item = $this->getTrueItem();
    }
    else
    {
      $item = $this->getFalseItem();
    }

    if ($item->getState() != CompleteOperationInterface::STATE_COMPLETED)
      return $item;

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function condition(array $options)
  {
    $function = $this->condition_function;
    if (!is_callable($function))
      throw new \Exception('Невозможно выполнить элемент с условием (экземпляр ' . static::class . ') с названием "' .
        $this->getLabel() . '" т.к не установлена функция для условия (метод setCondition())');
    return $function($this, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $data = parent::toArray();
    foreach (['true_item' => $this->getTrueItem()->id(), 'false_item' => $this->getFalseItem()->id()] as $key => $item_id)
    {
      $data[$key] = $item_id;
    }

    $serializable_callback = [];
    foreach ($this->condition_callback as $value)
    {
      if (is_object($value))
        $serializable_callback[] = get_class($value);
      else
        $serializable_callback[] = $value;
    }

    $data['condition_callback'] = $serializable_callback;
    return $data;
  }

}