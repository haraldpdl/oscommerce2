<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class ar_reset_password {
    public $code = 'ar_reset_password';
    public $title;
    public $description;
    public $sort_order = 0;
    public $minutes = 5;
    public $attempts = 1;
    public $identifier;

    public function __construct() {
      $this->title = MODULE_ACTION_RECORDER_RESET_PASSWORD_TITLE;
      $this->description = MODULE_ACTION_RECORDER_RESET_PASSWORD_DESCRIPTION;

      if ($this->check()) {
        $this->minutes = (int)MODULE_ACTION_RECORDER_RESET_PASSWORD_MINUTES;
        $this->attempts = (int)MODULE_ACTION_RECORDER_RESET_PASSWORD_ATTEMPTS;
      }
    }

    public function setIdentifier() {
      $this->identifier = tep_get_ip_address();
    }

    public function canPerform($user_id, $user_name) {
      $check_query = tep_db_query("select id from :table_action_recorder where module = '" . tep_db_input($this->code) . "' and user_name = '" . tep_db_input($user_name) . "' and date_added >= date_sub(now(), interval " . (int)$this->minutes  . " minute) and success = 1 order by date_added desc limit " . (int)$this->attempts);
      if (tep_db_num_rows($check_query) == $this->attempts) {
        return false;
      } else {
        return true;
      }
    }

    public function expireEntries() {
      tep_db_query("delete from :table_action_recorder where module = '" . tep_db_input($this->code) . "' and date_added < date_sub(now(), interval " . (int)$this->minutes  . " minute)");

      return tep_db_affected_rows();
    }

    public function check() {
      return defined('MODULE_ACTION_RECORDER_RESET_PASSWORD_MINUTES');
    }

    public function install() {
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Allowed Minutes', 'MODULE_ACTION_RECORDER_RESET_PASSWORD_MINUTES', '5', 'Number of minutes to allow password resets to occur.', '6', '0', now())");
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Allowed Attempts', 'MODULE_ACTION_RECORDER_RESET_PASSWORD_ATTEMPTS', '1', 'Number of password reset attempts to allow within the specified period.', '6', '0', now())");
    }

    public function remove() {
      tep_db_query("delete from :table_configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_ACTION_RECORDER_RESET_PASSWORD_MINUTES', 'MODULE_ACTION_RECORDER_RESET_PASSWORD_ATTEMPTS');
    }
  }
