<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\Access\AccessResult;

/**
 * Реализует методы для работы со свойством "Владелец" (Автор)
 *
 * @see ItemWithOwnerInterface
 */
trait ItemWithOwnerTrait {

  use ItemWithPropertiesTrait;

  /**
   * {@inheritdoc}
   */
  public function getOwnerUuid() {
    return isset($this->properties['owner_uuid_property']) ?
      $this->properties['owner_uuid_property']->getValue()['owner_uuid'] : null;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return isset($this->properties['owner_uuid_property']) ?
      $this->properties['owner_uuid_property']->getEntityInstance() : null;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerUuid(string $owner_uuid, array $options = []) {
    $options['owner_uuid'] = $owner_uuid;
    return $this->perform('Edit', $options);
  }

  /**
   * Типовая проверка доступа, можно в таком виде использовать
   *  в своих методах canOperation элементов (не обязательно), но
   *  иногда лучше описать свою уникальную логику
   *
   * @param array $options
   *
   * @param array $access_results
   */
  protected function checkOwnerUuid(array &$options, array &$access_results) {
    if ($this->isBuildOnly()) return;
    $properties = $this->foundPropertiesWhenPerform($options, ['owner_uuid_property']);
    if (isset($properties['owner_uuid_property'])) {
      // Автора (владельца) можно установить только если этап еще не сохранён
      if (!$this->isNew()) {
        $cut = $this->getMessagePart1();
        $label = $this->getPropertySetting('owner_uuid_property', 'label');
        $user_message = "Для элемента $cut невозможно установить \"$label\"" .
          ": он устанавливается автоматически при создании";
        $access_results['default_access'] = AccessResult::forbidden($user_message);
        return;
      }
    }
  }
}