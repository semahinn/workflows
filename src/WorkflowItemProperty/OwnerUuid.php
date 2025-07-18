<?php

namespace Snr\Workflows\WorkflowItemProperty;

class OwnerUuid extends Property implements OwnerUuidInterface {

  use EntityPropertyTrait;

  /**
   * {@inheritdoc}
   */
  public function setValue(array $data) {
    $uuid_key = static::getKeyForEntityUuid();
    $this->value[$uuid_key] = '';
    $label_key = static::getKeyForEntityLabel();
    $this->value[$label_key] = '';
    $label = static::getLabel();

    if ($this->isCreated()) {
      if (!$this::conditionToCreate($data))
        throw new \Exception("Для определения \"$label\" необходимо свойство с ключом '$uuid_key'");
    }

    if (isset($data[$label_key]) && is_string($data[$label_key]))
      $this->value[$label_key] = $data[$label_key];

    $instances = [];
    foreach ($this->getAllowedEntityInstances() as $instance)
      $instances[$instance->uuid()] = $instance;

    $current = ($instances[$data[$uuid_key]] ?? NULL);
    if (static::getEntityRequiredFlagFromOptions($data) && !$current) {
      $reason = "\"$label\" ($uuid_key) должна быть одним из допустимых значений";
      throw new \Exception($reason);
    }

    $this->value[$uuid_key] = $data[$uuid_key];
    if ($current) $this->value[$label_key] = $current->label();

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return 'Владелец (Автор)';
  }

  /**
   * {@inheritdoc}
   */
  public static function getEntityType() {
    return 'user';
  }

  /**
   * {@inheritdoc}
   */
  public static function getEntityBundle() {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public static function getKeyForEntityUuid() {
    return 'owner_uuid';
  }

}
