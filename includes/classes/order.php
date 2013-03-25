<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'order_total.php');
  require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'payment.php');
  require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'shipping.php');

  class order {
    protected $_data = array();

    public function __construct() {
      if ( !isset($_SESSION['order']) ) {
        $_SESSION['order'] = $this->_data;
      }

      $this->_data =& $_SESSION['order'];

      if ( isset($this->_data['shipping']['address']['id']) ) {
        $this->setShippingAddress($this->_data['shipping']['address']['id']);
      }

      if ( isset($this->_data['billing']['address']['id']) ) {
        $this->setBillingAddress($this->_data['billing']['address']['id']);
      }

      if ( !isset($this->_data['cart_id']) || !isset($_SESSION['cart']->cartID) || ($this->_data['cart_id'] != $_SESSION['cart']->cartID) ) {
        $this->_initiate();
      }

      if ( $this->hasBillingAddress() ) {
        $this->loadPaymentOptions();
      }

      if ( (!$this->requireShipping() || ($this->hasShippingAddress() && $this->hasShipping())) && $this->hasBillingAddress() && $this->hasBilling() ) {
        $this->calculate();
      }
    }

    protected function _initiate() {
      $this->_data['info'] = array('order_status_id' => DEFAULT_ORDERS_STATUS_ID,
                                   'subtotal' => $_SESSION['cart']->show_total(),
                                   'tax' => 0,
                                   'tax_groups' => array(),
                                   'total' => $_SESSION['cart']->show_total());

// Out of stock products
      $this->_data['out_of_stock_products'] = false;

      if ( STOCK_CHECK == 'true' ) {
        foreach ( $_SESSION['cart']->get_products() as $p ) {
          if ( osc_check_stock($p['id'], $p['quantity']) ) {
            $this->_data['out_of_stock_products'] = true;
            break;
          }
        }
      }

// Require shipping
      $this->_data['require_shipping'] = true;

      if ( $_SESSION['cart']->get_content_type() == 'virtual' ) {
        $this->_data['require_shipping'] = false;

        if ( isset($this->_data['shipping']) ) {
          unset($this->_data['shipping']);
        }
      }

      if ( $this->requireShipping() && $this->hasShippingAddress() ) {
        $this->loadShippingRates();
      }

      $this->_data['cart_id'] = $_SESSION['cart']->cartID = $_SESSION['cart']->generate_cart_id();
    }

    public function hasInfo($key) {
      return isset($this->_data['info'][$key]);
    }

    public function getInfo($key = null) {
      if ( isset($key) ) {
        return $this->_data['info'][$key];
      }

      return $this->_data['info'];
    }

    public function setInfo($key, $value) {
      $this->_data['info'][$key] = $value;
    }

    public function hasOutOfStockProducts() {
      return $this->_data['out_of_stock_products'];
    }

    public function requireShipping() {
      return $this->_data['require_shipping'];
    }

    public function hasShippingAddress() {
      return isset($this->_data['shipping']['address']);
    }

    public function setShippingAddress($address) { // INT or Array
      if ( is_numeric($address) ) {
        $address = $this->getCustomerAddress($address);
      }

      $is_different_address = false;

      if ( !isset($this->_data['shipping']['address']) || !empty(array_diff_assoc($this->_data['shipping']['address'], $address)) ) {
        $is_different_address = true;
      }

      $this->_data['shipping']['address'] = $address;

      if ( $is_different_address === true ) {
        unset($this->_data['cart_id']);
      }
    }

    public function getShippingAddress($key = null) {
      return $this->getAddress('shipping', $key);
    }

    public function getBillingAddress($key = null) {
      return $this->getAddress('billing', $key);
    }

    public function getTaxAddress($key = null) {
      return $this->getAddress($this->requireShipping() ? 'shipping' : 'billing', $key);
    }

    protected function getCustomerAddress($id) {
      global $OSCOM_Customer, $OSCOM_PDO;

      $Qaddress = $OSCOM_PDO->prepare('select * from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
      $Qaddress->bindInt(':address_book_id', $id);
      $Qaddress->bindInt(':customers_id', $OSCOM_Customer->getID());
      $Qaddress->execute();

      $address = array('id' => $Qaddress->valueInt('address_book_id'),
                       'gender' => $Qaddress->value('entry_gender'),
                       'company' => $Qaddress->value('entry_company'),
                       'firstname' => $Qaddress->value('entry_firstname'),
                       'lastname' => $Qaddress->value('entry_lastname'),
                       'street_address' => $Qaddress->value('entry_street_address'),
                       'suburb' => $Qaddress->value('entry_suburb'),
                       'postcode' => $Qaddress->value('entry_postcode'),
                       'city' => $Qaddress->value('entry_city'),
                       'state' => osc_get_zone_name($Qaddress->valueInt('entry_country_id'), $Qaddress->valueInt('entry_zone_id'), $Qaddress->value('entry_state')),
                       'zone_id' => $Qaddress->valueInt('entry_zone_id'),
                       'country_id' => $Qaddress->valueInt('entry_country_id'));

      return $address;
    }

    protected function getAddress($source, $key = null) {
      $data = ($source == 'shipping') ? $this->_data['shipping']['address'] : $this->_data['billing']['address'];

      if ( isset($key) ) {
        return $data[$key];
      }

      return $data;
    }

    public function hasBillingAddress() {
      return isset($this->_data['billing']['address']);
    }

    public function setBillingAddress($address) { // INT or Array
      if ( is_numeric($address) ) {
        $address = $this->getCustomerAddress($address);
      }

      $is_different_address = false;

      if ( !isset($this->_data['billing']['address']) || !empty(array_diff_assoc($this->_data['billing']['address'], $address)) ) {
        $is_different_address = true;
      }

      $this->_data['billing']['address'] = $address;

      if ( $is_different_address === true ) {
        unset($this->_data['cart_id']);
      }
    }

    public function hasShipping() {
      return isset($this->_data['shipping']['selected']);
    }

    public function setShipping($module, $method) {
      $rate = $this->getShippingRate($module, $method);

      $this->_data['shipping']['selected'] = array('id' => $module . '_' . $method,
                                                   'title' => $rate['title'] . (isset($rate['methods']['title']) ? ' (' . $rate['methods']['title'] . ')' : ''),
                                                   'cost' => $rate['methods']['cost']);

      if ( isset($rate['tax_class_id']) ) {
        $this->_data['shipping']['selected']['tax_class_id'] = $rate['tax_class_id'];
      }

      if ( isset($rate['icon']) ) {
        $this->_data['shipping']['selected']['icon'] = $rate['icon'];
      }
    }

    public function getShipping($key = null) {
      if ( isset($key) ) {
        return $this->_data['shipping']['selected'][$key];
      }

      return $this->_data['shipping']['selected'];
    }

    public function hasShippingTax() {
      return isset($this->_data['shipping']['selected']['tax_class_id']) && ($this->_data['shipping']['selected']['tax_class_id'] > 0);
    }

    public function getShippingTaxRate() {
      return osc_get_tax_rate($this->getShipping('tax_class_id'), $this->getShippingAddress('country_id'), $this->getShippingAddress('zone_id'));
    }

    public function loadShippingRates() {
      $OSCOM_Shipping = new shipping($this);

      $this->_data['shipping']['rates'] = $OSCOM_Shipping->getQuotes();

      if ( $this->hasShipping() ) {
        if ( $this->hasShippingRate('free', 'free') ) {
          $module = $method = 'free';
        } else {
          list($module, $method) = explode('_', $this->getShipping('id'), 2);
        }

        if ( $this->hasShippingRate($module, $method) ) {
          $this->setShipping($module, $method);
        } else {
          unset($this->_data['shipping']['selected']);
        }
      }
    }

    public function hasShippingRates() {
      return isset($this->_data['shipping']['rates']) && !empty($this->_data['shipping']['rates']);
    }

    public function getShippingRates() {
      return isset($this->_data['shipping']['rates']) ? $this->_data['shipping']['rates'] : array();
    }

    public function getNumberOfShippingRates() {
      return $this->hasShippingRates() ? count($this->_data['shipping']['rates']) : 0;
    }

    public function hasShippingRate($module, $method) {
      foreach ( $this->_data['shipping']['rates'] as $s ) {
        if ( $s['id'] == $module ) {
          foreach ( $s['methods'] as $m ) {
            if ( $m['id'] == $method ) {
              return true;
            }
          }
        }
      }

      return false;
    }

    public function getShippingRate($module, $method) {
      foreach ( $this->_data['shipping']['rates'] as $s ) {
        if ( $s['id'] == $module ) {
          foreach ( $s['methods'] as $m ) {
            if ( $m['id'] == $method ) {
              $rate = $s;
              $rate['methods'] = $m;

              return $rate;
            }
          }
        }
      }
    }

    public function getCheapestShippingRate() {
      $rate = null;

      foreach ( $this->_data['shipping']['rates'] as $s ) {
        foreach ( $s['methods'] as $m ) {
          if ( !isset($rate) || ($m['cost'] < $rate['cost']) ) {
            $rate = array('id' => $s['id'] . '_' . $m['id'],
                          'cost' => $m['cost']);
          }
        }
      }

      return $rate['id'];
    }

    public function loadPaymentOptions() {
      global $OSCOM_Payment;

      $OSCOM_Payment = new payment($this);
    }

    public function hasBilling() {
      return isset($this->_data['billing']['selected']);
    }

    public function setBilling($code) {
      global $OSCOM_Payment;

      $module = $OSCOM_Payment->get($code);

      $this->_data['billing']['selected'] = array('id' => $module->code,
                                                  'title' => (isset($module->public_title) ? $module->public_title : $module->title));

      if ( isset($module->order_status) && is_numeric($module->order_status) && ($module->order_status > 0) ) {
        $this->_data['info']['order_status_id'] = $module->order_status;
      }
    }

    public function getBilling($key = null) {
      if ( isset($key) ) {
        return $this->_data['billing']['selected'][$key];
      }

      return $this->_data['billing']['selected'];
    }

    public function getNumberOfTaxGroups() {
      $groups = array();

      foreach ( $_SESSION['cart']->get_products() as $p ) {
        if ( !in_array($p['tax_class_id'], $groups) ) {
          $groups[] = $p['tax_class_id'];
        }
      }

      return count($groups);
    }

    public function getTotals() {
      return $this->_data['totals'];
    }

    protected function calculate() {
      $OSCOM_OrderTotal = new order_total($this);

      $this->_data['totals'] = $OSCOM_OrderTotal->process();
    }
  }
?>
