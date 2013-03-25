<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class ot_tax {
    var $title, $output;

    function ot_tax(order $OSCOM_Order) {
      $this->code = 'ot_tax';
      $this->title = MODULE_ORDER_TOTAL_TAX_TITLE;
      $this->description = MODULE_ORDER_TOTAL_TAX_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_TAX_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_TAX_SORT_ORDER;

      $this->_order = $OSCOM_Order;

      $this->output = array();
    }

    function process() {
      global $currencies;

      $tax_groups = array();

      foreach ( $_SESSION['cart']->get_products() as $p ) {
        $tax_rate = osc_get_tax_rate($p['tax_class_id'], $this->_order->getTaxAddress('country_id'), $this->_order->getTaxAddress('zone_id'));

        if ( $tax_rate > 0 ) {
          $tax_description = osc_get_tax_description($p['tax_class_id'], $this->_order->getTaxAddress('country_id'), $this->_order->getTaxAddress('zone_id'));

          $shown_price = $currencies->calculate_price($p['final_price'], $tax_rate, $p['quantity']);

          if ( DISPLAY_PRICE_WITH_TAX == 'true' ) {
            $tax = $shown_price - ($shown_price / (($tax_rate < 10) ? '1.0' . str_replace('.', '', $tax_rate) : '1.' . str_replace('.', '', $tax_rate)));

            if ( isset($tax_groups[$tax_description]) ) {
              $tax_groups[$tax_description] += $tax;
            } else {
              $tax_groups[$tax_description] = $tax;
            }
          } else {
            $tax = ($tax_rate / 100) * $shown_price;

            if ( isset($tax_groups[$tax_description]) ) {
              $tax_groups[$tax_description] += $tax;
            } else {
              $tax_groups[$tax_description] = $tax;
            }
          }
        }
      }

      foreach ( $tax_groups as $key => $value ) {
        if ( $value > 0 ) {
          $this->output[] = array('title' => $key . ':',
                                  'text' => $currencies->format($value),
                                  'value' => $value);
        }
      }
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = osc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_TAX_STATUS'");
        $this->_check = osc_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_TAX_STATUS', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER');
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Tax', 'MODULE_ORDER_TOTAL_TAX_STATUS', 'true', 'Do you want to display the order tax value?', '6', '1','osc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '3', 'Sort order of display.', '6', '2', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>
