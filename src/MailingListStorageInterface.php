<?php

namespace Drupal\gm_mailing_list;
/**
 * Provides an interface defining Statistics Storage.
 *
 * @file
 * Contains Drupal\gm_mailing_list\MailingListStorageInterface.
 * 
 * Stores the views per day, total views and timestamp of last view
 * for entities.
 * 
 * @package Drupal\gm_mailing_list
 */
interface MailingListStorageInterface {

  /**
   * getFriendList
   *
   * @param string $name
   * @param string $forname
   * @param string $email
   * @param string $phone
   * @param string $prefix
   * @param int $status
   * @param string $type_mailing_list
   * 
   * @return void
   */
  public function storeSubscriber($name = NULL, $forname = NULL, $email = NULL, $phone = NULL, $prefix = NULL, $status = 1, $type_mailing_list = 'defaulf');

}
