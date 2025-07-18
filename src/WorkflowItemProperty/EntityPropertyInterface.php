<?php

namespace Snr\Workflows\WorkflowItemProperty;

use Snr\Workflows\Entity\EntityInterface;

interface EntityPropertyInterface extends PropertyInterface {

  // TODO: Сделать, чтобы этот экземпляр
  //  мог хранить несколько uuid и соотв. им label.
  //
  // getEntityUuid будет возвращать массив uuid
  // Т.е. [0 => '8d87cf21-66d9-4516-8bf5-58fd28710d9a',
  //      1 => '4d0a0432-8045-41b9-8be2-c09e45812574']
  // getEntityLabel будет возвращать массив label для этих экземляров
  // Т.е. [0 => 'Термин 1',
  //       1 => 'Термин 2']
  // getEntityInstance по аналогии
  //  будет возвращать массив экземляров ContentEntityInterface

  /**
   * @return string
   */
  public function getEntityUuid();

  /**
   * @return string
   */
  public function getEntityLabel();

  /**
   * @return EntityInterface
   */
  public function getEntityInstance();

  /**
   * @return EntityInterface[]
   */
  public static function getAllowedEntityInstances();

  /**
   * Массив, где ключами являются uuid экземпляров сущности,
   *  а значениями - их названия (label)
   *
   * @return array
   */
  public static function getAllowedEntityOptions();

  /**
   * @return string
   */
  public static function getEntityType();

  /**
   * @return string
   */
  public static function getEntityBundle();

  /**
   * Ключ массива, под которым будет храниться uuid экземпляра (-ов) сущности
   *
   * @return string
   */
  public static function getKeyForEntityUuid();

  /**
   * Ключ массива, под которым будет храниться label экземпляра (-ов) сущности
   *
   * @return mixed
   */
  public static function getKeyForEntityLabel();

}