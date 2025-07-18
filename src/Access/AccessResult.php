<?php

namespace Snr\Workflows\Access;

abstract class AccessResult implements AccessResultInterface {

  /**
   * Creates an AccessResultInterface object with isNeutral() === TRUE.
   *
   * @param string|null $reason
   *   (optional) The reason why access is neutral. Intended for developers,
   *   hence not translatable.
   *
   * @return AccessResultNeutral
   *   isNeutral() will be TRUE.
   */
  public static function neutral(?string $reason = NULL) {
    assert(is_string($reason) || is_null($reason));
    return new AccessResultNeutral($reason);
  }

  /**
   * Creates an AccessResultInterface object with isAllowed() === TRUE.
   *
   * @return AccessResultAllowed
   *   isAllowed() will be TRUE.
   */
  public static function allowed() {
    return new AccessResultAllowed();
  }

  /**
   * Creates an AccessResultInterface object with isForbidden() === TRUE.
   *
   * @param string|null $reason
   *   (optional) The reason why access is forbidden. Intended for developers,
   *   hence not translatable.
   *
   * @return AccessResultForbidden
   *   isForbidden() will be TRUE.
   */
  public static function forbidden(?string $reason = NULL) {
    assert(is_string($reason) || is_null($reason));
    return new AccessResultForbidden($reason);
  }

  /**
   * Creates an allowed or neutral access result.
   *
   * @param bool $condition
   *   The condition to evaluate.
   *
   * @return AccessResultInterface
   *   If $condition is TRUE, isAllowed() will be TRUE, otherwise isNeutral()
   *   will be TRUE.
   */
  public static function allowedIf(bool $condition): AccessResultInterface {
    return $condition ? static::allowed() : static::neutral();
  }

  /**
   * Creates a forbidden or neutral access result.
   *
   * @param bool $condition
   *   The condition to evaluate.
   * @param string|null $reason
   *   (optional) The reason why access is forbidden. Intended for developers,
   *   hence not translatable
   *
   * @return AccessResultInterface
   *   If $condition is TRUE, isForbidden() will be TRUE, otherwise isNeutral()
   *   will be TRUE.
   */
  public static function forbiddenIf(bool $condition, ?string $reason = NULL) {
    return $condition ? static::forbidden($reason) : static::neutral();
  }

  /**
   * {@inheritdoc}
   *
   * @see AccessResultAllowed
   */
  public function isAllowed() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   *
   * @see AccessResultForbidden
   */
  public function isForbidden() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   *
   * @see AccessResultNeutral
   */
  public function isNeutral() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function orIf(AccessResultInterface $other) {
    if ($this->isForbidden() || $other->isForbidden()) {
      $result = static::forbidden();
      if ($this->isForbidden() && $this instanceof AccessResultReasonInterface && !is_null($this->getReason())) {
        $result->setReason($this->getReason());
      }
      elseif ($other->isForbidden() && $other instanceof AccessResultReasonInterface && !is_null($other->getReason())) {
        $result->setReason($other->getReason());
      }
    }
    elseif ($this->isAllowed() || $other->isAllowed()) {
      $result = static::allowed();
    }
    else {
      $result = static::neutral();
      if ($this instanceof AccessResultReasonInterface && !is_null($this->getReason())) {
        $result->setReason($this->getReason());
      }
      elseif ($other instanceof AccessResultReasonInterface && !is_null($other->getReason())) {
        $result->setReason($other->getReason());
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function andIf(AccessResultInterface $other) {
    if ($this->isForbidden() || $other->isForbidden()) {
      $result = static::forbidden();
      if (!$this->isForbidden()) {
        if ($other instanceof AccessResultReasonInterface) {
          $result->setReason($other->getReason());
        }
      }
      else {
        if ($this instanceof AccessResultReasonInterface) {
          $result->setReason($this->getReason());
        }
      }
    }
    elseif ($this->isAllowed() && $other->isAllowed()) {
      $result = static::allowed();
    }
    else {
      $result = static::neutral();
      if (!$this->isNeutral()) {
        if ($other instanceof AccessResultReasonInterface) {
          $result->setReason($other->getReason());
        }
      }
      else {
        if ($this instanceof AccessResultReasonInterface) {
          $result->setReason($this->getReason());
        }
      }
    }
    return $result;
  }
}
