<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\Access\AccessResultInterface;

/**
 * В простейшем случае, параметр $options содержит новые значения свойств этапа
 * Помимо этого, в $options могут быть любые другие параметры,
 * использующиеся в логике редактирования
 */
interface EditOperationInterface extends OperationInterface {

  /**
   * Пытается ОТРЕДАКТИРОВАТЬ этап
   *
   * @param array $options
   *  Новые значения свойств этапа и другие параметры
   *
   * @return static
   *
   * @throws
   *  Порождает исключение, если элемент невозможно ОТРЕДАКТИРОВАТЬ
   */
  public function edit(array $options);

  /**
   * Проверяет, можно ли ОТРЕДАКТИРОВАТЬ этап
   *
   * @param array $options
   *  Новые значения свойств этапа и другие параметры
   *
   * @return AccessResultInterface
   */
  public function canEdit(array $options);
  
}