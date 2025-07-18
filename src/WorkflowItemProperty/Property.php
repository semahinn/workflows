<?php

namespace Snr\Workflows\WorkflowItemProperty;

abstract class Property implements PropertyInterface {

  /**
   * @var array
   */
  protected $value;

  /**
   * @var array
   */
  protected $initValue;

  final protected function __construct() {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(array $data) {
    $instance = new static();
    $instance->setValue($data);
    $instance->initValue = $instance->value;
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public abstract function setValue(array $data);

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getInitValue() {
    return $this->initValue;
  }

  /**
   * {@inheritdoc}
   */
  public function isCreated() {
    return isset($this->initValue);
  }

  /**
   * {@inheritdoc}
   */
  public function isChanged(string $key = null) {
    if ($key) {
      if (!in_array($key, static::getKeys())) {
        $class = static::class;
        $cut = implode(', ', static::getKeys());
        throw new \InvalidArgumentException("В качестве параметра метод $class::isChanged " .
          "должен принимать только строки $cut или null");
      }
      return static::compare($this->getValue(), $this->getInitValue(), $key);
    }
    return !empty(static::compare($this->getValue(), $this->getInitValue()));
  }

  /**
   * {@inheritdoc}
   */
  public function changesSchema(PropertyInterface $new = null) {
    $schema = [];
    if ($new) {
      $keys = static::compare($this->getValue(), $new_value = $new->getValue());
      foreach ($keys as $key) {
        $schema[$key] = $new_value[$key];
      }
    }
    else {
      $keys = static::compare($value = $this->getValue(), $this->getInitValue());
      foreach ($keys as $key) {
        $schema[$key] = $value[$key];
      }
    }
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function compare(array $l_value, array $r_value, string $key = null) {
    $diffs = [];
    if ($key) {
      if ($l_value[$key] != $r_value[$key]) {
        $diffs[] = $key;
      }
    }
    else {
      foreach (static::getKeys() as $key) {
        if ($l_value[$key] != $r_value[$key]) {
          $diffs[] = $key;
        }
      }
    }
    return $diffs;
  }

}
