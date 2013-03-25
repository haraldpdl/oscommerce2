<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class flat extends shipping_abstract {
    protected function initialize() {
      global $OSCOM_PDO;

      $this->_title = MODULE_SHIPPING_FLAT_TITLE;
      $this->_public_title = MODULE_SHIPPING_FLAT_PUBLIC_TITLE;
      $this->_description = MODULE_SHIPPING_FLAT_DESCRIPTION;
      $this->_installed = defined('MODULE_SHIPPING_FLAT_STATUS');

      if ( isset($this->_order) ) {
        $this->_enabled = (MODULE_SHIPPING_FLAT_STATUS == 'True') ? true : false;
        $this->_sort_order = MODULE_SHIPPING_FLAT_SORT_ORDER;
        $this->_tax_class_id = MODULE_SHIPPING_FLAT_TAX_CLASS;
        $this->_shipping_zone_class_id = MODULE_SHIPPING_FLAT_ZONE;

        if ( $this->isEnabled() && !$this->hasValidShippingZone() ) {
          $this->_enabled = false;
        }
      }
    }

    public function getQuote() {
      $data = array(array('id' => $this->_code,
                          'cost' => MODULE_SHIPPING_FLAT_COST));

      if ( osc_not_null(MODULE_SHIPPING_FLAT_SHIPPING_METHOD) ) {
        $data[0]['title'] = MODULE_SHIPPING_FLAT_SHIPPING_METHOD;
      }

      return $data;
    }

    protected function getParams() {
      $params = array('MODULE_SHIPPING_FLAT_STATUS' => array('title' => 'Enable Flat Shipping',
                                                             'desc' => 'Do you want to offer flat rate shipping?',
                                                             'value' => 'True',
                                                             'set_func' => 'osc_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_SHIPPING_FLAT_COST' => array('title' => 'Shipping Cost',
                                                           'desc' => 'The shipping cost for all orders using this shipping method.',
                                                           'value' => '5.00'),
                      'MODULE_SHIPPING_FLAT_TAX_CLASS' => array('title' => 'Tax Class',
                                                                'desc' => 'Use the following tax class on the shipping cost.',
                                                                'value' => '0',
                                                                'use_func' => 'osc_get_tax_class_title',
                                                                'set_func' => 'osc_cfg_pull_down_tax_classes('),
                      'MODULE_SHIPPING_FLAT_ZONE' => array('title' => 'Shipping Zone',
                                                           'desc' => 'If a zone is selected, only enable this shipping method for that zone.',
                                                           'value' => '0',
                                                           'use_func' => 'osc_get_zone_class_title',
                                                           'set_func' => 'osc_cfg_pull_down_zone_classes('),
                      'MODULE_SHIPPING_FLAT_SORT_ORDER' => array('title' => 'Sort Order',
                                                                 'desc' => 'Sort order of display.',
                                                                 'value' => '0'));

      return $params;
    }
  }
?>
