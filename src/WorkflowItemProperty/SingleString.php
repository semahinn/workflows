<?php

namespace Snr\Workflows\WorkflowItemProperty;

abstract class SingleString extends SingleKey implements SingleStringInterface {

  /**
   * {@inheritdoc}
   */
  protected function checkSinglePropertyValue(&$data, &$reason) {
    $label = static::getLabel();
    $property_key = static::getSingleKey();
    if ($data[$property_key] === null) $data[$property_key] = '';
    if (!is_string($data[$property_key])) {
      $reason = "Свойство \"$label\" должно быть строкой";
      return false;
    }
    return true;
  }

}