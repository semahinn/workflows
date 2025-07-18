<?php

namespace Snr\Workflows;

/**
 * Class PropertyProcessor
 */
class ProcessedPropertiesSettings {

  /**
   * @var array
   */
  protected $sourceSettings = [];

  /**
   * @var array
   */
  protected $processedSettings = [];

  public function __construct(array $settings) {
    $this->sourceSettings = $settings;
    $this->process();
  }

  protected function process() {
    foreach ($this->sourceSettings as $property_key => $property_settings) {
      $class = $this->processedSettings[$property_key]['class'] =
        $this->sourceSettings[$property_key]['class'];
      $this->processedSettings[$property_key]['label'] = $class::getLabel();
      unset($property_settings['class']);
      foreach ($property_settings as $setting_key => $setting) {
        if ($setting_key == 'label' && (is_string($setting) && $setting))
          $this->processedSettings[$property_key][$setting_key] = $setting;
        // TODO: Не должно быть в этом модуле. Необходим способ расширения
        elseif ($setting_key == 'disable_if_common_settings_flag_is_set')
          $this->processedSettings[$property_key][$setting_key] = (bool)$setting;
      }
    }
  }

  /**
   * @param array|NULL $keys
   * @param array|NULL $settings
   *
   * @return array
   */
  public function getSettings(array $keys = null, array $settings = null) {
    $results = [];
    foreach ($this->processedSettings as $property_key => $property_settings) {
      if ($keys === null || (is_array($keys) && in_array($property_key, $keys))) {
        foreach ($property_settings as $setting_key => $setting) {
          if ($settings === null || (is_array($settings) && in_array($setting_key, $settings))) {
            $results[$property_key][$setting_key] = $setting;
          }
        }
      }
    }
    return $results;
  }

  /**
   * @param string $property_key
   * @param string $setting_key
   *
   * @return mixed|NULL
   */
  public function getSetting(string $property_key, string $setting_key) {
    $results = $this->getSettings([$property_key], [$setting_key]);
    return $results[$property_key][$setting_key] ?? NULL;
  }

}
