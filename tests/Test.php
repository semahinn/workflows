<?php

namespace Snr\Workflows\Tests;

use PHPUnit\Framework\TestCase;
use Snr\Plugin\Adapter\SymfonyContainerAdapter;
use Snr\Plugin\Tests\TestKernel;
use Snr\Workflows\Dependency;
use Snr\Workflows\DependencySingleton;
use Snr\Workflows\Tests\Entity\MockUser;
use Snr\Workflows\Tests\Entity\MockUserStorage;
use Snr\Workflows\Tests\WorkflowItem\MockUserItem;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Snr\Workflows\Manager\WorkflowItemManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\DependencyInjection\Reference;

class Test extends TestCase {

  public function testMain() {

    $root = dirname(__FILE__, 2);
    require_once "$root/vendor/autoload.php";

    $container = new ContainerBuilder();

    $event_dispatcher = new EventDispatcher();
    $user_storage = new MockUserStorage();

    $container->register('event_dispatcher', $event_dispatcher);
    $container->register('user_storage', $user_storage);

    $container->register('workflow_item_manager', WorkflowItemManager::class)
      ->addArgument(["Snr\Workflows\Tests" => "$root/tests"])
      ->addArgument(new Reference('event_dispatcher'));

    $container->register('workflow_item_dependency', DependencySingleton::class)
      ->addArgument(new Reference('event_dispatcher'))
      ->addMethodCall('getInstance', [[
        'workflow_item_manager' => new Reference('workflow_item_manager'),
        'user_storage' => new Reference('user_storage')
      ]]);

    $container->compile();
    $container->get('workflow_item_dependency');

    $dave = MockUserItem::create([
      'id' => 'user_1',
      'user_uuid' => MockUser::dave()->uuid(),
    ]);
    //    или
//    $dave = $workflow_manager->createInstance('mock_user_item', [
//      'id' => 'user_1',
//      'user_uuid' => MockUser::dave()->uuid(),
//    ]);

    $dave->addItem(MockUserItem::create([
      'id' => 'user_1_1',
      'user_uuid' => MockUser::mike()->uuid(),
    ]));
    $dave->addItem(MockUserItem::create([
      'id' => 'user_1_2',
      'user_uuid' => MockUser::peter()->uuid(),
    ]));

  }

}