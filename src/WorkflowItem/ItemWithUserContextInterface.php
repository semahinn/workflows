<?php

namespace Snr\Workflows\WorkflowItem;

use Snr\Workflows\Entity\UserInterface;

/**
 * Предполагается, что любое действие с экземпляром WorkflowItemInterface
 * выполняется в контексте (от) определенного пользователя.
 * В массиве контекста этот пользователь хранится под ключом 'user_id'
 *
 *  @code
 *    $context = $real_workflow->getContext();
 *    $context['user_id'] = $admin1->id();
 *    $real_workflow->setContext($context);
 *  @endcode
 */
interface ItemWithUserContextInterface {

  /**
   * @return UserInterface
   *  Пользователь, в контексте (от) которого выполняются
   *  все действия с этим этапом
   *
   * @throws
   */
  public function getUserFromContext();

  /**
   * Пользователь, в контексте (от) которого выполняются
   * все действия с этим этапом, является пользователем (участником)
   * одного из родительских элементов (реализуют ItemWithUserInterface)
   *
   * @param int $depth
   *  Глубина (максимальное число искомых родительских этапов),
   *  которые учитываются при поиске
   *
   * @return bool
   */
  public function userFromContextIsOneOfParentUser(int $depth = 0);

}