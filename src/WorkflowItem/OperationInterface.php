<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\Access\AccessResultInterface;

/**
 * Для того, чтобы изменить состоянием этапа или
 * его свойств необходимо в отношении него выполнять ДЕЙСТВИЯ
 * Основные действия, доступные любому этапу, это
 * "Редактирование" и "Завершение"
 *
 * @see EditOperationInterface
 * @see CompleteOperationInterface
 */
interface OperationInterface {

  /**
   * @return array
   *  Массив, ключи которого являются допустимыми действиями над
   *    этим элементом (Например, "Complete" и "Edit"),
   *    а значения - это массив их параметров:
   *     - label - переведенное название действия,
   *     - no_ui - не предлагать в пользовательском интерфейсе,
   *                (доступно только программно)
   *
   *  @see WorkflowItemInterface::perform()
   */
  public static function getAvailableOperations();

  /**
   * Вызывает методы с логикой ПРОВЕРКИ ДЕЙСТВИЯ для экземпляра WorkflowItemInterface
   *
   * Порождает событие OperationEvent для проверки этого действия c префиксом 'pre'
   * Вызывает метод "canOperation"
   * Порождает событие OperationEvent для проверки этого действия c префиксом 'post'
   *
   * @param string $operation
   *  Действие с этапом
   *
   * @param array $options
   *  Необходимые данные
   *
   * @return AccessResultInterface
   *  Результат проверки доступа. Если доступ запрещен (AccessResultForbidden),
   *  то в этом результате содержится сообщение о причине запрете доступа.
   *  Если не нашлось каких то причин для запрета доступа - возвращает AccessResultNeutral.
   *
   *  Такой результат удобнее использовать любому коду, куда приходит этот результат,
   *  т.к. AccessResultNeutral означает, что, хоть доступ и не запрещён,
   *  он и не точно разрешён (AccessResultAllowed)
   *
   * @throws \Exception
   */
  public function can(string $operation, array &$options);

  /**
   * Выполняет ДЕЙСТВИЕ $operation над элементом WorkflowItemInterface
   * Сначала вызывается метод для ПРОВЕРКИ ДЕЙСТВИЯ can, который проверяет возможность выполнения данного ДЕЙСТВИЯ,
   * затем, если доступ к ДЕЙСТВИЮ разрешён, выполняется метод ВЫПОЛНЕНИЯ ДЕЙСТВИЯ doIt
   *
   * @param string $operation
   *  Тип выполняемого ДЕЙСТВИЯ
   *
   * @param array $options
   *  Необходимые данные
   *
   * @return $this
   *
   * @throws \Exception
   */
  public function perform(string $operation, array $options);

}