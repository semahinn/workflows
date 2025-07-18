<?php

namespace Snr\Workflows\Entity;

interface UserStorageInterface extends EntityStorageInterface {

  /**
   * @return UserInterface
   */
  public function getCurrentUser();

}