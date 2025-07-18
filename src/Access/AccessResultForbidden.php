<?php

namespace Snr\Workflows\Access;

/**
 * Value object indicating a forbidden access result.
 */
class AccessResultForbidden extends AccessResult implements AccessResultReasonInterface {

  /**
   * The reason why access is forbidden. For use in error messages.
   *
   * @var string|null
   */
  protected $reason;

  /**
   * Constructs a new AccessResultForbidden instance.
   *
   * @param null|string $reason
   *   (optional) A message to provide details about this access result.
   */
  public function __construct(?string $reason = NULL) {
    $this->reason = $reason;
  }

  /**
   * {@inheritdoc}
   */
  public function isForbidden() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getReason() {
    return $this->reason;
  }

  /**
   * {@inheritdoc}
   */
  public function setReason($reason) {
    $this->reason = $reason;
    return $this;
  }
}
