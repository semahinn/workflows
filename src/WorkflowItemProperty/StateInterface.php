<?php

namespace Snr\Workflows\WorkflowItemProperty;

/**
 * Interface StateInterface
 */
interface StateInterface extends PropertyInterface {

  /**
   * Ищет в массиве $options параметр 'state'
   *
   * @param array $options
   *
   * @return string
   *  true означает, что код будет устанавливать значения некоторых свойств,
   *   даже если элемент помечен как "Использовать общие параметры"
   */
  public static function getStateFromOptions(array &$options);

}
