<?php

namespace Snr\Workflows\Entity;

interface UserInterface extends EntityInterface {

  /**
   * @return string
   */
  public function getDisplayName();

  /**
   * @return string
   */
  public function getUsername();

}