<?php

namespace Snr\Workflows\Tests\Entity;

use Snr\Workflows\Entity\UserInterface;

class MockUser implements UserInterface {

  const USERS = [
    '1' => [
      'id' => '1',
      'username' => 'john',
      'display_name' => 'John Doe',
      'uuid' => 'c51f22fb-a192-48dc-8d43-eba46e81d822',
    ],
    '2' => [
      'id' => '2',
      'username' => 'peter',
      'display_name' => 'Peter Griffin',
      'uuid' => '4f93e298-4b7e-40af-b435-2888941a61fa',
    ],
    '3' => [
      'id' => '3',
      'username' => 'mike',
      'display_name' => 'Mike Vazovski',
      'uuid' => '98d173f3-41ba-4535-a2a4-6676d9a98a81',
    ],
    '4' => [
      'id' => '4',
      'username' => 'dave',
      'display_name' => 'Dave Mustaine',
      'uuid' => 'a2cd0de0-3ff5-47b8-a25c-443cc9c9196a',
    ],
    '5' => [
      'id' => '5',
      'username' => 'sean',
      'display_name' => 'Sean O\'Neill',
      'uuid' => '69d32965-162c-4ff4-a9a4-d29f93cc4647',
    ],
  ];

  protected $id;

  protected $uuid;

  protected $displayName;

  protected $username;

  public function __construct(int $id, string $uuid, string $display_name, string $username) {
    $this->id = $id;
    $this->uuid = $uuid;
    $this->displayName = $display_name;
    $this->username = $username;
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
  public function uuid() {
    return $this->uuid;
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayName() {
    return $this->displayName;
  }

  /**
   * {@inheritdoc}
   */
  public function getUsername() {
    return $this->username;
  }

}