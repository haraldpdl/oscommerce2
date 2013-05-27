<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class cod extends payment_abstract {
    protected function initialize() {
      $this->_title = MODULE_PAYMENT_COD_TITLE;
      $this->_public_title = MODULE_PAYMENT_COD_PUBLIC_TITLE;
      $this->_description = MODULE_PAYMENT_COD_DESCRIPTION;
      $this->_installed = defined('MODULE_PAYMENT_COD_STATUS');

      if ( isset($this->_order) ) {
        $this->_enabled = (MODULE_PAYMENT_COD_STATUS == 'True') ? true : false;
        $this->_sort_order = MODULE_PAYMENT_COD_SORT_ORDER;
        $this->_order_status_id = MODULE_PAYMENT_COD_ORDER_STATUS_ID;
        $this->_billing_zone_class_id = MODULE_PAYMENT_COD_ZONE;

// disable the module if the order only contains virtual products
        if ( $this->isEnabled() && !$this->_order->requireShipping() ) {
          $this->_enabled = false;
        }

        if ( $this->isEnabled() && !$this->hasValidBillingZone() ) {
          $this->_enabled = false;
        }
      }
    }

    protected function getParams() {
      $params = array('MODULE_PAYMENT_COD_STATUS' => array('title' => 'Enable Cash On Delivery Payments',
                                                           'desc' => 'Do you want to accept Cash On Delivery payments?',
                                                           'value' => 'True',
                                                           'set_func' => 'osc_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_COD_ORDER_STATUS_ID' => array('title' => 'Order Status',
                                                                    'desc' => 'The order status will be set to this value when this payment method has been selected.',
                                                                    'value' => '0',
                                                                    'use_func' => 'osc_get_order_status_name',
                                                                    'set_func' => 'osc_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_COD_ZONE' => array('title' => 'Billing Zone',
                                                         'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                                                         'value' => '0',
                                                         'use_func' => 'osc_get_zone_class_title',
                                                         'set_func' => 'osc_cfg_pull_down_zone_classes('),
                      'MODULE_PAYMENT_COD_SORT_ORDER' => array('title' => 'Sort Order',
                                                               'desc' => 'Sort order of display.',
                                                               'value' => '0'));

      return $params;
    }
  }
?>
