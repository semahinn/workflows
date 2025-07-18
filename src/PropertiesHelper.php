<?php

namespace Snr\Workflows;

use Snr\Workflows\WorkflowItem\WorkflowItemInterface;

/**
 * Class PropertiesHelper
 */
class PropertiesHelper {

  /**
   * @param WorkflowItemInterface $item
   *
   * @return array
   */
  public function getPropertiesChanges(WorkflowItemInterface $item) {
    $results = [];
    foreach ($item->getPropertiesChanges()
             as $property_key => $property_value) {
      foreach ($property_value as $sub_key => $value) {
        $results[$sub_key] = $value;
      }
    }
    return $results;
  }

  /**
   * @param WorkflowItemInterface $item
   * @param array $options
   *
   * @return array
   */
  public function getPropertiesChangesFromOptions(WorkflowItemInterface $item, array $options) {
    $results = [];
    foreach ($item->getPropertiesChangesFromOptions($options)
             as $property_key => $property_value) {
      foreach ($property_value as $sub_key => $value) {
        $results[$sub_key] = $value;
      }
    }
    return $results;
  }

  /**
   * @param WorkflowItemInterface $item
   *
   * @return array
   */
  public function getProperties(WorkflowItemInterface $item) {
    $results = [];
    foreach ($item->getProperties() as $key => $property) {
      foreach ($property::getKeys() as $sub_key)
        $results[$sub_key] = $property->getValue()[$sub_key];
    }
    // label тоже нужен, хоть он и не является свойством PropertyInterface
    $results['label'] = $item->getLabel();
    return $results;
  }

}