<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class free extends shipping_abstract {
    protected function initialize() {
      $this->_title = MODULE_SHIPPING_FREE_TITLE;
      $this->_public_title = MODULE_SHIPPING_FREE_PUBLIC_TITLE;
      $this->_description = MODULE_SHIPPING_FREE_DESCRIPTION;
      $this->_installed = defined('MODULE_SHIPPING_FREE_STATUS');

      if ( isset($this->_order) ) {
        $this->_enabled = (MODULE_SHIPPING_FREE_STATUS == 'True') ? true : false;
        $this->_sort_order = MODULE_SHIPPING_FREE_SORT_ORDER;
        $this->_shipping_zone_class_id = MODULE_SHIPPING_FREE_ZONE;

        if ( $this->isEnabled() && (($this->_order->getInfo('subtotal') < MODULE_SHIPPING_ORDER_TOTAL_MINIMUM) || !$this->hasValidShippingZone()) ) {
          $this->_enabled = false;
        }
      }
    }

    public function getQuote() {
      $data = array(array('id' => $this->_code,
                          'cost' => 0));

      if ( osc_not_null(MODULE_SHIPPING_FREE_SHIPPING_METHOD) ) {
        $data[0]['title'] = MODULE_SHIPPING_FREE_SHIPPING_METHOD;
      }

      return $data;
    }

    protected function getParams() {
      $params = array('MODULE_SHIPPING_FREE_STATUS' => array('title' => 'Enable Free Shipping',
                                                             'desc' => 'Do you want to offer free shipping?',
                                                             'value' => 'True',
                                                             'set_func' => 'osc_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_SHIPPING_ORDER_TOTAL_MINIMUM' => array('title' => 'Minimum Order Total',
                                                                     'desc' => 'The minimum order total to apply free shipping to (a value of 0 applies to all orders).',
                                                                     'value' => '20'),
                      'MODULE_SHIPPING_FREE_ZONE' => array('title' => 'Shipping Zone',
                                                           'desc' => 'If a zone is selected, only enable free shipping method for that zone.',
                                                           'value' => '0',
                                                           'use_func' => 'osc_get_zone_class_title',
                                                           'set_func' => 'osc_cfg_pull_down_zone_classes('),
                      'MODULE_SHIPPING_FREE_SORT_ORDER' => array('title' => 'Sort Order',
                                                                 'desc' => 'Sort order of display.',
                                                                 'value' => '0'));

      return $params;
    }
  }
?>
