<?php

namespace Snr\Workflows;

trait PassivateTrait {

  protected $is_passivated = false;

  /**
   * {@inheritdoc}
   */
  public function setPassivated(bool $flag) {
    $this->is_passivated = $flag;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPassivated()
  {
    return $this->is_passivated;
  }

  protected static function getPassivateOptions()
  {
    return [
      'allow_passivate' => 'Блок имеет возможность быть запассивированным',
    ];
  }

}