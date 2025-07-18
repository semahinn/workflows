<?php

namespace Snr\Workflows\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Snr\Plugin\Manager\ByPluginClassInterface;
use Snr\Plugin\Manager\PluginManagerInterface;
use Snr\Workflows\Entity\UserStorageInterface;

interface WorkflowItemManagerInterface extends PluginManagerInterface, ByPluginClassInterface {

  /**
   * @return EventDispatcherInterface
   */
  public function getEventDispatcher();

  /**
   * @return UserStorageInterface
   */
  public function getUserStorage();

}