<?php

namespace Snr\Workflows\Manager;

use Snr\Workflows\WorkflowItem\AbstractGroupInterface;
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
  
  public function __construct(array $namespaces,
                              EventDispatcherInterface $event_dispatcher) {
    $this->eventDispatcher = $event_dispatcher;
    parent::__construct('WorkflowItem', $namespaces, WorkflowItemInterface::class, WorkflowItem::class);
    $this->factory = new WorkflowItemFactory($this, WorkflowItemInterface::class);
  }

  /**
   * {@inheritdoc}
   */
  public function getEventDispatcher() {
    return $this->eventDispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstances(array $items_array, array $context = [], AbstractGroupInterface $root = null) {
    $instances = [];

    foreach ($items_array as $item) {
      if (!(isset($item['type']) && is_string($item['type']))) {
        $interface = WorkflowItemInterface::class;
        $message = "Невозможно создать экземпляр этапа/маршрута " .
          "({$interface}), т.к. тип элемента не указан (св-во \"type\")";
        throw new \InvalidArgumentException($message);
      }

      if ($definition = $this->getDefinition($item['type'], true)) {
        unset($item['type']);
        $item['context'] = $context;
        if ($root) $item['root_when_create'] = $root;
        $instances[] = $this->createInstance($definition['type'], $item);
      }
    }

    return $instances;
  }

}