<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\Access\AccessResult;
use Snr\Workflows\WorkflowItemProperty\OwnerUuid;
use Snr\Workflows\WorkflowItemProperty\UserUuid;

/**
 * Описывает ЭТАП ПОЛЬЗОВАТЕЛЯ
 *
 * @see UserItemInterface
 */
abstract class UserItem extends Group implements UserItemInterface {

  use ItemWithUserTrait;
  use ItemWithOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function autoCompleteFlag() {
    // Всегда false
    return false;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterDataWhenCreate(array &$data) {
    parent::alterDataWhenCreate($data);
    // "Пользователь" (Пользователь, с которым ассоциирован этот элемент) (user_uuid)
    $user_uuid = null;
    if (isset($data['user_uuid']) && is_string($data['user_uuid']))
      $user_uuid = $data['user_uuid'];
    if (!$this->isNew() && !$user_uuid) {
      $type_definition = $this->getPluginDefinition();
      // "свойство \"Пользователь\" ('user_uuid') не установлено или не является строкой"
      $user_message = "Не удалось инициализировать существующий экземпляр типа \"{$type_definition['label']}\"" .
        ", т.к. свойство \"Пользователь\" не установлено";
      throw new \InvalidArgumentException($user_message);
    }

    // "Владелец" (Автор) (owner_uuid)
    // Устанавливается автоматически, если не указано напрямую
    if (!isset($data['owner_uuid']) || !is_string($data['owner_uuid'])) {
      $data['owner_uuid'] = $this->getUserFromContext()->uuid();
    }
  }

  // Это необходимо, чтобы использовался метод doIt из класса WorkflowItem
  /**
   * {@inheritdoc}
   */
  protected function doIt(string $operation, array &$options) {
    return parent::doIt($operation, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    $user = $this->getUserInstance();
    if (!$user) return parent::getLabel();
    return $user->getDisplayName();
  }

  /**
   * {@inheritdoc}
   */
  public function isBlocked() {
    // Считаем этап заблокированным, если он был создан,
    //  но для него отсутствует пользователь (user_uuid)
    if (!$this->isNew() && !$this->getUserInstance())
      return true;
    return false;
  }

  /**
   * {@inheritdoc}
   */
  protected function canOperation(string $operation, array &$options, array &$access_results) {
    parent::canOperation($operation, $options, $access_results);

    if ($this->isAccessForbidden($access_results, $options)) return;

    // УСЛОВИЯ РАЗГРАНИЧЕНИЯ ДОСТУПА К ОПЕРАЦИИ "Edit" (Редактирование элемента)
    if ($operation == 'Edit') {
      $this->checkUserUuid($options, $access_results);
      $this->checkOwnerUuid($options, $access_results);
    }

    // УСЛОВИЯ РАЗГРАНИЧЕНИЯ ДОСТУПА К ОПЕРАЦИИ "Complete" (Завершение элемента)
    elseif ($operation == 'Complete') {
      // Параметры:
      // $options['comment'] - Комментарий к завершению
      if (!$this->isBuildOnly()) {
        if ($this->getState() == CompleteOperationInterface::STATE_COMPLETED) {
          $cut = $this->getMessagePart1();
          $reason = "Этап $cut невозможно завершить, т.к. он уже завершён";
          $access_results['default_access'] = AccessResult::forbidden($reason);
          return;
        }

        // Пока не назначили пользователя для этого элемента и не
        //  сохранили его - "Завершить" элемент невозможно
        if ($this->isNew()) {
          $cut = $this->getMessagePart1();
          $reason = "Этап $cut невозможно завершить, т.к. он ещё не сохранён";
          $access_results['default_access'] = AccessResult::forbidden($reason);
          return;
        }

        if (!$this->getUserUuid()) {
          $cut = $this->getMessagePart1();
          $reason = "Этап $cut невозможно завершить, т.к. для него не назначен пользователь";
          $access_results['default_access'] = AccessResult::forbidden($reason);
          return;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doOperation(string $operation, array &$options) {
    if ($operation == 'Complete') {
      // $options['autocomplete_nested_items'] - См. parent::doComplete($options);
      // Всегда завершается без автоматического завершения вложенных элементов
      unset($options['autocomplete_nested_items']);

      // Параметры:
      // $options['comment'] - Комментарий к завершению
    }

    parent::doOperation($operation, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function propertiesSettings() {
    return array_merge(parent::propertiesSettings(), [
      'owner_uuid_property' => [
        'class' => OwnerUuid::class,
        'label' => 'Владелец (Автор)'
      ],
      'user_uuid_property' => [
        'class' => UserUuid::class,
        'label' => 'Пользователь этапа'
      ],
    ]);
  }
}
