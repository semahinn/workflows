<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\WorkflowItemProperty\PropertyInterface;

/**
 * Описывает методы для установки и поиска значений свойств этапов
 */
interface ItemWithPropertiesInterface {

  /**
   * Возвращает массив, ключи которого - идентификаторы свойств,
   * а значения - их параметры
   *
   * ВАЖНО: Логики, которая бы разрешала конфликты
   *  с повторяющимися идентификаторами свойств и ключами их
   *  внутренних значений пока НЕ СУЩЕСТВУЕТ
   *
   * Т.е. всегда необходимо следить за тем, чтобы идентификаторы не повторялись.
   * Например, если в массиве $options мы используем ключ 'resolution', чтобы установить
   * значение 'resolution' для какого то экземпляра PropertyInterface, то
   * само свойство должно идентифицироваться как, например, 'resolution_property'.
   *
   * Если этого не сделать, то экземпляр PropertyInterface
   * затрёт это значение в массиве $options ($options['resolution'])
   *
   * @code
   *  return [
   *    ...
   *  'resolution_property' => [
   *    'class' => Resolution::class
   *  ],
   *  'file_attachments_property' => [
   *    'class' => FileAttachments::class,
   *    'label' => 'Прикреплённые файлы'
   *  ],
   *    ...
   * ];
   * @endcode
   *
   * @return array
   */
  public function getPropertiesSettings();

  /**
   * @param string $property_key
   *
   * @param string $setting_key
   *
   * @return mixed
   */
  public function getPropertySetting(string $property_key, string $setting_key);

  /**
   * @return PropertyInterface[]
   *  Возвращает текущие значения свойств
   */
  public function getProperties();
  
  /**
   * Называется WhenCreate, потому предназначено для
   * использования в doCreate методах. Отличается текстом исключения
   *
   * @param array $options
   *  Массив данных, в котором осуществляется поиск
   *
   * @param array|null $keys
   *  Идентификаторы свойств
   *
   * @return PropertyInterface[]
   *  Найденные (или если готовых экземпляров не было найдено в массиве $options - созданные)
   *  экземпляры PropertyInterface
   */
  public function foundPropertiesWhenCreate(array &$options, array $keys = null);
  
  /**
   * Называется WhenPerform, потому предназначено для
   *  использования в doOperation и canOperation методах и соответствующих хуках.
   *  Отличается текстом исключения
   *
   * @param array $options
   *  Массив данных, в котором осуществляется поиск
   *
   * @param array|null $keys
   *  Идентификаторы свойств
   *
   * @return PropertyInterface[]
   *  Найденные (или если готовых экземпляров не было найдено в массиве $options - созданные)
   *  экземпляры PropertyInterface
   */
  public function foundPropertiesWhenPerform(array &$options, array $keys = null);
  
  /**
   * @param array $options
   *
   * @param array|null $keys
   *  Идентификаторы свойств, которые проверяются на
   *  наличие для них новых значений (новые значения ищутся в массиве $options)
   *
   * @return array
   *  Массив, хранящий информацию о свойствах,
   *  для которых теперь установлены новые значения.
   *
   *  Может быть несколько значений.
   *  (Например, не просто 'deadline', а 'deadline' и 'deadline_label_uuid'),
   *  в массиве будет информация о каждом таком значении, которое поменялось
   *
   * @code
   *  $result = [
   *    'resolution_property' => [
   *      'resolution' => 'Новое значение'
   *    ],
   *    'deadline_property' => [
   *      'deadline' => 12345678
   *      'deadline_label_uuid => 955e83f9-7e7d-4bed-88a2-3b9e13e51bfd
   *    ]
   *  ]
   * @endcode
   */
  public function getPropertiesChangesFromOptions(array $options, array $keys = null);
  
  /**
   * @param array|null $keys
   *  Идентификаторы свойств, которые проверяются на
   *  наличие для них новых значений
   *
   * @return array
   *  Массив, хранящий информацию о свойствах,
   *  для которых теперь установлены новые значения.
   *
   *  Может быть несколько значений.
   *  (Например, не просто 'deadline', а 'deadline' и 'deadline_label_uuid'),
   *  в массиве будет информация о каждом таком значении, которое поменялось
   *
   * @code
   *  $result = [
   *    'resolution_property' => [
   *      'resolution' => 'Новое значение'
   *    ],
   *    'deadline_property' => [
   *      'deadline' => 12345678
   *      'deadline_label_uuid => 955e83f9-7e7d-4bed-88a2-3b9e13e51bfd
   *    ]
   *  ]
   * @endcode
   */
  public function getPropertiesChanges(array $keys = null);

  /**
   * Возвращает все значения установленных свойств
   *
   * @return array
   */
  public function propertiesToArray();

}