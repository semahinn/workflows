<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\Entity\UserInterface;

/**
 * Описывает работу со свойством "Пользователь" (user_uuid)
 * (Пользователь этого этапа)
 */
interface ItemWithUserInterface extends WorkflowItemInterface {

  /**
   * @param string $uuid
   *
   * @param array $options
   *
   * @return static
   */
  public function setUserUuid(string $uuid, array $options = []);

  /**
   * @return UserInterface
   */
  public function getUserInstance();

  /**
   * @return string
   */
  public function getUserUuid();

  /**
   * Пользователь этого этапа является текущим пользователем
   *
   * @return bool
   */
  public function userFromContextIsRealUser();

}