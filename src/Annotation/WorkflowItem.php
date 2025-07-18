<?php

namespace Snr\Workflows\Annotation;

/**
 * @Annotation
 */
class WorkflowItem {

  /**
   * @var string
   */
  public $id;

  /**
   * @var string
   */
  public $label;

  /**
   * @var string
   */
  public $description;

}