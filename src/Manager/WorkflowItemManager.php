<?php

namespace Snr\Workflows\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Snr\Plugin\Manager\ByPluginClassTrait;
use Snr\Plugin\Manager\DefaultPluginManager;
use Snr\Workflows\Factory\WorkflowItemFactory;
use Snr\Workflows\Entity\UserStorageInterface;
use Snr\Workflows\WorkflowItem\WorkflowItemInterface;
use Snr\Workflows\Annotation\WorkflowItem;

/**
 * Является основной точкой доступа к работе с функционалом этапов рабочих процессов
 *
 * Сюда внедряются все зависимости, которые необходимы для функционирования
 * логики этапов рабочих процессов
 *
 * Так, каждый этап рабочего процесса (экземпляр WorkflowItemInterface),
 * может обращаться у этому менеджеру с помощью своего метода getPluginManager
 *
 * Впрочем, если внедряемых зависимостей не хватает (Например, вы создали
 * свой кастомный этап, создав класс плагина "@WorkflowItem"), то можно
 * переопределить логику создания этапов, за которую отвечает класс фабрики WorkflowItemFactory,
 * создав свою фабрику, реализующую WorkflowItemFactoryInterface
 *
 * @see WorkflowItemInterface::getPluginManager()
 * @see WorkflowItemFactory
 */
class WorkflowItemManager extends DefaultPluginManager implements WorkflowItemManagerInterface {
  
  use ByPluginClassTrait;

  /**
   * @var EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * @var UserStorageInterface
   */
  protected $userStorage;
  
  public function __construct(array $namespaces,
                              EventDispatcherInterface $event_dispatcher,
                              UserStorageInterface $user_storage) {
    $this->eventDispatcher = $event_dispatcher;
    $this->userStorage = $user_storage;
    parent::__construct('WorkflowItem', $namespaces, WorkflowItemInterface::class, WorkflowItem::class);
    $this->factory = new WorkflowItemFactory($this, WorkflowItemInterface::class);
  }

  /**
   * @return EventDispatcherInterface
   */
  public function getEventDispatcher() {
    return $this->eventDispatcher;
  }

  /**
   * @return UserStorageInterface
   */
  public function getUserStorage() {
    return $this->userStorage;
  }

}