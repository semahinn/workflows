<?php

namespace Snr\Workflows\WorkflowItemProperty;

trait EntityPropertyTrait {

  /**
   * {@inheritdoc}
   */
  public function getEntityUuid() {
    return $this->getValue()[static::getKeyForEntityUuid()];
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityLabel() {
    return $this->getValue()[$this->getKeyForEntityLabel()];
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityInstance() {
    $entity_uuid = $this->getEntityUuid();
    if (!$entity_uuid) return null;
  }

  /**
   * {@inheritdoc}
   */
  public static function getAllowedEntityInstances() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function getAllowedEntityOptions() {
    $instances = static::getAllowedEntityInstances();
    $options = [];
    foreach ($instances as $instance) {
      $options[$instance->uuid()] = $instance->label();
    }
    return $options;
  }

  /**
   * @param array $options
   *
   * @return bool
   */
  protected function getEntityRequiredFlagFromOptions(array &$options) {
    $required = false;
    $required_key = static::getKeyForEntityUuid() . '_required';
    if (isset($options[$required_key]) &&
      is_bool($options[$required_key]))
      $required = $options[$required_key];
    return $required;
  }

  /**
   * @return string
   */
  public static function getKeyForEntityLabel() {
    return static::getKeyForEntityUuid() . '_label';
  }

  /**
   * {@inheritdoc}
   */
  public static function conditionToCreate(array $data) {
    return array_key_exists(static::getKeyForEntityUuid(), $data);
  }

  /**
   * {@inheritdoc}
   */
  public static function getKeys() {
    return [static::getKeyForEntityUuid(), static::getKeyForEntityLabel()];
  }

}
