<?php

namespace Snr\Workflows;

use Snr\Workflows\Entity\UserStorageInterface;
use Snr\Workflows\Manager\WorkflowItemManagerInterface;

/**
 * Только те сервисы, которые нужны логике рабочих процессов
 */
interface DependencyInterface {

  /**
   * @param $id
   *
   * @return mixed
   */
  public function getService($id);

  /**
   * @return WorkflowItemManagerInterface
   */
  public function getWorkflowItemManager();

  /**
   * @return UserStorageInterface
   */
  public function getUserStorage();

}