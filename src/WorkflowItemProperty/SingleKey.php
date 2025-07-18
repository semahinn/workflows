<?php

namespace Snr\Workflows\WorkflowItemProperty;

abstract class SingleKey extends Property implements SingleKeyInterface {

  /**
   * {@inheritdoc}
   */
  public function setValue(array $data) {
    $property_key = static::getSingleKey();
    $this->value[$property_key] = '';
    $label = static::getLabel();

    if ($this->isCreated()) {
      if (!$this::conditionToCreate($data))
        throw new \Exception("Для определения \"$label\" необходимо свойство с ключом '$property_key'");
    }

    $reason = '';
    if (!$this->checkSinglePropertyValue($data, $reason))
      throw new \Exception($reason);

    $this->value[$property_key] = $data[$property_key];;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function conditionToCreate(array $data) {
    return array_key_exists(static::getSingleKey(), $data);
  }

  /**
   * {@inheritdoc}
   */
  public static function getKeys() {
    return [static::getSingleKey()];
  }

  /**
   * {@inheritdoc}
   */
  public static abstract function getSingleKey();

  /**
   * @param mixed $data
   * @param $reason
   *
   * @return bool
   */
  protected abstract function checkSinglePropertyValue(&$data, &$reason);

}