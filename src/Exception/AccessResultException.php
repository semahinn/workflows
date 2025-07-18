<?php

namespace Snr\Workflows\Exception;

class AccessResultException extends \Exception {

  /**
   * @var string
   */
  protected $resultType;

  /**
   * @var string
   */
  protected $operation;

  /**
   * @var string
   */
  protected $uiMessage;

  /**
   * @var string
   */
  protected $systemMessage;

  public function __construct(string $result_type,
                              string $operation = 'Edit',
                              string $ui_message = '',
                              string $system_message = '',
                              $code = 0,
                              \Throwable $previous = NULL) {
    parent::__construct($ui_message, $code, $previous);
    $this->resultType = $result_type;
    $this->operation = $operation;
    $this->systemMessage = $system_message;
  }

  /**
   * @return string
   */
  public function getResultType(): string {
    return $this->resultType;
  }

  /**
   * @return string
   */
  public function getOperation(): string {
    return $this->operation;
  }

  /**
   * @return string
   */
  public function getSystemMessage(): string {
    return $this->systemMessage;
  }

  /**
   * @return string
   */
  public function getUiMessage(): string {
    return $this->uiMessage;
  }

}
