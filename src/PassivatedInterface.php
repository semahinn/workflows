<?php

namespace Snr\Workflows;

interface PassivatedInterface {

  /**
   * @return bool
   */
  public function isPassivated();

  /**
   * @param bool $flag
   *
   *
   * @return static
   */
  public function setPassivated(bool $flag);

}