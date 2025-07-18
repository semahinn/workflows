<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\Access\AccessResultInterface;

/**
 * Описывает любую группу этапов
 *
 * Группы бывают двух типов: последовательными ('sequential') и параллельными ('parallel')
 *
 * Этапы внутри группы могут быть объединены двумя способами:
 * с помощью оператора 'or' или с помощью оператора 'and'
 *
 * От комбинации типа группы и оператора группы зависит логика завершения
 * Алгоритм описан в методе doOperation класса AbstractGroup
 *
 * @see AbstractGroup::doOperation()
 */
interface AbstractGroupInterface extends WorkflowItemInterface {

  /**
   * @var string
   *  Этапы в группе выполняются последовательно
   */
  const TYPE_SEQUENTIAL = 'sequential';

  /**
   * @var string
   *  Этапы в группе выполняются параллельно
   */
  const TYPE_PARALLEL = 'parallel';

  /**
   * @var string
   *  Этапы в группе объединены с помощью оператора 'or'
   */
  const TYPE_OPERATOR_OR = 'or';

  /**
   * @var string
   *  Этапы в группе объединены с помощью оператора 'and'
   */
  const TYPE_OPERATOR_AND = 'and';

  /**
   * Возвращает тип группы
   *
   * @return string
   */
  public function getGroupType();

  /**
   * @return bool
   */
  public function isParallel();

  /**
   * @return bool
   */
  public function isSequential();

  /**
   * @param string $type
   * @param array  $options
   *
   * @return static
   */
  public function setGroupType(string $type, array $options = []);

  /**
   * @return string
   */
  public function getGroupOperator();

  /**
   * @return bool
   */
  public function isOr();

  /**
   * @return bool
   */
  public function isAnd();

  /**
   * Устанавливает оператор, с помощью которого объединяются
   * вложенные элементы группы.
   *
   * @see AbstractGroupInterface::assumeGroupCanBeCompleted()
   *
   * @param string $operator
   *  Оператор ('or' или 'and')
   *
   * @param array  $options
   *
   * @return static
   */
  public function setGroupOperator(string $operator, array $options = []);

  /**
   * Возвращает данные группы на момент сразу после её инициализации
   * (после создания с помощью метода create)
   *
   * @return AbstractGroupInterface
   */
  public function getSourceGroup();
  
  /**
   * ДОБАВЛЯЕТ элемент $item в группу
   *
   * @param WorkflowItemInterface $item
   *  Элемент для ДОБАВЛЕНИЯ в группу
   *
   * @param array $options
   *
   * @return static
   *
   * @throws
   *  Порождает исключение, если ДОБАВИТЬ элемент в группу невозможно
   *
   * @code
   * try
   * {
   *   // ДОБАВЛЕНИЕ элемента в группу
   *   $group->addItem($performer);
   * }
   * catch (\Exception $ex)
   * {
   *   $can_add = false;
   *   $error_message = $ex->getMessage();
   * }
   * @endcode
   */
  public function addItem(WorkflowItemInterface $item, array $options = []);

  /**
   * Проверяет, можно ли ДОБАВИТЬ элемент $item в группу
   *
   * @param WorkflowItemInterface|null $item
   *  Элемент для добавления в группу
   *  Если необходимо проверить можно ли ДОБАВИТЬ
   *  ЛЮБОЙ элемент - не передавать ничего
   *
   * @param array $options
   *
   * @return AccessResultInterface
   *
   * 1. Если $item == null, то проверяет можно ли ДОБАВЛЯТЬ любые элементы в группу
   * 2. Если $item указан, то проверяет можно ли ДОБАВИТЬ элемент $item в группу
   *
   * @see OperationInterface::can()
   *
   * @code
   *
   * // Можно ли ДОБАВИТЬ ЛЮБОЙ элемент в группу
   * $access = $group->canAddItem(null);
   *
   * // Можно ли ДОБАВИТЬ элемент в группу
   * $access = $group->canAddItem($performer);
   *
   * @endcode
   */
  public function canAddItem(WorkflowItemInterface $item = null, array $options = []);

  /**
   * @param bool $recursive
   *
   * @return WorkflowItemInterface[]
   */
  public function getItems(bool $recursive = false);

  /**
   * @param string $id
   *
   * @param bool $recursive
   *
   * @return WorkflowItemInterface
   */
  public function getItem(string $id, bool $recursive = false);

  /**
   * @param string $item_id
   *  Идентификатор элемента в группе
   *
   * @return AbstractGroupInterface[]
   *  Массив из всех родительских групп, в которых расположен элемент $item_id.
   *
   * Перечислены в порядке от корневой
   * (Последним элементом является сам элемент $item_id,
   *  т.о., в массиве всегда как минимум 2 элемента)
   */
  public function getArrayParents(string $item_id);

  /**
   * Возвращает первый незавершённый ("Текущий") элемент этой группы
   *
   * @param array $options
   *
   * @return WorkflowItemInterface
   */
  public function getFirstUncompleted(array $options = []);

  /**
   * Выполняет действия по автоматическому завершению группы
   * Ищет очередной "Текущий" элемент в группе. Если находит - пытается "Автоматически" его завершить.
   * "Автоматическому" завершению подвергаются вложенные элементы группы,
   * которые помечены как "Автоматически завершаемые"
   *
   * @param array $options
   *
   * @see CompleteOperationInterface::autoCompleteFlag()
   * @see AbstractGroupInterface::getFirstUncompleted()
   * @see CompleteOperationInterface::isElementReadyForAutoComplete()
   */
  public function tryAutoCompleteNestedItems(array &$options);

  /**
   * Для группы проверяет, завершены ли все ее
   * вложенные этапы таким образом, чтобы СЧИТАТЬ,
   * что она может быть автоматически завершена.
   *
   * Если группа с оператором 'or', то для этого
   * достаточно одного завершённого вложенного элемента
   * Если группа с оператором 'and', то для этого
   * необходимо, чтобы все вложенные элементы были завершены
   *
   * @return bool
   */
  public function assumeGroupCanBeCompleted();

  /**
   * Возвращает массив (дерево), в который могут попадать
   * этапы из всех вложенных групп (глубина вложенности не ограничена).
   *
   * Алгоритм:
   * Берётся определенная группа:
   * 1. Если эта группа последовательная, то добавляем в массив
   * первый её незавершённый (если установлен флаг $only_uncompleted) этап,
   * для добавленного этапа повторяем алгоритм
   * 2. Если эта группа параллельная, то добавляем в массив
   * ВСЕ её незавершённые (если установлен флаг $only_uncompleted) этапы,
   * для каждого добавленного этапа повторяем алгоритм
   *
   * @param string $mode
   *  Разновидность возвращаемого результата:
   *  1. 'tree' - результат будет представлен в виде иерархического дерева (идентификаторы этапов)
   * @code
   *  ['group_1' => [
   *     'group_1_1' => [
   *       'item_1' => [],
   *       'group_1_1_1' => []
   *     ],
   *     'item_2' => [],
   *     'item_3' => [],
   *   ],
   *  'group_2' => [
   *    'item_4' => [],
   *    'group_2_1' => [],
   *   ]
   *  ]
   * @endcode
   *
   *  2. 'list' - результат будет в виде массива, каждый элемент которого имеет ключи:
   *  'id' - идентификатор этапа
   *  'parent' - идентификатор родительского этапа
   *  'item' - экземпляр этапа
   * @code
   *   ['group_1' => [
   *      'id' => 'group_1',
   *      'parent' => 'this_id',
   *      'item' => $group_1
   *    ],
   *    'group_1_1' => [
   *      'id' => 'group_1_1',
   *      'parent' => 'group_1',
   *      'item' => $group_1_1
   *    ],
   *    'item_1' => [
   *      'id' => 'item_1',
   *      'parent' => 'group_1_1',
   *      'item' => $item_1'
   *     ],
   *    'group_1_1_1' => [
   *      'id' => 'group_1_1_1',
   *      'parent' => 'group_1_1',
   *      'item' => $group_1_1_1
   *     ],
   *    'item_2' => [
   *      'id' => 'item_2',
   *      'parent' => 'group_1',
   *       item' => $item_2
   *     ],
   *    'item_3' => [
   *      'id' => 'item_3',
   *      'parent' => 'group_1',
   *      'item' => $item_3
   *     ],
   *    'group_2' => [
   *      'id' => 'group_2',
   *      'parent' => 'this_id',
   *      'item' => $group_2
   *     ],
   *    'item_4' => [
   *      'id' => 'item_4',
   *      'parent' => 'group_2',
   *      'item' => $item_4
   *     ],
   *    'group_2_1' => [
   *      'id' => 'group_2_1',
   *      'parent' => 'group_2',
   *      'item' => $group_2_1
   *     ],
   *   ]
   * @endcode
   *
   *  3. 'branches' - результат это массив ветвей иерархичного дерева
   *  (каждый элемент массива это массив идентификаторов, составляющих одну ветвь)
   * @code
   *  [
   *    ['group_1', 'group_1_1', 'item_1'],
   *    ['group_1', 'group_1_1', 'group_1_1_1'],
   *    ['group_1', 'item_2'],
   *    ['group_1', 'item_3'],
   *    ['group_2', 'item_4'],
   *    ['group_2', 'group_2_1'],
   *  ]
   * @endcode
   *
   * @param bool $only_current
   *  Листьями дерева будут только "Текущие" этапы
   *  (т.е. ветвь состоит только из НЕ завершённых элементов)
   *
   * @param array $options
   *
   * @return array
   */
  public function getTree(string $mode = 'tree', bool $only_current = false, array $options = []);

  /**
   * Пытается УДАЛИТЬ из группы элемент с идентификатором $item_id
   *
   * @param string $item_id
   *  Идентификатор элемента для УДАЛЕНИЯ
   *
   * @param array  $options
   *
   * @return static
   *
   * @code
   * try
   * {
   *   // Удаление элемента из группы
   *   $group->removeItem('item_1');
   * }
   * catch (\Exception $ex)
   * {
   *   $can_add = false;
   *   $error_message = $ex->getMessage();
   * }
   * @endcode
   *
   * @throws
   *
   * @see OperationInterface::perform()
   *
   * Порождает исключение, если элемент невозможно УДАЛИТЬ
   */
  public function removeItem(string $item_id, array $options = []);

  /**
   * Проверяет, можно ли УДАЛИТЬ из группы этап с идентификатором $item_id
   *  является оберткой над.
   *
   * @param string $item_id
   *  Идентификатор этапа для УДАЛЕНИЯ
   *
   * @param array  $options
   *
   * @return AccessResultInterface
   *
   * @code
   * // Можно ли УДАЛИТЬ этап из группы
   * $access = $group->canRemoveItem('item_1');
   * @endcode
   */
  public function canRemoveItem(string $item_id, array $options = []);

  /**
   * Проверяет, выполняется ли сейчас УДАЛЕНИЕ этапа
   * Если да, то возвращает идентификатор удаляемого этапа.
   *
   * @param string $operation
   * @param array $options
   * @param WorkflowItemInterface $item
   *
   * @return string
   */
  public static function conditionForRemoveOperation(string $operation, array $options, WorkflowItemInterface $item);

  /**
   * Возвращает массив идентификаторов плагинов,
   * этапы которых можно добавлять в эту группу
   *
   * @return array
   */
  public function getAllowedPlugins();

}
