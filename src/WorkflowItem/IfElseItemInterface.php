<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\PassivatedInterface;

interface IfElseItemInterface extends GroupInterface, PassivatedInterface {

  /**
   * @param WorkflowItemInterface $item
   *
   * @return static
   */
  public function setTrueItem(WorkflowItemInterface $item);

  /**
   * @param WorkflowItemInterface $item
   *
   * @return static
   */
  public function setFalseItem(WorkflowItemInterface $item);

  /**
   * @return WorkflowItemInterface
   */
  public function getTrueItem();

  /**
   * @return WorkflowItemInterface
   */
  public function getFalseItem();

  /**
   * @param array $options
   *
   * @return bool
   */
  public function condition(array $options);

  /**
   * @param callable $function
   *
   * @return mixed
   */
  public function setCondition($function);

}