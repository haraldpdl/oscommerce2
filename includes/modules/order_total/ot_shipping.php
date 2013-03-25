<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  class ot_shipping {
    var $title, $output;

    function ot_shipping(order $OSCOM_Order) {
      $this->code = 'ot_shipping';
      $this->title = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
      $this->description = MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_SHIPPING_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER;

      $this->_order = $OSCOM_Order;

      $this->output = array();
    }

    function process() {
      global $currencies;

      if ( $this->_order->hasShipping() ) {
        $this->_order->setInfo('total', $this->_order->getInfo('total') + $this->_order->getShipping('cost'));

        if ( $this->_order->hasShippingTax() ) {
          $shipping_tax_rate = $this->_order->getShippingTaxRate();

          if ( $shipping_tax_rate > 0 ) {
            $shipping_tax = osc_calculate_tax($this->_order-getShipping('cost'), $shipping_tax_rate);
            $shipping_tax_description = osc_get_tax_description($this->_order->getShipping('tax_class_id'), $this->_order->getShippingAddress('country_id'), $this->_order->getShippingAddress('zone_id'));

            $this->_order->setInfo('tax', $this->_order->getInfo('tax') + $shipping_tax);

            $otaxg = $this->_order->getInfo('tax_groups');
            if ( isset($otaxg[$shipping_tax_description]) ) {
              $otaxg[$shipping_tax_description] += $shipping_tax;
            } else {
              $otaxg[$shipping_tax_description] = $shipping_tax;
            }
            $this->_order->setInfo('tax_groups', $otaxg);

            $this->_order->setInfo('total', $this->_order->getInfo('total') + $shipping_tax);

//            if (DISPLAY_PRICE_WITH_TAX == 'true') $order->info['shipping_cost'] += osc_calculate_tax($order->info['shipping_cost'], $shipping_tax);
          }
        }

        $this->output[] = array('title' => $this->_order->getShipping('title') . ':',
                                'text' => $currencies->format($this->_order->getShipping('cost')),
                                'value' => $this->_order->getShipping('cost'));
      }
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = osc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_SHIPPING_STATUS'");
        $this->_check = osc_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION');
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Shipping', 'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', 'Do you want to display the order shipping cost?', '6', '1','osc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '2', 'Sort order of display.', '6', '2', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Allow Free Shipping', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false', 'Do you want to allow free shipping?', '6', '3', 'osc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Free Shipping For Orders Over', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', 'Provide free shipping for orders over the set amount.', '6', '4', 'currencies->format', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Provide Free Shipping For Orders Made', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national', 'Provide free shipping for orders sent to the set destination.', '6', '5', 'osc_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>
