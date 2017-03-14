<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_header_messagestack {
    public $code;
    public $group;
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_HEADER_MESSAGESTACK_TITLE;
      $this->description = MODULE_CONTENT_HEADER_MESSAGESTACK_DESCRIPTION;

      if ( defined('MODULE_CONTENT_HEADER_MESSAGESTACK_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_HEADER_MESSAGESTACK_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_HEADER_MESSAGESTACK_STATUS == 'True');
      }
    }

    public function execute() {
      global $oscTemplate, $messageStack;

      if ($messageStack->size('header') > 0) {

        ob_start();
        include('includes/modules/content/' . $this->group . '/templates/messagestack.php');
        $template = ob_get_clean();

        $oscTemplate->addContent($template, $this->group);

      }
    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_CONTENT_HEADER_MESSAGESTACK_STATUS');
    }

    public function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Message Stack Notifications Module', 'MODULE_CONTENT_HEADER_MESSAGESTACK_STATUS', 'True', 'Should the Message Stack Notifications be shown in the header when needed? ', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_HEADER_MESSAGESTACK_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    public function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_CONTENT_HEADER_MESSAGESTACK_STATUS', 'MODULE_CONTENT_HEADER_MESSAGESTACK_SORT_ORDER');
    }
  }
