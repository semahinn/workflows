<?php

namespace Snr\Workflows\WorkflowItemProperty;

interface SingleKeyInterface extends PropertyInterface {

  /**
   * @return string
   */
  public static function getSingleKey();

}