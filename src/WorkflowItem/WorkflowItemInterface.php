<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Plugin\Plugin\PluginableInstanceInterface;
use Snr\Workflows\WorkflowItemProperty\StateInterface;

/**
 * Описывает любой этап рабочего процесса
 */
interface WorkflowItemInterface extends PluginableInstanceInterface, CompleteOperationInterface, EditOperationInterface, ItemWithUserContextInterface, ItemWithPropertiesInterface, BlockedItemInterface {

  /**
   * Возвращает идентификатор этапа.
   *
   * @return string
   */
  public function id();

  /**
   * Означает, что этап ещё не был сохранён.
   *
   * @return bool
   */
  public function isNew();

  /**
   * Позволяет для этапа явно установить, что он ещё не был сохранён.
   *
   * Это может пригодиться, например, когда необходимо
   * повторно выполнить логику, которая выполняется только для
   * несохранённого этапа.
   *
   * @param bool $flag
   *
   * @return static
   */
  public function setIsNew(bool $flag);

  /**
   * @return StateInterface|null
   */
  public function getStateProperty();
  
  /**
   * Текущее состояние этапа
   *
   * @return string
   */
  public function getState();

  /**
   * Устанавливает состояние этапа
   *
   * @param string $state
   *  Новое состояние этапа
   *
   * @param array $options
   *
   * @return static
   */
  public function setState(string $state, array $options = []);

  /**
   * Отображаемое название этапа
   *
   * @return string
   */
  public function getLabel();

  /**
   * Устанавливает отображаемое название этапа
   *
   * @param string $label
   *  Новое название этапа
   *
   * @param array $options
   *
   * @return static
   */
  public function setLabel(string $label, array $options = []);

  /**
   * Логика подтверждения элемента перед сохранением
   *
   * Код здесь может описывать установку определенных свойств,
   * которые, например, подготовливают объект WorkflowItemInterface перед
   * сохранением (Информация для отправки уведомлений,
   * записей в лог, информация для других подсистем портала и т.д.)
   *
   * @param array $options
   *
   * @return static
   */
  public function submit(array $options = []);

  /**
   * @param array $context
   *
   * @return static
   */
  public function setContext(array $context);

  /**
   * @return array
   */
  public function getContext();

  /**
   * Возвращает true, если в текущий момент происходит
   * ПОСТРОЕНИЕ элемента после загрузки его данных из хранилища.
   *
   * В таком случае выполняются только ОСНОВНЫЕ проверки,
   * которые не помешают построению элемента, остальные проверки игнорируются.
   * (см. как используется isBuildOnly в методах doCreate, canOperation и doOperation)
   *
   * @return bool
   */
  public function isBuildOnly();

  /**
   * @param AbstractGroupInterface $group
   *
   * @return static
   */
  public function setRoot(AbstractGroupInterface $group);

  /**
   * @return AbstractGroupInterface
   */
  public function getRoot();

  /**
   * Проверяет, что экземпляр $item
   * действительно находится внутри группы $root.
   *
   * @param WorkflowItemInterface $item
   * @param AbstractGroupInterface $root
   * @param string $reason
   *
   * @return bool
   */
  public function checkRootHasItem(WorkflowItemInterface $item, AbstractGroupInterface $root, string &$reason = '');

  /**
   * Если элемент находится в группе, то возвращает его ПЕРВУЮ от него (ближайшую) родительскую группу из иерархии
   *
   * @return AbstractGroupInterface
   */
  public function getClosestParent();

  /**
   * @return WorkflowItemInterface[]
   *  Если элемент находится в группе, то возвращает все этапы, расположенные ПОСЛЕ него
   *
   * @throws
   */
  public function getFollowingElements();

  /**
   * @return WorkflowItemInterface[]
   *  Если элемент находится в группе, то возвращает все этапы, расположенные ПЕРЕД ним
   *
   * @throws
   */
  public function getPreviousElements();

  /**
   * @param array $settings
   * @param array|null $path
   *
   * @return static
   */
  public function setThirdPartySettings(array $settings, array $path = null);

  /**
   * @param array|null $path
   *
   * @return mixed
   */
  public function getThirdPartySettings(array $path = null);

  /**
   * @return array
   */
  public function toArray();

  /**
   * Возвращает определение плагина этого этапа
   *
   * @return array
   */
  public function getPluginDefinition();

  /**
   * Возвращает идентификатор плагина этого этапа.
   *
   * @return string
   */
  public function getPluginId();

}
