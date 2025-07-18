<?php

namespace Snr\Workflows\Entity;

interface EntityInterface {

  /**
   * @return int
   */
  public function id();

  /**
   * @return string
   */
  public function uuid();

}