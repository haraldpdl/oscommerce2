<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_cs_product_notifications {
    public $code;
    public $group;
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_TITLE;
      $this->description = MODULE_CONTENT_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_DESCRIPTION;

      if ( defined('MODULE_CONTENT_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_STATUS == 'True');
      }
    }

    public function execute() {
      global $oscTemplate, $order_id;

      if ( isset($_SESSION['customer_id']) ) {
        $global_query = tep_db_query("select global_product_notifications from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int)$_SESSION['customer_id'] . "'");
        $global = tep_db_fetch_array($global_query);

        if ( $global['global_product_notifications'] != '1' ) {
          if ( isset($_GET['action']) && ($_GET['action'] == 'update') ) {
            if ( isset($_POST['notify']) && is_array($_POST['notify']) && !empty($_POST['notify']) ) {
              $notify = array_unique($_POST['notify']);

              foreach ( $notify as $n ) {
                if ( is_numeric($n) && ($n > 0) ) {
                  $check_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . (int)$n . "' and customers_id = '" . (int)$_SESSION['customer_id'] . "' limit 1");

                  if ( !tep_db_num_rows($check_query) ) {
                    tep_db_query("insert into " . TABLE_PRODUCTS_NOTIFICATIONS . " (products_id, customers_id, date_added) values ('" . (int)$n . "', '" . (int)$_SESSION['customer_id'] . "', now())");
                  }
                }
              }
            }
          }

          $products_displayed = array();

          $products_query = tep_db_query("select products_id, products_name from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "' order by products_name");
          while ($products = tep_db_fetch_array($products_query)) {
            if ( !isset($products_displayed[$products['products_id']]) ) {
              $products_displayed[$products['products_id']] = '<div class="checkbox"><label> ' . tep_draw_checkbox_field('notify[]', $products['products_id']) . ' ' . $products['products_name'] . '</label></div>';
            }
          }

          $products_notifications = implode('', $products_displayed);

          ob_start();
          include('includes/modules/content/' . $this->group . '/templates/product_notifications.php');
          $template = ob_get_clean();

          $oscTemplate->addContent($template, $this->group);
        }
      }
    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_CONTENT_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_STATUS');
    }

    public function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Product Notifications Module', 'MODULE_CONTENT_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_STATUS', 'True', 'Should the product notifications block be shown on the checkout success page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '3', now())");
    }

    public function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_CONTENT_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_STATUS','MODULE_CONTENT_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_SORT_ORDER');
    }
  }
