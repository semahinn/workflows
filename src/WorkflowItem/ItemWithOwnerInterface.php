<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\Entity\UserInterface;

/**
 * Описывает работу со свойством "Владелец" (Автор) (owner_uuid) этапа
 */
interface ItemWithOwnerInterface extends WorkflowItemInterface {

  /**
   * @param string $owner_uuid
   *
   * @param array $options
   *
   * @return static
   */
  public function setOwnerUuid(string $owner_uuid, array $options = []);

  /**
   * @return UserInterface
   */
  public function getOwner();

  /**
   * @return string
   */
  public function getOwnerUuid();

}