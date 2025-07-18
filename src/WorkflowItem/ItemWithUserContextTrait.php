<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\Entity\UserInterface;

/**
 * Trait ItemWithUserContextTrait
 *
 * Если пользователь не был установлен через вызовы метода setContext,
 * то по умолчанию будет использоваться текущий аутентифицированный пользователь
 */
trait ItemWithUserContextTrait {

  /**
   * {@inheritdoc}
   */
  public function getUserFromContext() {
    $root = $this->getRoot();
    if (!$root) $root = $this;
    $context = $root->getContext();

    if (isset($context['user_id'])) {
      $user = $this->getPluginManager()->getUserStorage()->load($context['user_id']);
      if (!$user) {
        $type_definition = $this->getPluginDefinition();
        $message =
          "Пользователя c id \"{$context['user_id']}\", в контексте которого идет работа с элементом типа \"{$type_definition['label']}\", не существует";
        throw new \InvalidArgumentException($message);
      }
    }
    else {
      $user = $this->getPluginManager()->getUserStorage()->getCurrentUser();
    }

    return $user;
  }

  /**
   * {@inheritdoc}
   */
  public function userFromContextIsOneOfParentUser(int $depth = 0) {
    /**
     * @var $user UserInterface
     */
    $user = $this->getUserFromContext();
    /**
     * @var $root AbstractGroupInterface
     */
    $root = $this->getRoot();
    // Все родительские элементы этого элемента, являющиеся элементами пользователей
    //  (реализуют ItemWithUserInterface)
    if ($root) {
      $parents = $root->getArrayParents($this->id());
      if (count($parents) >= 2) {
        array_pop($parents);
        $parents_count = count($parents);
        for ($i = $parents_count - 1;
             $i >= ($depth ? $parents_count - $depth : 0); $i--) {
          $parent = $parents[$i];
          if ($parent instanceof ItemWithUserInterface &&
            $parent->getUserUuid() == $user->uuid()) {
            return true;
          }
        }
      }
    }
    return false;
  }

}