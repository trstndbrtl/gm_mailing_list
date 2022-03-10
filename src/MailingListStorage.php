<?php

namespace Drupal\gm_mailing_list;

use Drupal\Core\Database\Connection;
use Drupal\Core\State\StateInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides the default database storage backend for statistics.
 * 
 * @package Drupal\gm_mailing_list
 */
class MailingListStorage implements MailingListStorageInterface {

  /**
  * The database connection used.
  *
  * @var \Drupal\Core\Database\Connection
  */
  protected $connection;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs the statistics storage.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection for the node view storage.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(Connection $connection, StateInterface $state, RequestStack $request_stack) {
    $this->connection = $connection;
    $this->state = $state;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function storeSubscriber($name, $forname, $email, $phone, $status, $prefix, $type_mailing_list = 'defaulf') {
    return (bool) $this->connection
      ->merge('users_mailing_list')
      ->key('email', $email)
      ->insertFields(array(
        'name' => $uid_original,
        'forname' => $uid_target,
        'status' => $status,
        'type' => $type_mailing_list,
        'phone' => $phone,
        'prefix' => $prefix,
        'email' => $email,
      ))
      ->updateFields(array(
        'name' => $uid_original,
        'forname' => $uid_target,
        'status' => $status,
        'type' => $type_mailing_list,
        'phone' => $phone,
        'prefix' => $prefix,
        'email' => $email,
      ))->execute();
    }

}
