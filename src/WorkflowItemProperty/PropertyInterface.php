<?php

namespace Snr\Workflows\WorkflowItemProperty;

/**
 * Interface PropertyInterface
 *
 * Экземпляр PropertyInterface описывает определенное свойство
 * этапа/маршрута, значение которого проходит определенные проверки.
 * Экземпляры PropertyInterface используются внутри методов
 *
 * Т.о., один раз создав экземпляр PropertyInterface, он будет "проходить"
 * через весь цикл вызовов, начинающийся с этих методов.
 */
interface PropertyInterface {

  /**
   * Содержит логику создания и проверки переданного значения
   *  на соответствие определенному типу и формату
   *
   * @param array $data
   *
   * @return static
   */
  public static function create(array $data);

  /**
   * @param array $data
   *
   * @return static
   */
  public function setValue(array $data);

  /**
   * @return array
   */
  public function getValue();

  /**
   * @return array
   */
  public function getInitValue();

  /**
   * @return bool
   *  true, если переменная создана
   *  Это нужно, чтобы находясь в методе setValue можно было понять,
   *   сейчас она создаётся её (был вызван метод create), или же
   *   setValue вызывается напрямую у созданного экземпляра
   */
  public function isCreated();

  /**
   * @return string
   */
  public static function getLabel();

  /**
   * @param string|null $key
   *
   * @return bool
   */
  public function isChanged(string $key = null);

  /**
   * @param PropertyInterface|NULL $new
   *  Если указано, то сранивает ТЕКУЩИЕ значения свойств этого экземпляра
   *  с ТЕКУЩИМИ значениями свойств экземпляра $new
   *
   * @return array
   *  Специальный метод, который возвращает информацию об
   *  изменениях значений свойств в удобном виде
   */
  public function changesSchema(PropertyInterface $new = null);

  /**
   * Метод для произвольного сравнения значений свойств
   *
   * @param array $l_value
   * @param array $r_value
   * @param string|null $key
   *
   * @return array
   *  Массив ключей отличающихся значений
   */
  public static function compare(array $l_value, array $r_value, string $key = null);

  /**
   * Условие создания (индикатор) того, что
   *  на основе данных $data можно попытаться создать
   *  экземпляр PropertyInterface
   *
   * @param array $data
   *
   * @return bool
   */
  public static function conditionToCreate(array $data);

  /**
   * @return array
   *  Идентификаторы ключей каждого из значений этого свойства
   */
  public static function getKeys();

}
