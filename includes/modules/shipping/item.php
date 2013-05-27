<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class item extends shipping_abstract {
    protected function initialize() {
      $this->_title = MODULE_SHIPPING_ITEM_TITLE;
      $this->_public_title = MODULE_SHIPPING_ITEM_PUBLIC_TITLE;
      $this->_description = MODULE_SHIPPING_ITEM_DESCRIPTION;
      $this->_installed = defined('MODULE_SHIPPING_ITEM_STATUS');

      if ( isset($this->_order) ) {
        $this->_enabled = (MODULE_SHIPPING_ITEM_STATUS == 'True') ? true : false;
        $this->_sort_order = MODULE_SHIPPING_ITEM_SORT_ORDER;
        $this->_tax_class_id = MODULE_SHIPPING_ITEM_TAX_CLASS;
        $this->_shipping_zone_class_id = MODULE_SHIPPING_ITEM_ZONE;

        if ( $this->isEnabled() && !$this->hasValidShippingZone() ) {
          $this->_enabled = false;
        }
      }
    }

    public function getQuote() {
      $data = array(array('id' => $this->_code,
                          'cost' => (MODULE_SHIPPING_ITEM_COST * $this->getNumberOfItems()) + MODULE_SHIPPING_ITEM_HANDLING));

      if ( osc_not_null(MODULE_SHIPPING_ITEM_SHIPPING_METHOD) ) {
        $data[0]['title'] = MODULE_SHIPPING_ITEM_SHIPPING_METHOD;
      }

      return $data;
    }

    protected function getParams() {
      $params = array('MODULE_SHIPPING_ITEM_STATUS' => array('title' => 'Enable Item Shipping',
                                                             'desc' => 'Do you want to offer per item rate shipping?',
                                                             'value' => 'True',
                                                             'set_func' => 'osc_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_SHIPPING_ITEM_COST' => array('title' => 'Shipping Cost',
                                                           'desc' => 'The shipping cost will be multiplied by the number of items in an order that uses this shipping method.',
                                                           'value' => '2.50'),
                      'MODULE_SHIPPING_ITEM_HANDLING' => array('title' => 'Handling Fee',
                                                                      'desc' => 'Handling fee for this shipping method.',
                                                                      'value' => '0'),
                      'MODULE_SHIPPING_ITEM_TAX_CLASS' => array('title' => 'Tax Class',
                                                                'desc' => 'Use the following tax class on the shipping cost.',
                                                                'value' => '0',
                                                                'use_func' => 'osc_get_tax_class_title',
                                                                'set_func' => 'osc_cfg_pull_down_tax_classes('),
                      'MODULE_SHIPPING_ITEM_ZONE' => array('title' => 'Shipping Zone',
                                                           'desc' => 'If a zone is selected, only enable this shipping method for that zone.',
                                                           'value' => '0',
                                                           'use_func' => 'osc_get_zone_class_title',
                                                           'set_func' => 'osc_cfg_pull_down_zone_classes('),
                      'MODULE_SHIPPING_ITEM_SORT_ORDER' => array('title' => 'Sort Order',
                                                                 'desc' => 'Sort order of display.',
                                                                 'value' => '0'));

      return $params;
    }

    protected function getNumberOfItems() {
      global $OSCOM_PDO;

      $number_of_items = $_SESSION['cart']->count_contents();

      if ( $_SESSION['cart']->get_content_type() == 'mixed' ) {
        $number_of_items = 0;

        foreach ( $_SESSION['cart']->get_products() as $p ) {
          if ( isset($p['attributes']) ) {
            foreach ( $p['attributes'] as $pa ) {
              $Qcheck = $OSCOM_PDO->prepare('select pa.products_id from :table_products_attributes pa, :table_products_attributes_download pad where pa.products_id = :products_id and pa.options_values_id = :options_values_id and pa.products_attributes_id = pad.products_attributes_id');
              $Qcheck->bindInt(':products_id', $p['id']);
              $Qcheck->bindInt(':options_values_id', $pa['value_id']);
              $Qcheck->execute();

              if ( $Qcheck->fetch() !== false ) {
                continue 2;
              }
            }
          }

          $number_of_items += $p['quantity'];
        }
      }

      return $number_of_items;
    }
  }
?>
