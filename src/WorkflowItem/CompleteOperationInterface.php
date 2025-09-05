<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\AccessResult\AccessResultInterface;

/**
 * Любой этап рабочего процесса имеет, как минимум, два состояния:
 * 1. Этап активен (не завершён) - 'active'
 * 2. Этап завершён - 'completed'
 *
 * Определяют как поведение самого этапа, так и поведение
 * группы (рабочего процесса), в которой этот этап находится
 *
 * Незавершённый этап можно завершить двумя способами:
 * 1. Явно - вызвав метод complete у этого этапа
 * 2. Завершив группу (рабочий процесс), в которой этот этап
 * находится. Тогда, если этап внутри этой группы помечен как
 * "Автоматически завершаемый", алгоритм попытается завершить этап
 *
 * Логика завершения этапа или группы описана в
 * в методах doOperation классов WorkflowItem и AbstractGroup
 *
 * @see WorkflowItem::doOperation()
 * @see AbstractGroup::doOperation()
 */
interface CompleteOperationInterface extends OperationInterface {

  // С одной стороны, вместо состояний 'active' и 'completed',
  // мог бы быть один флаг, описывающий завершённость процесса (да/нет),
  // но тогда мы бы не смогли описать дополнительное состояние
  // Например, третье состояние 'in_progress' (что бы оно ни значило)

  /**
   * @var string
   *  Этап активен (не завершён)
   */
  const STATE_ACTIVE = 'active';

  /**
   * @var string
   *  Этап завершён
   */
  const STATE_COMPLETED = 'completed';

  /**
   * @param bool $flag
   *  Если true, то устанавливает, что этап будет работать
   *  как "Автоматически завершаемый"
   *
   * @return static
   */
  public function setAutoCompleteFlag(bool $flag);

  /**
   * @return bool
   *  true, если этап работает как "Автоматически завершаемый" и false если нет
   */
  public function autoCompleteFlag();

  /**
   * Для очередного этапа в группе определяет, может ли он быть "Автоматически завершён"
   *
   * @param array $options
   *
   * @return bool
   */
  public function isElementReadyForAutoComplete(array $options = []);

  /**
   * @return array
   *  Возвращает массив параметров, которые будут передаваться в метод
   *  complete при "Автоматическом завершении" этого элемента
   *
   * @see CompleteOperationInterface::setAutoCompleteFlag()
   */
  public function getAutoCompleteOptions();

  /**
   * Пытается ЗАВЕРШИТЬ элемент
   *
   * @param array $options
   *
   * @return static
   *
   * @throws
   *  Порождает исключение, если элемент невозможно ЗАВЕРШИТЬ
   */
  public function complete(array $options);

  /**
   * Проверяет, можно ли ЗАВЕРШИТЬ элемент
   *
   * @param array $options
   *
   * @return AccessResultInterface
   */
  public function canComplete(array $options);
  
}