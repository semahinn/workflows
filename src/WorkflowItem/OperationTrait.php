<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\Access\AccessResult;
use Snr\Workflows\Access\AccessResultForbidden;
use Snr\Workflows\Access\AccessResultInterface;
use Snr\Workflows\Event\OperationEvent;
use Snr\Workflows\Exception\AccessResultException;

trait OperationTrait {

  /**
   * @var string
   */
  protected $lastAccessResultForbiddenType = '';

  /**
   * Вызывает методы с логикой действия для элемента WorkflowItemInterface
   *
   * Сначала вызывает "doOperation" для действия $operation,
   * затем порождает событие OperationEvent для этого действия c префиксом 'post'
   *
   * @param string $operation
   *  Действие с этапом
   *
   * @param array $options
   *
   * @return $this
   *
   * @throws
   *
   */
  protected function doIt(string $operation, array &$options) {
    // Не порождаю события OperationEvent перед вызовом метода doOperation,
    // необходимо чтобы логика в doOperation всегда выполнялась первой
    if (method_exists($this, 'doOperation'))
      $this->doOperation($operation, $options);

    $event = new OperationEvent($this, 'do', $operation, 'post', $options);
    $event_dispatcher = $this->getPluginManager()->getEventDispatcher();
    $event_dispatcher->dispatch(OperationEvent::EVENT_NAME, $event);

    if (method_exists($this, 'postDoOperation'))
      $this->postDoOperation($operation, $options);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function can(string $operation, array &$options) {
    $available_operations = static::getAvailableOperations();
    // ПРОВЕРКА того, что требуемое действие ($operation) поддерживается
    if (!(isset($available_operations[$operation]))) {
      $class = static::class;
      $message = "Wrong operation \"{$available_operations[$operation]['label']}\". Can't use with $class";
      throw new \InvalidArgumentException($message);
    }

    $access_results = [];

    $event = new OperationEvent($this, 'can', $operation, 'pre', $options);
    $event_dispatcher = $this->getPluginManager()->getEventDispatcher();
    $event_dispatcher->dispatch(OperationEvent::EVENT_NAME, $event);

    $result = $this->orIfResults($access_results, $options);
    if ($result instanceof AccessResultForbidden) return $result;

    if (method_exists($this, 'canOperation')) {
      $this->canOperation($operation, $options, $access_results);
      $result = $this->orIfResults($access_results, $options);
      if ($result instanceof AccessResultForbidden) return $result;
    }

    $event = new OperationEvent($this, 'can', $operation, 'post', $options);
    $event_dispatcher = $this->getPluginManager()->getEventDispatcher();
    $event_dispatcher->dispatch(OperationEvent::EVENT_NAME, $event);

    return $this->orIfResults($access_results, $options);
  }

  /**
   * @param AccessResultInterface[] $access_results
   *
   * @param array $options
   *
   * @return AccessResultInterface
   */
  protected function orIfResults(array $access_results, array &$options): AccessResultInterface {
    // No results means no opinion.
    if (empty($access_results)) {
      return AccessResult::neutral();
    }

    if (isset($options['ignore_access']) && is_array($options['ignore_access'])) {
      foreach ($options['ignore_access'] as $ignored) {
        if ($ignored != 'default_access')
          unset($access_results[$ignored]);
      }
    }

    if (empty($access_results))
      return AccessResult::neutral();

    /** @var AccessResultInterface $result */
    $type = array_key_first($access_results);
    $result = array_shift($access_results);
    foreach ($access_results as $type => $other) {
      $result = $result->orIf($other);
      if ($result instanceof AccessResultForbidden) {
        $this->lastAccessResultForbiddenType = $type;
        return $result;
      }
    }

    if ($result instanceof AccessResultForbidden)
      $this->lastAccessResultForbiddenType = $type;

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function perform(string $operation, array $options) {
    if (($access = $this->can($operation, $options)) && $access instanceof AccessResultForbidden)
      throw new AccessResultException($this->getLastAccessResultForbiddenType(), $operation, $access->getReason());
    $this->doIt($operation, $options);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setIgnoreAccessInOptions(array &$options, string $key = 'user_access') {
    if (!empty($options['ignore_access']) &&
      in_array($key, $options['ignore_access']))
      $options['ignore_access'] += ['user_access'];
    else $options['ignore_access'] = ['user_access'];
  }

  /**
   * {@inheritdoc}
   */
  public function getIgnoreAccessFromOptions(array $options) {
    if (!empty($options['ignore_access']) && is_array($options['ignore_access']))
      return $options['ignore_access'];
    else return [];
  }

  /**
   * {@inheritdoc}
   */
  public function isAccessForbidden(array $access_results, array &$options) {
    return $this->orIfResults($access_results, $options) instanceof AccessResultForbidden;
  }

  /**
   * @return string
   */
  public function getLastAccessResultForbiddenType() {
    return $this->lastAccessResultForbiddenType;
  }

}
