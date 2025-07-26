<?php

namespace Snr\Workflows\Tests\Entity;

use Snr\Workflows\Entity\UserStorageInterface;

class MockUserStorage implements UserStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function load(int $id) {
    if (isset(MockUser::USERS[$id])) {
      $user = MockUser::USERS[$id];
      return new MockUser($user['id'], $user['uuid'], $user['display_name'], $user['username']);
    }
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function loadByProperties(array $properties) {
    $results = [];
    foreach (MockUser::USERS as $id => $user) {
      if ((isset($properties['id']) && $properties['id'] == $user['id']) ||
        (isset($properties['uuid']) && $properties['uuid'] == $user['uuid']) ||
        (isset($properties['username']) && $properties['username'] == $user['username'])) {
        $results[$id] = $user;
      }
    }
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentUser() {
    $user = current(MockUser::USERS);
    return new MockUser($user['id'], $user['uuid'], $user['display_name'], $user['username']);
  }

}