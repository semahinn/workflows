<?php

namespace Snr\Workflows;

class DependencySingleton
{
  /**
   * @var DependencyInterface
   */
  protected static $instance = null;

  public static function getInstance(array $services = []) {
    if (!static::$instance) {
      static::$instance = new Dependency($services);
    }
    return static::$instance;
  }

}