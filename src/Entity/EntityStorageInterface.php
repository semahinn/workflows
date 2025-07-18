<?php

namespace Snr\Workflows\Entity;

interface EntityStorageInterface {

  /**
   * @param int $id
   *
   * @return EntityInterface
   */
  public function load(int $id);

  /**
   * @param array $properties
   *
   * @return EntityInterface[]
   */
  public function loadByProperties(array $properties);

}