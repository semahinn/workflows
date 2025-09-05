<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\AccessResult\AccessResult;
use Snr\AccessResult\AccessResultForbidden;
use Snr\AccessResult\AccessResultInterface;
use Snr\Workflows\Event\GetAllowedPluginsEvent;

/**
 * Является базовым классом для всех групп
 */
abstract class AbstractGroup extends WorkflowItem implements AbstractGroupInterface {

  /**
   * По - умолчанию группа всегда "Автоматически завершаемая",
   * но это можно установить и явно, вызвав метод setAutoCompleteFlag
   *
   * @code
   *
   * $group = Group::create(['id' => 'group_1', 'label' => 'Group 1', 'autocomplete' => false]);
   * // or
   * $group = Group::create(['id' => 'group_1', 'label' => 'Group 1']);
   * $group->setAutoCompleteFlag(false);
   *
   * @endcode
   */
  protected $autoCompleteFlag = true;

  /**
   * @var string
   */
  protected $groupOperator = self::TYPE_OPERATOR_AND;

  /**
   * @var WorkflowItemInterface[]
   */
  protected $items = [];

  /**
   * @var string
   */
  protected $groupType = self::TYPE_SEQUENTIAL;

  /**
   * Массив идентификаторо этапов (в т.ч. и c идентификатором этой группы,
   * если она была завершена), которые были завершены вследствие последнего действия,
   * произошедшего с этой группой или любым этапом внутри этой группы
   *
   * @var array
   */
  protected $completedOnLastOperation;

  /**
   * Версия группы сразу после инициализации
   *
   * @var AbstractGroupInterface
   */
  protected $sourceGroup;

  /**
   * {@inheritdoc}
   */
  public function doCreate(array $data) {
    // Итого, занятые ключи в массиве data:
    // те, что перечислил в родительском doCreate +
    // group_type, group_operator, items, autocomplete_nested_items
    parent::doCreate($data);

    $items = [];
    if (!empty($data['items']) && is_array($data['items'])) {
      // 'root_when_create' - необходим для обращения к экземпляру
      // корневой группы, потому что в момент создания этапа,
      // мы ещё не может получить его корневую группу (св-во rootGroup ещё не установлено,
      // это произойдет только при вызове метода addItem)
      $items = $this->getPluginManager()->createInstances($data['items'], $this->getContext(), $data['root_when_create'] ?? $this);
    }
    
    foreach ($items as $item) {
      $this->addItem($item);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupType() {
    return $this->groupType;
  }

  /**
   * {@inheritdoc}
   */
  public function isParallel() {
    return $this->groupType == self::TYPE_PARALLEL;
  }

  /**
   * {@inheritdoc}
   */
  public function isSequential() {
    return $this->groupType == self::TYPE_SEQUENTIAL;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroupType(string $type, array $options = []) {
    $options['group_type'] = $type;
    return $this->perform('Edit', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupOperator() {
    return $this->groupOperator;
  }

  /**
   * {@inheritdoc}
   */
  public function isAnd() {
    return $this->groupOperator == self::TYPE_OPERATOR_AND;
  }

  /**
   * {@inheritdoc}
   */
  public function isOr() {
    return $this->groupOperator == self::TYPE_OPERATOR_OR;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroupOperator(string $operator, array $options = []) {
    $options['group_operator'] = $operator;
    return $this->perform('Edit', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceGroup() {
    return $this->sourceGroup;
  }

  /**
   * {@inheritdoc}
   */
  protected function clear() {
    $this->items = [];
    return parent::clear();
  }

  /**
   * {@inheritdoc}
   */
  public static function getAvailableOperations() {
    return array_merge([
      'AddItem' => [
        'label' => 'Добавить элемент',
        'no_ui' => false
      ],
      'RemoveItem' => [
        'label' => 'Удалить элемент',
        'no_ui' => false
      ]
    ], parent::getAvailableOperations());
  }

  /**
   * {@inheritdoc}
   */
  protected function canOperation(string $operation, array &$options, array &$access_results) {
    parent::canOperation($operation, $options, $access_results);

    if (isset($access_results['default_access']) && $access_results['default_access'] instanceof AccessResultInterface) {
      if ($access_results['default_access'] instanceof AccessResultForbidden) {
        return;
      }
    }

    if (!$this->isBuildOnly() && $this->getState() == CompleteOperationInterface::STATE_COMPLETED &&
      ($operation == 'Edit' || $operation == 'AddItem' || $operation == 'RemoveItem')) {
      if ($this->getState() == CompleteOperationInterface::STATE_COMPLETED) {
        $cut = $this->getMessagePart1();
        $reason = '';
        if ($operation == 'Edit') {
          $reason = "Этап $cut невозможно отредактировать, т.к. он уже завершён";
        }
        if ($operation == 'AddItem') {
          $reason = "Невозможно добавить вложенный элемент для этапа $cut, т.к. он уже завершён";
        }
        if ($operation == 'RemoveItem') {
          $reason = "Невозможно удалить вложенный элемент из этапа $cut, т.к. он уже завершён";
        }
        $access_results['default_access'] = AccessResult::forbidden($reason);

        return;
      }
    }

    // УСЛОВИЯ РАЗГРАНИЧЕНИЯ ДОСТУПА К ОПЕРАЦИИ "Edit" (Редактирование элемента)
    if ($operation == 'Edit') {
      // Тип группы можно всегда менять, тут нет ограничений
      if (array_key_exists('group_type', $options)) {
        $new_type = $options['group_type'];
        if (!($new_type == static::TYPE_PARALLEL ||
          $new_type == static::TYPE_SEQUENTIAL)) {
          $class = static::class;
          $interface = AbstractGroupInterface::class;
          $message = "Для экземпляра \"$class\", свойство " .
            "\"group_type\" должно иметь одно из допустимых значений: \"parallel\", \"sequential\"" .
            "(см. \"$interface::setGroupType()\")";
          $access_results['default_access'] = AccessResult::forbidden($message);

          return;
        }
      }

      // Оператор группы можно всегда менять, тут нет ограничений
      if (array_key_exists('group_operator', $options)) {
        $new_group_operator = $options['group_operator'];
        if (!(is_string($new_group_operator) &&
          (strtolower($options['group_operator']) == 'or' || strtolower($options['group_operator']) == 'and'))) {
          $class = static::class;
          $interface = AbstractGroupInterface::class;
          $message = "Для экземпляра \"$class\", свойство " .
            "\"group_operator\" должно иметь одно из допустимых значений: \"or\", \"and\"" .
            "(см. \"$interface::setGroupOperator()\")";
          $access_results['default_access'] = AccessResult::forbidden($message);

          return;
        }
      }
    }

    // УСЛОВИЯ РАЗГРАНИЧЕНИЯ ДОСТУПА К ОПЕРАЦИИ "AddItem" (Добавление элемента в группу)
    elseif ($operation == 'AddItem') {
      /**
       * @var $item WorkflowItemInterface
       */
      // $options['item'] - ДОБАВЛЯЕМЫЙ элемент. Элемент, который требуется ДОБАВИТЬ в ТЕКУЩУЮ группу
      $item = $options['item'];
      $forbidden_message = "В группу с id \"{$this->id()}\" ({$this->getLabel()}) " .
        "невозможно добавлять элементы";
      if ($item)
        $forbidden_message = "В группу с id \"{$this->id()}\" ({$this->getLabel()}) " .
          "невозможно добавить элемент (id = \"{$item->id()}\" label = \"{$item->getLabel()}\")";

      // $this - ТЕКУЩАЯ группа (\Snr\Workflows\Item\AbstractGroupInterface).
      //    Группа, куда ДОБАВЛЯЕТСЯ элемент $item

      // $item - ДОБАВЛЯЕМЫЙ элемент (\Snr\Workflows\Item\WorkflowItemInterface).
      // Элемент, который требуется ДОБАВИТЬ в ТЕКУЩУЮ группу
      //
      // $root - КОРНЕВАЯ группа (\Snr\Workflows\Item\AbstractGroupInterface).
      // Группа, в которой предположительно находится ТЕКУЩАЯ группа
      //
      // Элемент НЕЛЬЗЯ ДОБАВИТЬ в группу:
      //
      // Правила из сanOperation($operation, $options) +
      //
      // 1) Если идентификатор ДОБАВЛЯЕМОГО элемента уже занят ТЕКУЩЕЙ группой
      // 2) Если идентификатор ДОБАВЛЯЕМОГО элемента уже используется где - то внутри ТЕКУЩЕЙ группы;
      // или внутри ГЛАВНОЙ группы (В случае если был передан контекст ГЛАВНОЙ группы $root)
      // 3) Если тип ДОБАВЛЯЕМОГО элемента запрещен для ДОБАВЛЕНИЯ в ТЕКУЩУЮ группу
      //

      $root = $this->getRoot();
      if ($root) {
        $all_existing_items = $root->getItems(true);
      }
      else {
        $all_existing_items = $this->getItems(true);
      }

      if ($item) {
        // 1)
        if ($item->id() == $this->id()) {
          $reason = "$forbidden_message: Последний идентификатор уже использует группа";
          return AccessResult::forbidden($reason);
        }

        // 2)
        if (isset($all_existing_items[$item->id()])) {
          $reason = "$forbidden_message: Последний идентификатор уже используется";
          $access_results['default_access'] = AccessResult::forbidden($reason);
          return;
        }

        // 3)
        // $item можно добавить в группу, только если группа,
        //  куда добавляется элемент $item поддерживает добавление элементов этого типа
        $allowed_items = $this->getAllowedPlugins();
        $allowed = false;
        $definitions = $this->getPluginManager()->getDefinitions();
        foreach ($allowed_items as $id) {
          $class = $definitions[$id]['class'];
          if ($item instanceof $class) {
            $allowed = true;
            break;
          }
        }

        if (!$allowed) {
          $reason = "$forbidden_message: Объект этого типа нельзя добавить как вложенный";
          $access_results['default_access'] = AccessResult::forbidden($reason);

          return;
        }
      }
    }

    // УСЛОВИЯ РАЗГРАНИЧЕНИЯ ДОСТУПА К ОПЕРАЦИИ "RemoveItem" (Удаление элемента из группы)
    elseif ($operation == 'RemoveItem') {
      $this->checkRemoveItem($options, $access_results);
    }

    return;
  }

  /**
   * {@inheritdoc}
   */
  protected function doOperation(string $operation, array &$options) {
    // Операция "Edit" (Редактирование элемента)
    if ($operation == 'Edit') {
      if (array_key_exists('group_type', $options)) {
        $this->groupType = strtolower($options['group_type']);
      }

      if (array_key_exists('group_operator', $options)) {
        $this->groupOperator = strtolower($options['group_operator']);
      }
    }

    // Операция "Complete" (Завершение элемента)
    elseif ($operation == 'Complete') {
      // Параметры:
      // Если 'autocomplete_nested_items' - true, то группа
      // завершается с автоматическим завершением ее вложенных элементов
      $autocomplete_nested_items = null;
      if (isset($options['autocomplete_nested_items'])) {
        if ($options['autocomplete_nested_items'] === true) {
          $autocomplete_nested_items = true;
        } elseif ($options['autocomplete_nested_items'] === false) {
          $autocomplete_nested_items = false;
        }
      }

      if (!$autocomplete_nested_items) {
        parent::doOperation('Complete', $options);
      } else {
        $this->tryAutoCompleteNestedItems($options);
      }
    }

    // Операция "AddItem" (Добавление элемента в группу)
    elseif ($operation == 'AddItem') {
      /**
       * @var $item WorkflowItemInterface
       */
      // $options['item'] - ДОБАВЛЯЕМЫЙ элемент. Элемент, который требуется ДОБАВИТЬ в ТЕКУЩУЮ группу
      $item = $options['item'];
      $this->items[$item->id()] = $item;
      // Запоминаем для нового элемента его ГЛАВНУЮ (root) группу
      if ($root = $this->getRoot()) {
        $item->updateRootOfAddedItem($root);
      } else {
        $item->updateRootOfAddedItem($this);
      }

      // Если добавляемый элемент группа, то всем его дочерним элементам AbstractGroupInterface
      //  устанавливаем новую ГЛАВНУЮ группу
      if ($item instanceof AbstractGroupInterface) {
        foreach ($item->getItems(true) as $child_item) {
          $child_item->setRoot($item->getRoot());
        }
      }
    }

    // Операция "RemoveItem" (Удаление элемента из группы)
    elseif ($operation == 'RemoveItem') {
      $id_to_remove = $options['id_to_remove'];
      unset($this->items[$id_to_remove]);
    }

    if ($operation != 'Complete') {
      parent::doOperation($operation, $options);
    }
  }

  // МЕТОДЫ ДОБАВЛЕНИЯ (addItem, canAddItem)
  /**
   * {@inheritdoc}
   */
  final public function addItem(WorkflowItemInterface $item, array $options = []) {
    $options['item'] = $item;
    $this->perform('AddItem', $options);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function canAddItem(WorkflowItemInterface $item = null, array $options = []) {
    $options = ['item' => $item];
    return $this->can('AddItem', $options);
  }

  // МЕТОДЫ УДАЛЕНИЯ (removeItem, canRemoveItem)
  /**
   * {@inheritdoc}
   */
  final public function removeItem(string $item_id, array $options = []) {
    foreach ($this->items as $key => $item) {
      if ($item->id() == $item_id) {
        $options['id_to_remove'] = $item->id();
        $this->perform('RemoveItem', $options);
      } elseif ($item instanceof AbstractGroupInterface) {
        $item->removeItem($item_id);
      }
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  final public function canRemoveItem(string $item_id, array $options = []) {
    $parents = $this->getArrayParents($item_id);
    if ($parents) {
      $item_to_delete = array_pop($parents);
      $group = $parents[array_key_last($parents)];
      $options['id_to_remove'] = $item_to_delete->id();

      return $group->can('RemoveItem', $options);
    }

    $cut = $this->getMessagePart1();
    $reason = "Из группы $cut невозможно удалить элемент (id = \"$item_id\"), потому что его там нет";
    throw new \Exception($reason);
  }

  /**
   * Проверяет, выполняется ли сейчас УДАЛЕНИЕ этапа
   * Если да, то возвращает идентификатор удаляемого этапа.
   *
   * @param string $operation
   * @param array $options
   * @param WorkflowItemInterface $item
   *
   * @return WorkflowItemInterface
   *  Удаляемый этап непосредственно перед удалением
   */
  public static function conditionForRemoveOperation(string $operation, array $options, WorkflowItemInterface $item) {
    $root_item = $item->getRoot();
    if (!$root_item) {
      $root_item = $item;
    }

    if ($root_item && $operation == 'RemoveItem' && $item instanceof AbstractGroupInterface &&
      isset($options['item_to_remove']) && ($item_to_remove = $options['item_to_remove'])) {
      return $item_to_remove;
    }

    return null;
  }

  /**
   * Типовая проверка доступа, можно в таком виде использовать
   *  в своих методах canOperation элементов (не обязательно), но
   *  иногда лучше описать свою уникальную логику.
   *
   * @param array $options
   * @param array $access_results
   */
  protected function checkRemoveItem(array &$options, array &$access_results) {
    if (empty($options['id_to_remove'])) {
      $message = "Не указано свойство 'id_to_remove' (Идентификатор удаляемого этапа)";
      $access_results['default_access'] = AccessResult::forbidden($message);
      return;
    }

    $item_to_remove = $this->getItem($options['id_to_remove'], true);
    if ($item_to_remove) {
      $options['item_to_remove'] = $item_to_remove;
    } else {
      $cut = $this->getMessagePart1();
      $message = "Из группы $cut невозможно удалить элемент (id = {$options['id_to_remove']}), потому что его там нет";
      $access_results['default_access'] = AccessResult::forbidden($message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function tryAutoCompleteNestedItems(array &$options) {
    if ($this->getGroupType() == AbstractGroupInterface::TYPE_PARALLEL) {
      // Игнорирую все дискреционные проверки при автоматическом завершении
      $this->setIgnoreAccessInOptions($options, 'user_access');
      // Если группа параллельная, то, возможно,
      //  ее вложенных завершённых элементов УЖЕ достаточно, чтобы
      //  СЧИТАТЬ, что она может быть автоматически завершена
      if ($this->isElementReadyForAutoComplete($options) && $this->assumeGroupCanBeCompleted()) {
        parent::doOperation('Complete', $options);
        return;
      }

      // В параллельной группе сразу пытаемся завершить все вложенные элементы группы,
      //  как только завершённых элементов становится достаточно - завершаем саму группу
      foreach ($this->getItems() as $item) {
        if (!$this->assumeGroupCanBeCompleted() && $item->isElementReadyForAutoComplete($options)) {
          $item->complete($options);
        }
      }
    } elseif ($this->getGroupType() == AbstractGroupInterface::TYPE_SEQUENTIAL) {
      $current_item = $this->getFirstUncompleted();
      // Игнорирую все дискреционные проверки при автоматическом завершении
      $this->setIgnoreAccessInOptions($options, 'user_access');
      // Завершаем группу простым образом, если в ней нет незавершённых элементов
      if (!$current_item && $this->isElementReadyForAutoComplete($options)) {
        parent::doOperation('Complete', $options);
      }
      // Пытаемся завершить "Текущий" элемент группы
      elseif ($current_item && $current_item->isElementReadyForAutoComplete($options)) {
        $current_item->complete($options);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isElementReadyForAutoComplete(array $options = []) {
    if ($this->autoCompleteFlag() &&
      !($this->canComplete($options) instanceof AccessResultForbidden) &&
      !$this->isBlocked()) {
      return true;
    }

    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function assumeGroupCanBeCompleted() {
    if (!$this->items) {
      return true;
    }

    $group_operator = $this->getGroupOperator();
    if ($group_operator == 'and') {
      foreach ($this->items as $item) {
        if ($item->getState() != CompleteOperationInterface::STATE_COMPLETED) {
          return false;
        }
      }

      return true;
    } elseif ($group_operator == 'or') {
      foreach ($this->items as $item) {
        if ($item->getState() == CompleteOperationInterface::STATE_COMPLETED) {
          return true;
        }
      }

      return false;
    }

    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems($recursive = false) {
    $all = [];
    foreach ($this->items as $item) {
      $all[$item->id()] = $item;
      if ($item instanceof AbstractGroupInterface && $recursive) {
        $all = array_merge($all, $item->getItems($recursive));
      }
    }

    return $all;
  }

  /**
   * {@inheritdoc}
   */
  public function getItem($id, $recursive = false) {
    foreach ($this->getItems($recursive) as $item) {
      if ($item->id() == $id) {
        return $item;
      }
    }

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getArrayParents($item_id) {
    foreach ($this->items as $item) {
      if ($item->id() == $item_id) {
        return [$this, $item];
      }
      if ($item instanceof AbstractGroupInterface) {
        $array_parents = $item->getArrayParents($item_id);
        if ($array_parents && $array_parents[count($array_parents) - 1]->id() == $item_id) {
          return array_merge([$this], $array_parents);
        }
      }
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFirstUncompleted(array $options = []) {
    foreach ($this->items as $item) {
      if ($item->getState() != CompleteOperationInterface::STATE_COMPLETED) {
        return $item;
      }
    }
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getTree(string $mode = 'tree', bool $only_current = false, array $options = []) {
    $results = [];
    $items = $this->items;
    if ($this->groupType == AbstractGroupInterface::TYPE_SEQUENTIAL &&
      $only_current && ($current_element = $this->getFirstUncompleted())) {
      $items = [$current_element];
    }
    foreach ($items as $item) {
      if (!$only_current || $item->getState() != CompleteOperationInterface::STATE_COMPLETED) {
        // list
        if (strtolower($mode) == 'list') {
          $results[$item->id()] = [
            'id' => $item->id(),
            'parent' => $this->id(),
            'item' => $item
          ];
          if ($item instanceof AbstractGroupInterface)
            $results = array_merge($results, $item->getTree($mode, $only_current, $options));
        }
        // branches
        elseif (strtolower($mode) == 'branches') {
          $results[] = [$item->id()];
          if ($item instanceof AbstractGroupInterface) {
            $inner_branches = $item->getTree($mode, $only_current, $options);
            if ($inner_branches) {
              $branch_prefix = array_pop($results);
              foreach ($inner_branches as $inner_branch) {
                array_push($results, array_merge($branch_prefix, $inner_branch));
              }
            }
          }
        }
        // tree
        elseif (strtolower($mode) == 'tree') {
          if ($item instanceof AbstractGroupInterface) {
            $results[$item->id()] = $item->getTree($mode, $only_current, $options);
            if (empty($results[$item->id()])) $results[$item->id()] = $item->id();
          }
        }
      }
    }
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllowedPlugins() {
    $event = new GetAllowedPluginsEvent($this);
    $event_dispatcher = $this->getPluginManager()->getEventDispatcher();
    $event_dispatcher->dispatch(GetAllowedPluginsEvent::EVENT_NAME, $event);
    return $event->getAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $data = parent::toArray();
    $data['group_type'] = $this->getGroupType();
    $data['group_operator'] = $this->getGroupOperator();
    foreach ($this->items as $item) {
      $item_array = $item->toArray();
      $data['items'][] = $item_array;
    }
    return $data;
  }

  public function __clone() {
    foreach ($this->items as $key => $item) {
      $this->items[$key] = clone $item;
    }
  }

}
