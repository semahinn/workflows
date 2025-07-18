<?php

namespace Snr\Workflows\WorkflowItem;

interface CodeInterface extends WorkflowItemInterface {

  /**
   * @param callable $function
   *
   * @return static
   */
  public function setFunction($function);

  /**
   * @param array $context
   *
   * @return mixed
   */
  public function doCode(array $context);

  /**
   * @return mixed
   */
  public function getResult();

}