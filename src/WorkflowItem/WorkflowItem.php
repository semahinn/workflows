<?php

namespace Snr\Workflows\WorkflowItem;

use Psr\Container\ContainerInterface;
use Snr\Workflows\Access\AccessResult;
use Snr\Workflows\Access\AccessResultInterface;
use Snr\Workflows\Manager\WorkflowItemManagerInterface;
use Snr\Workflows\Util\NestedArray;
use Snr\Workflows\WorkflowItemProperty\State;

/**
 * Является базовым классом для всех этапов
 */
abstract class WorkflowItem implements WorkflowItemInterface {

  use EditOperationTrait;
  use CompleteOperationTrait;
  use OperationTrait {
    doIt as workflowItemOperationTrait_doIt;
  }
  use ItemWithUserContextTrait;
  use ItemWithPropertiesTrait;
  
  /**
   * Идентификатор этапа
   *
   * @var string
   */
  protected $id;
  
  /**
   * Означает, что этап ещё НЕ был сохранён
   *
   * @var bool
   */
  protected $isNew;
  
  /**
   * @var string
   */
  protected $state = self::STATE_ACTIVE;

  /**
   * @var string
   */
  protected $label = '';

  /**
   * @var AbstractGroupInterface
   */
  protected $rootGroup;

  /**
   * @var array
   */
  protected $context = [];

  /**
   * @var array
   */
  protected $thirdPartySettings = [];

  /**
   * @var WorkflowItemManagerInterface
   */
  protected $pluginManager;

  /**
   * @param array $data
   *  Параметры создания этапа:
   *  1. Есть у любого этапа, устанавливаются в конструкторе:
   *  'is_new' - true, если элемент ещё не был сохранён,
   *  'id' - иденификатор этапа,
   *  'type' - идентификаор плагина (типа) этапа
   *
   *  'context' - контекст создания,
   *  'autocomplete' - флаг автоматического завершения,
   *  'third_party_settings' - дополнительные настройки, хранящиеся для этапа,
   *
   *  2. Свойства (атрибуты), устанавливаемые методом perform
   *  'state' - состояние этапа,
   *  'label' - отображаемое название этапа
   *
   * @param ContainerInterface $plugin_manager
   */
  public function __construct(array $data, ContainerInterface $plugin_manager) {

    $this->pluginManager = $plugin_manager;

    $this->isNew = true;
    if (isset($data['is_new']) && is_bool($data['is_new']))
      $this->isNew = $data['is_new'];

    $id = null;
    if (!empty($data['id']) && is_string($data['id']))
      $id = $data['id'];

    // id является атрибутом, который обязательно должен быть
    //  установлен при инициализации элемента
    if (!$id) {
      $class = static::class;
      $message = "Невозможно создать экземляр класса $class, т.к. свойство " .
        "'id' не установлено или не является строкой";
      throw new \InvalidArgumentException($message);
    }
    $this->id = $id;

    if (isset($data['context']) && is_array($data['context']))
      $this->setContext($data['context']);
    
    if (isset($data['autocomplete']))
      $this->setAutoCompleteFlag($data['autocomplete']);
    
    if (isset($data['third_party_settings']) && is_array($data['third_party_settings']))
      $this->setThirdPartySettings($data['third_party_settings']);

  }
  
  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function isNew() {
    return $this->isNew;
  }

  /**
   * {@inheritdoc}
   */
  public function setIsNew(bool $flag) {
    if ($this->isBuildOnly())
      $this->isNew = $flag;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel(string $label, array $options = []) {
    $options['label'] = $label;
    return $this->perform('Edit', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function getStateProperty() {
    return $this->properties['state_property'] ?? null;
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    return isset($this->properties['state_property']) ?
      $this->properties['state_property']->getValue()['state'] : null;
  }

  /**
   * {@inheritdoc}
   */
  public function setState(string $state, array $options = []) {
    $options['state'] = $state;
    return $this->perform('Edit', $options);
  }
  
  /**
   * {@inheritdoc}
   */
  public function isBlocked() {
    return false;
  }
  
  /**
   * @param array $data
   *
   * @throws
   */
  public function doCreate(array $data) {
    $this->filterDataWhenCreate($data);
    $this->alterDataWhenCreate($data);
    if ($data) $this->perform('Edit', $data);
  }

  /**
   * При создании этапа бывает необходимо установить
   * значения некоторых свойств, вызвав метод perform
   * Это удобно сделать одним вызовом, чтобы не вызывать
   * отдельный метод для каждого свойства (Напр., setLabel и setState)
   *
   * @param array $data
   *
   * @throws
   *
   * @return void
   */
  protected function alterDataWhenCreate(array &$data) {
    if (!State::getStateFromOptions($data)) $data['state'] = CompleteOperationInterface::STATE_ACTIVE;
  }

  /**
   * Сразу после установки встроенных свойств этапа
   * (устанавливаются в методе preCreate первым делом)
   * необходимо исключить их из массива $data,
   * т.к. они не предназначены для установки с помощью метода perform
   *
   * @param array $data
   */
  protected function filterDataWhenCreate(array &$data) {
    unset($data['context']);
    unset($data['autocomplete']);
    unset($data['third_party_settings']);
    unset($data['is_new']);
    unset($data['id']);
    unset($data['type']);
  }
  
  /**
   * @param array $data
   *
   * @return void
   */
  public function postCreate(array $data = []) {
    $context = $this->getContext();
    unset($context['build_only']);
    $this->setContext($context);
  }

  /**
   * {@inheritdoc}
   */
  protected function doIt(string $operation, array &$options) {
    $this->workflowItemOperationTrait_doIt($operation, $options);
    // Если элемент действительно был завершён - вызываем postComplete
    if ($operation == 'Complete' && $this->getState() == CompleteOperationInterface::STATE_COMPLETED) {
      if (method_exists($this, $method = 'postComplete'))
        $this->$method($options);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $options = []) {
    $this->perform('Submit', $options);
    return $this;
  }

  /**
   * Создание любого этапа (метод create) устроено таким образом, что
   * выполняющиеся внутри этого метода действия по установке значений этапа
   * НЕ устанавливают значение свойства rootGroup (корневая группа).
   * Но созданный этап должен "знать" к какой корневой группе он относится.
   *
   * Значение свойства rootGroup устанавливается в момент добавления этапа
   * в определённую группу (метод addItem у группы), метод updateRootOfAddedItem
   * содержит логику проверки этой возможности
   *
   * @param AbstractGroupInterface $group
   *
   * @return static
   * @throws
   */
  protected function updateRootOfAddedItem(AbstractGroupInterface $group) {
    $reason = '';
    if (!$this->checkRootHasItem($this, $group, $reason)) {
      $reason = "Невозможно установить группу с id = \"{$group->id()}\" ({$group->getLabel()})" .
        " в качестве родительской (ГЛАВНОЙ): $reason";
      throw new \Exception($reason);
    }
    $this->rootGroup = $group;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoot() {
    if ($this->rootGroup) return $this->rootGroup;
    return null;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setRoot(AbstractGroupInterface $group) {
    $reason = '';
    if (!$this->checkRootHasItem($this, $group, $reason)) {
      $reason = "Невозможно установить группу с id = '{$group->id()}' ({$group->getLabel()})" .
        " в качестве родительской: $reason";
      throw new \Exception($reason);
    }
    $this->rootGroup = $group;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setContext(array $context) {
    $this->context = $context;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return $this->context;
  }

  /**
   * @return static
   */
  protected function clear() {
    $this->label = '';
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isBuildOnly() {
    $context = $this->getContext();
    $build_only = false;
    if (isset($context['build_only']) && is_bool($context['build_only']) && $context['build_only'])
      $build_only = true;
    return $build_only;
  }

  /**
   * {@inheritdoc}
   */
  public function checkRootHasItem(WorkflowItemInterface $item, AbstractGroupInterface $root, string &$reason = '') {
    $array_parents = $root->getArrayParents($item->id());
    array_pop($array_parents);
    if (!$array_parents) {
      $item_type_definition = $this->getPluginDefinition();
      $item_label = $item->getLabel();
      if (!$item_label) $cut = "типа \"{$item_type_definition['label']}\"";
      else $cut = "\"$item_label\"";
      $reason = "Этап $cut не находится в группе (id = \"{$root->id()}\")";
      if ($root_label = $root->getLabel())
        $reason = "Этап $cut не находится в группе \"$root_label\" (id = \"{$root->id()}\")";
      return false;
    }
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public static function getAvailableOperations() {
    return [
      'Edit' => [
        'label' => "Редактировать",
        'no_ui' => false,
      ],
      'Complete' => [
        'label' => "Завершить",
        'no_ui' => false,
      ],
      'Submit' => [
        'label' => "Подтвердить",
        'no_ui' => true,
      ],
    ];
  }

  /**
   * Метод для описания логики выполнения операций
   *
   * Здесь просто происходит установка новых значений атрибутов
   * Все проверки ВЫПОЛНЕНЫ ранее в методе canOperation
   *
   * Переопределяется в классах наследниках
   *
   * @param string $operation
   * @param array $options
   */
  protected function doOperation(string $operation, array &$options) {
    // Операция "Edit" (Редактирование элемента)
    if ($operation == 'Edit') {
      if (array_key_exists('label', $options)) {
        $this->label = $options['label'];
      }
    }

    // Операция "Complete" (Завершение элемента)
    elseif ($operation == 'Complete') {
      // Все что происходит для операции 'Complete' в простейшем случае -
      // это установка состояния STATE_COMPLETED для элемента
      // Именно на это состояние ориентируется логика в postComplete, т.к. считаем, что
      // элемент действительно завершился, если его состояние теперь STATE_COMPLETED
      $this->state = CompleteOperationInterface::STATE_COMPLETED;
    }
  }

  /**
   * @param string $operation
   *
   * @param array $options
   */
  protected function postDoOperation(string $operation, array &$options) {
    // Новые значения свойств устанавливаются в последнюю очередь
    $this->doNewProperties($operation, $options);
  }

  /**
   * @param array $options
   *
   * @throws
   *
   * @see WorkflowItem::doIt()
   */
  protected function postComplete(array $options) {
    // Завершение считается выполненным, если состояние элемента стало "Завершено" (STATE_COMPLETED)
    if ($this->getState() == CompleteOperationInterface::STATE_COMPLETED && $this->getRoot()) {
      /**
       * @var $closest_parent AbstractGroupInterface
       */
      $closest_parent = $this->getClosestParent();
      if ($closest_parent->getGroupType() == AbstractGroupInterface::TYPE_SEQUENTIAL) {
        // Если находимся в последовательной группе, а следующий элемент помечен
        //  как "Автоматически завершаемый" (autoCompleteFlag), то он
        //  завершится сразу после завершения текущего элемента
        /**
         * @var $following_elements WorkflowItemInterface[]
         */
        $following_elements = $this->getFollowingElements();
        if ($following_elements) {
          $next = current($following_elements);
          // Игнорирую все дискреционные проверки (user_access)
          //  при автоматическом завершении
//          $this->addIgnoreUserAccessFlagInOptions($options);
          $complete_options = [
            'ignore_access' => ['user_access'],
            'autocomplete_nested_items' => true,
          ];
          if ($next->isElementReadyForAutoComplete($complete_options)) {
            $next->complete($complete_options);
          }
        }
        // Если этот элемент был последним незавершенным элементом в последовательной группе,
        //  и теперь он завершен, и группа помечена как "Автоматически завершаемая" - то завершаем группу
        else {
          // Игнорирую все дискреционные проверки (user_access)
          //  при автоматическом завершении
//          $this->addIgnoreUserAccessFlagInOptions($options);
          $complete_options = [
            'ignore_access' => ['user_access'],
          ];
          if ($closest_parent->isElementReadyForAutoComplete($complete_options)) {
            $closest_parent->complete($complete_options);
          }
        }
      }
      // Если этот элемент завершился находясь в параллельной группе,
      //  то в зависимости от оператора группы (OR, AND) (метод assumeGroupCanBeCompleted)
      //  определяем, можно ли считать эту параллельную группу завершённой
      elseif ($closest_parent->getGroupType() == AbstractGroupInterface::TYPE_PARALLEL) {
        // Игнорирую все дискреционные проверки (user_access)
        //  при автоматическом завершении
//        $this->addIgnoreUserAccessFlagInOptions($options);
        $complete_options = [
          'ignore_access' => ['user_access'],
        ];
        if ($closest_parent->assumeGroupCanBeCompleted() &&
          $closest_parent->isElementReadyForAutoComplete($complete_options)
        ) {
          $closest_parent->complete($complete_options);
        }
      }
    }
  }

  /**
   * Метод для описания условий разграничения доступа к разным операциям
   *
   * @param string $operation
   *
   * @param array $options
   *  'ignore_access' - Массив, в котором указано, какие из проверок будут отключены:
   *  1. 'default_access' - основной тип проверок. Эти проверки, как правило,
   *  связанны с основной логикой состояний этапов и групп. Их нельзя отключить
   *  2. 'user_access' - проверки, связанные с дискреционными разрешениями пользователя
   *
   * @code
   *   $item->complete(['ignore_access' => ['user_access']]);
   * @endcode
   *
   * @param AccessResultInterface[] $access_results
   *  Массив результатов доступа для каждого из типа проверок ('default_access', 'user_access' и т.д.)
   *
   * @see OperationTrait::can()
   * @see AccessResultInterface
   */
  protected function canOperation(string $operation, array &$options, array &$access_results) {
    // 1) Если элемент в состоянии "Завершён" (WorkflowItemInterface::STATE_COMPLETED),
    // то для него запрещены действия "Edit" и "Complete"

    // В режиме build_only многих проверок для действий не происходит
    // В процессе построения элементов вызываются методы set..., addItem и др., которые используют проверки,
    // опирающиеся на соотв. действия. В данном случае они игнорируются (иначе не построится маршрут)
    $build_only = $this->isBuildOnly();
    if ($build_only) return;

    $available_operations = static::getAvailableOperations();
    if ($this->getState() == CompleteOperationInterface::STATE_COMPLETED &&
      ($operation == 'Edit' || $operation == 'Complete')) {
      $cut = $this->getMessagePart1();
      if ($operation == 'Complete') {
        $reason = "Элемент $cut невозможно завершить, т.к. он уже завершён";
        $access_results['default_access'] = AccessResult::forbidden($reason);
        return;
      }
      elseif ($operation == 'Edit') {
        $reason = "Элемент $cut невозможно отредактировать, т.к. он уже завершён";
        $access_results['edit_when_item_is_completed'] = AccessResult::forbidden($reason);
      }
    }

    $root = $this->getRoot();
    // 2) Если есть хотя бы одна группа из иерархии сверху над элементом с состоянием "Завершено",
    // то для него запрещены ВСЕ действия ($operation)
    if ($root) {
      /**
       * @see AbstractGroupInterface::getArrayParents
       */
      $array_parents = $root->getArrayParents($this->id());
      array_pop($array_parents);
      foreach (array_reverse($array_parents) as $parent)
        if ($parent->getState() == CompleteOperationInterface::STATE_COMPLETED) {
          $cut = $this->getMessagePart1();
          $reason = "Над элементом $cut невозможно выполнить действие \"{$available_operations[$operation]['label']}\"" .
            ", т.к. группа из его иерархии сверху уже \"Завершена\"";
          $access_results['edit_when_item_is_completed'] = AccessResult::forbidden($reason);
          return;
        }
    }

    // УСЛОВИЯ РАЗГРАНИЧЕНИЯ ДОСТУПА К ОПЕРАЦИИ "Edit" (Редактирование элемента)
    //
    // Для любого минимального элемента есть возможность
    //  отредактировать только 'label' и 'state'
    //  (state - меняется в т.ч. и при вызове метода complete или других)
    //
    //  (id - устанавливается при инициализации, нед. для изменения;
    //  isNew - устанавливается при инициализации, меняется при сохранении, нед. для изменения)
    //
    if ($operation == 'Edit') {
      if (array_key_exists('label', $options)) {
        $new_label = $options['label'];
        // Нет особых ограничений на изменение 'label'
        // TODO: Возможно, необходимо проверять на длину строки (256 симв.) или допустимые символы в названии
        if ($new_label && !is_string($new_label)) {
          $cut = $this->getMessagePart1();
          $reason = "Для элемента $cut свойство \"label\" должно быть строкой";
          $access_results['default_access'] = AccessResult::forbidden($reason);
          return;
        }
      }

      if (array_key_exists('state', $options)) {
        $new_state = $options['state'];
        if (!($new_state == CompleteOperationInterface::STATE_COMPLETED ||
          $new_state == CompleteOperationInterface::STATE_ACTIVE)) {
          $cut = $this->getMessagePart1();
          $reason = "Для элемента $cut свойство \"state\" должно иметь одно из допустимых значений: \"start\", \"completed\"";
          $access_results['default_access'] = AccessResult::forbidden($reason);
        }

        if (!$build_only) {
          // Если элемент еще не был сохранён, мы можем указать ему любое состояние
          //  (его сразу можно обозначить как "completed"
          // Если же элемент был сохранён с состоянием "start", то перевести его в "completed"
          //  можно только с помощью действия завершения (метод complete())
          if (!$this->isBuildOnly() && !$this->isNew() && $new_state == CompleteOperationInterface::STATE_COMPLETED &&
            $this->state == CompleteOperationInterface::STATE_ACTIVE) {
            $interface = CompleteOperationInterface::class;
            $cut = $this->getMessagePart1();
            $message = "Для экземпляра $cut, свойство " .
              "\"state\" невозможно установить в состояние \"completed\" (Завершить элемент). " .
              "Для завершения необходимо использовать метод complete (см. \"$interface::complete()\")";
            $access_results['default_access'] = AccessResult::forbidden($message);
            return;
          }
        }
      }
    }

    // УСЛОВИЯ РАЗГРАНИЧЕНИЯ ДОСТУПА К ОПЕРАЦИИ "Complete" (Завершение элемента)
    elseif ($operation == 'Complete') {
      // Элемент нельзя ЗАВЕРШИТЬ:
      //
      // 1) Если элемент не является одним из "Текущих",
      //  то его нельзя завершить
      //  (см. \Snr\Workflows\Item\AbstractGroupInterface::getTree())
      //
      // Если группа параллельная, то можно ЗАВЕРШИТЬ в любой момент
      //
      $root = $this->getRoot();
      if (!$root) {
        $access_results['default_access'] = AccessResult::neutral();
        return;
      }

      $tree = $root->getTree('list', true);
      if (!isset($tree[$this->id()])) {
        $cut = $this->getMessagePart1();
        $reason = "Элемент $cut невозможно завершить, т.к. он находится в группе, а элементы перед ним или выше по иерархии еще не завершены";
        $access_results['default_access'] = AccessResult::forbidden($reason);
        return;
      }
    }
  }

  public function getMessagePart1() {
    $type_definition = $this->getPluginDefinition();
    $label = $this->getLabel();
    if (!$label) $cut = "типа \"{$type_definition['label']}\"";
    else $cut = "\"$label\"";
    return $cut;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getClosestParent() {
    /**
     * @var $this WorkflowItemInterface
     */
    $root = $this->getRoot();
    if (!$root) {
      return null;
//      $reason = "Для элемента с id \"{$this->id()}\" ({$this->getLabel()}) " .
//        "невозможно найти его родительскую группу, т.к. он не находится в группе";
//      throw new \Exception($reason);
    }

    /**
     * @see AbstractGroupInterface::getArrayParents
     */
    $array_parents = $root->getArrayParents($this->id());
    array_pop($array_parents);
    // Ближайшая родительская группа, в которой и расположен элемент
    return $array_parents[array_key_last($array_parents)];
  }

  /**
   * {@inheritdoc}
   */
  public function getFollowingElements() {
    /**
     * @see AbstractGroupInterface::getArrayParents
     */
    $closest_parent = $this->getClosestParent();
    $items = $closest_parent->getItems();
    $values = array_values($items);
    $combined = array_combine(array_keys($values), array_keys($items));
    $pos = array_search($this->id(), $combined);
    $following = array_slice($values, $pos + 1);
    if (!$following) return [];
    return $following;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousElements() {
    /**
     * @see AbstractGroupInterface::getArrayParents
     */
    $closest_parent = $this->getClosestParent();
    $items = $closest_parent->getItems();
    $values = array_values($items);
    $combined = array_combine(array_keys($values), array_keys($items));
    $pos = array_search($this->id(), $combined);
    $previous = array_slice($values, 0, $pos);
    if (!$previous) return [];
    return $previous;
  }

  /**
   * {@inheritdoc}
   */
  protected function propertiesSettings() {
    return [
      'state_property' => [
        'class' => State::class,
        'label' => 'Состояние',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setThirdPartySettings($settings, array $path = null) {
    if (!$path)
      return $this->thirdPartySettings = $settings;
    else NestedArray::setValue($this->thirdPartySettings, $path, $settings);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getThirdPartySettings(array $path = null) {
    if (!$path)
      return $this->thirdPartySettings;
    else return NestedArray::getValue($this->thirdPartySettings, $path);
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $data['id'] = $this->id();
    $data['is_new'] = $this->isNew();
    $data['state'] = $this->getState();
    $data['label'] = $this->getLabel();
    $data['autocomplete'] = $this->autoCompleteFlag();
    $data['type'] = $this->getPluginId();
    $data['third_party_settings'] = $this->getThirdPartySettings();
    return array_merge($data, $this->propertiesToArray());
  }
  
  /**
   * @return WorkflowItemManagerInterface
   */
  public final function getPluginManager() {
    return $this->pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public final function getPluginDefinition() {
    return $this->getPluginManager()->getDefinitionByClass(static::class);
  }
  
  /**
   * {@inheritdoc}
   */
  public final function getPluginId() {
    return $this->getPluginDefinition()['id'];
  }
  
}