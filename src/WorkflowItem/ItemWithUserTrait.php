<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\Access\AccessResult;

/**
 * Реализует методы для работы со свойством "Пользователь" (user_uuid)
 * (Пользователь этого этапа)
 */
trait ItemWithUserTrait {

  use ItemWithUserContextTrait;
  use ItemWithPropertiesTrait;

  /**
   * {@inheritdoc}
   */
  public function getUserUuid() {
    return isset($this->properties['user_uuid_property']) ?
      $this->properties['user_uuid_property']->getValue()['user_uuid'] : null;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserInstance() {
    return isset($this->properties['user_uuid_property']) ?
      $this->properties['user_uuid_property']->getEntityInstance() : null;
  }

  /**
   * {@inheritdoc}
   */
  public function setUserUuid(string $uuid, array $options = []) {
    $options['user_uuid'] = $uuid;
    return $this->perform('Edit', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function userFromContextIsRealUser() {
    $user = $this->getUserFromContext();
    if ($user->uuid() == $this->getUserUuid())
      return true;
    return false;
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
  protected function checkUserUuid(array &$options, array &$access_results) {
    if ($this->isBuildOnly()) return;
    $properties = $this->foundPropertiesWhenPerform($options, ['user_uuid_property']);
    if (isset($properties['user_uuid_property'])) {
      $cut = $this->getMessagePart1();
      if ($this->getState() == WorkflowItemInterface::STATE_COMPLETED) {
        $user_message = "Для этапа $cut невозможно назначить пользователя, т.к. этап уже завершён";
        $access_results['default_access'] = AccessResult::forbidden($user_message);
        return;
      }
      if (!$this->isNew()) {
        $user_message = "Для этапа $cut невозможно назначить пользователя, т.к. этап уже зарегистрирован";
        $access_results['default_access'] = AccessResult::forbidden($user_message);
        return;
      }
      if (!$this->isNew() && $this->getItems()) {
        $user_message = "Для этапа $cut невозможно назначить пользователя, т.к. этап уже содержит вложенные элементы";
        $access_results['default_access'] = AccessResult::forbidden($user_message);
        return;
      }
    }
  }

}