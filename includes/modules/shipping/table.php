<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class table extends shipping_abstract {
    protected function initialize() {
      $this->_title = MODULE_SHIPPING_TABLE_TITLE;
      $this->_public_title = MODULE_SHIPPING_TABLE_PUBLIC_TITLE;
      $this->_description = MODULE_SHIPPING_TABLE_DESCRIPTION;
      $this->_installed = defined('MODULE_SHIPPING_TABLE_STATUS');

      if ( isset($this->_order) ) {
        $this->_enabled = (MODULE_SHIPPING_TABLE_STATUS == 'True') ? true : false;
        $this->_sort_order = MODULE_SHIPPING_TABLE_SORT_ORDER;
        $this->_tax_class_id = MODULE_SHIPPING_TABLE_TAX_CLASS;
        $this->_shipping_zone_class_id = MODULE_SHIPPING_TABLE_ZONE;

        if ( $this->isEnabled() && !$this->hasValidShippingZone() ) {
          $this->_enabled = false;
        }
      }
    }

    public function getQuote() {
      if ( MODULE_SHIPPING_TABLE_MODE == 'price' ) {
        $order_total = $this->getShippableTotal();
      } else {
        $order_total = $_SESSION['cart']->show_weight();
      }

      $shipping = 0;

      $table_cost = preg_split('/[:,]/', MODULE_SHIPPING_TABLE_COST);
      $size = sizeof($table_cost);
      for ($i=0, $n=$size; $i<$n; $i+=2) {
        if ($order_total <= $table_cost[$i]) {
          $shipping = $table_cost[$i+1];
          break;
        }
      }

      if ( MODULE_SHIPPING_TABLE_MODE == 'weight' ) {
        $shipping_boxes = 1;

        if ( $order_total > SHIPPING_MAX_WEIGHT ) {
          $shipping_boxes = ceil($order_total / SHIPPING_MAX_WEIGHT);
        }

        $shipping = $shipping * $shipping_boxes;
      }

      $data = array(array('id' => $this->_code,
                          'cost' => $shipping + MODULE_SHIPPING_TABLE_HANDLING));

      if ( osc_not_null(MODULE_SHIPPING_TABLE_SHIPPING_METHOD) ) {
        $data[0]['title'] = MODULE_SHIPPING_TABLE_SHIPPING_METHOD;
      }

      return $data;
    }

    protected function getParams() {
      $params = array('MODULE_SHIPPING_TABLE_STATUS' => array('title' => 'Enable Table Rate Shipping',
                                                              'desc' => 'Do you want to offer table rate shipping?',
                                                              'value' => 'True',
                                                              'set_func' => 'osc_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_SHIPPING_TABLE_COST' => array('title' => 'Shipping Cost Table',
                                                            'desc' => 'The shipping cost is based on the total cost or weight of items. Example: 25:8.50,50:5.50 up to 25 charge 8.50, from there to 50 charge 5.50.',
                                                            'value' => '25:8.50,50:5.50'),
                      'MODULE_SHIPPING_TABLE_HANDLING' => array('title' => 'Handling Fee',
                                                            'desc' => 'Handling fee for this shipping method.',
                                                            'value' => '0'),
                      'MODULE_SHIPPING_TABLE_MODE' => array('title' => 'Calculation Method',
                                                            'desc' => 'The shipping cost is based on the order total or the total weight of the items ordered.',
                                                            'value' => 'weight',
                                                            'set_func' => 'osc_cfg_select_option(array(\'weight\', \'price\'), '),
                      'MODULE_SHIPPING_TABLE_TAX_CLASS' => array('title' => 'Tax Class',
                                                                 'desc' => 'Use the following tax class on the shipping cost.',
                                                                 'value' => '0',
                                                                 'use_func' => 'osc_get_tax_class_title',
                                                                 'set_func' => 'osc_cfg_pull_down_tax_classes('),
                      'MODULE_SHIPPING_TABLE_ZONE' => array('title' => 'Shipping Zone',
                                                            'desc' => 'If a zone is selected, only enable this shipping method for that zone.',
                                                            'value' => '0',
                                                            'use_func' => 'osc_get_zone_class_title',
                                                            'set_func' => 'osc_cfg_pull_down_zone_classes('),
                      'MODULE_SHIPPING_TABLE_SORT_ORDER' => array('title' => 'Sort Order',
                                                                  'desc' => 'Sort order of display.',
                                                                  'value' => '0'));

      return $params;
    }

    protected function getShippableTotal() {
      global $OSCOM_PDO, $currencies;

      $order_total = $this->_order->getInfo('subtotal');

      if ( $_SESSION['cart']->get_content_type() == 'mixed' ) {
        $order_total = 0;

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

          $order_total += $currencies->calculate_price($p['final_price'], osc_get_tax_rate($p['tax_class_id'], $this->_order->getShippingAddress('country_id'), $this->_order->getShippingAddress('zone_id')), $p['quantity']);
        }
      }

      return $order_total;
    }
  }
?>
