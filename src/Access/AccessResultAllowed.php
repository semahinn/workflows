<?php

namespace Snr\Workflows\Access;

/**
 * Value object indicating an allowed access result.
 */
class AccessResultAllowed extends AccessResult {

  /**
   * {@inheritdoc}
   */
  public function isAllowed() {
    return TRUE;
  }
}
