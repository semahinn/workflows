<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\ProcessedPropertiesSettings;
use Snr\Workflows\WorkflowItemProperty\PropertyInterface;

trait ItemWithPropertiesTrait {
  
  use EditOperationTrait;
  
  /**
   * @var PropertyInterface[]
   */
  protected $properties = [];
  
  /**
   * @var ProcessedPropertiesSettings
   */
  protected $processedPropertiesSettings;
  
  /**
   * @return array
   */
  protected abstract function propertiesSettings();
  
  /**
   * {@inheritdoc}
   */
  public function getPropertiesSettings() {
    if (!$this->processedPropertiesSettings)
      $this->processedPropertiesSettings = new ProcessedPropertiesSettings(static::propertiesSettings());
    return $this->processedPropertiesSettings->getSettings();
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPropertySetting(string $property_key, string $setting_key) {
    if (!$this->processedPropertiesSettings)
      $this->processedPropertiesSettings = new ProcessedPropertiesSettings(static::propertiesSettings());
    return $this->processedPropertiesSettings->getSetting($property_key, $setting_key);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getProperties() {
    return $this->properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getProperty(string $key) {
    if (isset($this->properties[$key])) return $this->properties[$key];
    return null;
  }
  
  /**
   * @param array $keys
   * @param array $data
   * @param $error_message
   *
   * @return PropertyInterface[]
   */
  protected function tryCreateProperties(
    array $keys, array $data, &$error_message) {
    $settings = $this->getPropertiesSettings();
    $results = [];
    foreach ($keys as $key) {
      $class = $settings[$key]['class'];
      if ($class::conditionToCreate($data)) {
        try {
          $property = $class::create($data);
          $results[$key] = $property;
        }
        catch (\Exception $ex) {
          $error_message = $ex->getMessage();
          return [];
        }
      }
    }
    
    return $results;
  }
  
  /**
   * @param array $options
   *
   * @param array|null $keys
   *  Идентификаторы экземпляров свойств, которые
   *  требуется найти в массиве $options
   *
   * @param bool $try_create_if_not_found
   *  Если true, то пытается создать свойства, если
   *  они не были найдены в массиве $options
   *
   * @return array
   *  При этом, если произошла ошибка при создании
   *  одного из свойств, то в результате об этом появится информация (ключ
   *   'error_message')
   *  ($results[$key]['error_message'])
   *  Если экземпляр был создан успешно, то он будет в массиве результата (ключ
   *   'property')
   *  ($results[$key]['property'])
   */
  protected function foundProperties(
    array $options, array $keys = null, bool $try_create_if_not_found = true) {
    $settings = $this->getPropertiesSettings();
    if ($keys === null) $keys = array_keys($settings);
    else $keys = array_intersect($keys, array_keys($settings));
    $results = [];
    foreach ($keys as $key) {
      if (isset($options[$key]) && $options[$key] instanceof PropertyInterface) {
        $results[$key] = ['property' => $options[$key]];
        break;
      }
      else {
        if ($try_create_if_not_found) {
          $class = $settings[$key]['class'];
          if ($class::conditionToCreate($options)) {
            try {
              $property = $class::create($options);
              $results[$key] = ['property' => $property];
            }
            catch (\Exception $ex) {
              // Ключ 'error_message' уже является признаком того,
              //  что при создании произошла ошибка
              $results[$key] = ['error_message' => $ex->getMessage()];
            }
          }
        }
      }
    }
    
    return $results;
  }
  
  /**
   * Логика установки НОВЫХ значений свойств,
   * выполняется после всех вызовов do...
   * На этот момент в массиве $data уже должны быть все
   * нужные экземпляры PropertyInterface
   *
   * @param string $operation
   *
   * @param array $options
   *
   * @throws
   */
  protected function doNewProperties(string $operation, array &$options) {
    foreach ($this->getPropertiesSettings() as $key => $values) {
      $results = $this->foundProperties($options, [$key], true);
      if (isset($results[$key]) && array_key_exists('error_message', $results[$key])) {
        $cut = $this->getMessagePart1();
//        $error_message = $results[$key]['error_message'];
//          ", т.к. данные не соответствуют формату" . ($error_message ? ": $error_message" : '');
        $user_message = "Для элемента $cut невозможно установить св-во \"{$values['class']::getLabel()}\": ошибка выполнения";
        throw new \InvalidArgumentException($user_message);
      }
      
      if (isset($results[$key]['property'])) {
        if (!isset($this->properties[$key]))
          $this->properties[$key] = $results[$key]['property'];
        else {
          $this->properties[$key]->setValue($options);
        }
      }
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function foundPropertiesWhenCreate(array &$options, array $keys = null) {
    $items = [];
    $settings = $this->getPropertiesSettings();
    if ($keys === null) $keys = array_keys($settings);
    else $keys = array_intersect($keys, array_keys($settings));
    foreach ($keys as $key) {
      $results = $this->foundProperties($options, [$key], true);
      if (isset($results[$key]) && array_key_exists('error_message', $results[$key])) {
        $error_message = (!empty($results[$key]['error_message']) ? $results[$key]['error_message'] : null);
        $cut = $this->getMessagePart1();
        $user_message = "Невозможно создать этап $cut";
        if (!$error_message)
          $user_message .= ": ошибка выполнения";
        else $user_message .= ": $error_message";
        throw new \InvalidArgumentException($user_message);
      }
      
      if (isset($results[$key]['property'])) {
        $items[$key] = $results[$key]['property'];
        $options[$key] = $results[$key]['property'];
      }
    }
    
    return $items;
  }
  
  /**
   * {@inheritdoc}
   */
  public function foundPropertiesWhenPerform(array &$options, array $keys = null) {
    $items = [];
    $settings = $this->getPropertiesSettings();
    if ($keys === null) $keys = array_keys($settings);
    else $keys = array_intersect($keys, array_keys($settings));
    foreach ($keys as $key) {
      $results = $this->foundProperties($options, [$key], true);
      if (isset($results[$key]) && array_key_exists('error_message', $results[$key])) {
        $error_message = (!empty($results[$key]['error_message']) ? $results[$key]['error_message'] : null);
        $cut = $this->getMessagePart1();
        $user_message = "Для этапа $cut невозможно установить св-во \"{$settings[$key]['class']::getLabel()}\"";
        if (!$error_message)
          $user_message .= ": ошибка выполнения";
        else $user_message .= ": $error_message";
        throw new \InvalidArgumentException($user_message);
      }
      
      if (isset($results[$key]['property'])) {
        $items[$key] = $results[$key]['property'];
        $options[$key] = $results[$key]['property'];
      }
    }
    
    return $items;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPropertiesChangesFromOptions(array $options, array $keys = null) {
    $results = [];
    $properties_from_options = $this->foundPropertiesWhenPerform($options, $keys);
    $current_properties = $this->getProperties();
    foreach (array_keys($properties_from_options) as $key) {
      if (isset($properties_from_options[$key])) {
        if (isset($current_properties[$key]) && ($current_properties[$key] instanceof PropertyInterface)) {
          if ($changes = $current_properties[$key]->changesSchema($properties_from_options[$key]))
            $results[$key] = $changes;
        }
        else {
          if ($changes = $properties_from_options[$key]->changesSchema())
            $results[$key] = $changes;
        }
      }
    }
    return $results;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPropertiesChanges(array $keys = null) {
    $results = [];
    $properties = $this->getProperties();
    foreach ($properties as $key => $property)
      if ($keys === null ||
        (is_array($keys) && in_array($key, $keys))) {
        if ($changes = $property->changesSchema())
          $results[$key] = $changes;
      }
    return $results;
  }
  
  /**
   * {@inheritdoc}
   */
  public function propertiesToArray() {
    // ВАЖНО: Логики, которая бы разрешала конфликты
    //  с повторяющимися ключами значений свойств @see PropertyInterface::getKeys()
    //  пока НЕ СУЩЕСТВУЕТ.
    // Поэтому, если у двух каких то свойств совпадают ключи их значений,
    //  в возвращаемый массив попадет только одно из них (последнее)
    $result = [];
    foreach ($this->getProperties() as $property)
      foreach ($property::getKeys() as $sub_key)
        $result[$sub_key] = $property->getValue()[$sub_key];
    
    return $result;
  }
}
