<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class bm_product_notifications {
    public $code = 'bm_product_notifications';
    public $group = 'boxes';
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->title = MODULE_BOXES_PRODUCT_NOTIFICATIONS_TITLE;
      $this->description = MODULE_BOXES_PRODUCT_NOTIFICATIONS_DESCRIPTION;

      if ( defined('MODULE_BOXES_PRODUCT_NOTIFICATIONS_STATUS') ) {
        $this->sort_order = MODULE_BOXES_PRODUCT_NOTIFICATIONS_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_PRODUCT_NOTIFICATIONS_STATUS == 'True');

        $this->group = ((MODULE_BOXES_PRODUCT_NOTIFICATIONS_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    public function execute() {
      global $PHP_SELF, $request_type, $oscTemplate;

      if (isset($_GET['products_id'])) {
        if (isset($_SESSION['customer_id'])) {
          $check_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . (int)$_GET['products_id'] . "' and customers_id = '" . (int)$_SESSION['customer_id'] . "'");
          $check = tep_db_fetch_array($check_query);

          $notification_exists = (($check['count'] > 0) ? true : false);
        } else {
          $notification_exists = false;
        }

        $notif_contents = '';

        if ($notification_exists == true) {
          $notif_contents = '<a href="' . tep_href_link($PHP_SELF, tep_get_all_get_params(array('action')) . 'action=notify_remove', $request_type) . '"><span class="fa fa-remove"></span> ' . sprintf(MODULE_BOXES_PRODUCT_NOTIFICATIONS_BOX_NOTIFY_REMOVE, tep_get_products_name($_GET['products_id'])) .'</a>';
        } else {
          $notif_contents = '<a href="' . tep_href_link($PHP_SELF, tep_get_all_get_params(array('action')) . 'action=notify', $request_type) . '"><span class="fa fa-envelope"></span> ' . sprintf(MODULE_BOXES_PRODUCT_NOTIFICATIONS_BOX_NOTIFY, tep_get_products_name($_GET['products_id'])) .'</a>';
        }

        ob_start();
        include('includes/modules/boxes/templates/product_notifications.php');
        $data = ob_get_clean();

        $oscTemplate->addBlock($data, $this->group);
      }
    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_BOXES_PRODUCT_NOTIFICATIONS_STATUS');
    }

    public function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Product Notifications Module', 'MODULE_BOXES_PRODUCT_NOTIFICATIONS_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_PRODUCT_NOTIFICATIONS_CONTENT_PLACEMENT', 'Right Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_PRODUCT_NOTIFICATIONS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    public function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_BOXES_PRODUCT_NOTIFICATIONS_STATUS', 'MODULE_BOXES_PRODUCT_NOTIFICATIONS_CONTENT_PLACEMENT', 'MODULE_BOXES_PRODUCT_NOTIFICATIONS_SORT_ORDER');
    }
  }
