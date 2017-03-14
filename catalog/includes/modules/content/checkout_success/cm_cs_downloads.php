<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_cs_downloads {
    public $code;
    public $group;
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_CHECKOUT_SUCCESS_DOWNLOADS_TITLE;
      $this->description = MODULE_CONTENT_CHECKOUT_SUCCESS_DOWNLOADS_DESCRIPTION;

      if ( defined('MODULE_CONTENT_CHECKOUT_SUCCESS_DOWNLOADS_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_CHECKOUT_SUCCESS_DOWNLOADS_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_CHECKOUT_SUCCESS_DOWNLOADS_STATUS == 'True');
      }
    }

    public function execute() {
      global $oscTemplate;

      if ( DOWNLOAD_ENABLED == 'true' ) {
        ob_start();
        extract($GLOBALS, EXTR_SKIP);
        include('includes/modules/downloads.php');
        $template = ob_get_clean();

        $oscTemplate->addContent($template, $this->group);
      }
    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_CONTENT_CHECKOUT_SUCCESS_DOWNLOADS_STATUS');
    }

    public function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Product Downloads Module', 'MODULE_CONTENT_CHECKOUT_SUCCESS_DOWNLOADS_STATUS', 'True', 'Should ordered product download links be shown on the checkout success page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_CHECKOUT_SUCCESS_DOWNLOADS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '3', now())");
    }

    public function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_CONTENT_CHECKOUT_SUCCESS_DOWNLOADS_STATUS','MODULE_CONTENT_CHECKOUT_SUCCESS_DOWNLOADS_SORT_ORDER');
    }
  }
