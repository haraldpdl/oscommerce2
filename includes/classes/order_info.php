<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class orderInfo {
    protected $_data = array();

    public function __construct($id) {
      global $OSCOM_PDO;

      $Qorder = $OSCOM_PDO->prepare('select * from :table_orders where orders_id = :orders_id');
      $Qorder->bindInt(':orders_id', $id);
      $Qorder->execute();

      if ( $Qorder->fetch() === false ) {
        return false;
      }

      $this->_data['info'] = array('id' => $Qorder->valueInt('orders_id'),
                                   'currency' => $Qorder->value('currency'),
                                   'currency_value' => $Qorder->value('currency_value'),
                                   'payment_method' => $Qorder->value('payment_method'),
                                   'date_purchased' => $Qorder->value('date_purchased'),
                                   'last_modified' => $Qorder->value('last_modified'));

      $Qstatus = $OSCOM_PDO->prepare('select orders_status_name from :table_orders_status where orders_status_id = :orders_status_id and language_id = :language_id');
      $Qstatus->bindInt(':orders_status_id', $Qorder->valueInt('orders_status'));
      $Qstatus->bindInt(':language_id', $_SESSION['languages_id']);
      $Qstatus->execute();

      $this->_data['info']['status'] = $Qstatus->value('orders_status_name');

      $this->_data['customer'] = array('id' => $Qorder->valueInt('customers_id'),
                                       'name' => $Qorder->value('customers_name'),
                                       'company' => $Qorder->value('customers_company'),
                                       'street_address' => $Qorder->value('customers_street_address'),
                                       'suburb' => $Qorder->value('customers_suburb'),
                                       'city' => $Qorder->value('customers_city'),
                                       'postcode' => $Qorder->value('customers_postcode'),
                                       'state' => $Qorder->value('customers_state'),
                                       'country' => $Qorder->value('customers_country'),
                                       'format_id' => $Qorder->valueInt('customers_address_format_id'),
                                       'telephone' => $Qorder->value('customers_telephone'),
                                       'email_address' => $Qorder->value('customers_email_address'));

      if ( osc_not_null($Qorder->value('delivery_name')) && osc_not_null($Qorder->value('delivery_street_address')) ) {
        $this->_data['shipping'] = array('name' => $Qorder->value('delivery_name'),
                                         'company' => $Qorder->value('delivery_company'),
                                         'street_address' => $Qorder->value('delivery_street_address'),
                                         'suburb' => $Qorder->value('delivery_suburb'),
                                         'city' => $Qorder->value('delivery_city'),
                                         'postcode' => $Qorder->value('delivery_postcode'),
                                         'state' => $Qorder->value('delivery_state'),
                                         'country' => $Qorder->value('delivery_country'),
                                         'format_id' => $Qorder->valueInt('delivery_address_format_id'));
      }

      $this->_data['billing'] = array('name' => $Qorder->value('billing_name'),
                                      'company' => $Qorder->value('billing_company'),
                                      'street_address' => $Qorder->value('billing_street_address'),
                                      'suburb' => $Qorder->value('billing_suburb'),
                                      'city' => $Qorder->value('billing_city'),
                                      'postcode' => $Qorder->value('billing_postcode'),
                                      'state' => $Qorder->value('billing_state'),
                                      'country' => $Qorder->value('billing_country'),
                                      'format_id' => $Qorder->valueInt('billing_address_format_id'));

      $index = 0;

      $Qproducts = $OSCOM_PDO->prepare('select * from :table_orders_products where orders_id = :orders_id');
      $Qproducts->bindInt(':orders_id', $id);
      $Qproducts->execute();

      while ( $Qproducts->fetch() ) {
        $this->_data['products'][$index] = array('id' => $Qproducts->valueInt('products_id'),
                                                 'name' => $Qproducts->value('products_name'),
                                                 'model' => $Qproducts->value('products_model'),
                                                 'tax' => $Qproducts->value('products_tax'),
                                                 'price' => $Qproducts->value('products_price'),
                                                 'final_price' => $Qproducts->value('final_price'),
                                                 'quantity' => $Qproducts->valueInt('products_quantity'));

        $Qattributes = $OSCOM_PDO->prepare('select * from :table_orders_products_attributes where orders_id = :orders_id and orders_products_id = :orders_products_id');
        $Qattributes->bindInt(':orders_id', $id);
        $Qattributes->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
        $Qattributes->execute();

        while ( $Qattributes->fetch() ) {
          $this->_data['products'][$index]['attributes'][] = array('option' => $Qattributes->value('products_options'),
                                                                   'value' => $Qattributes->value('products_options_values'),
                                                                   'prefix' => $Qattributes->value('price_prefix'),
                                                                   'price' => $Qattributes->value('options_values_price'));
        }

        $this->_data['info']['tax_groups'][$this->_data['products'][$index]['tax']] = '1';

        $index++;
      }

      $Qtotals = $OSCOM_PDO->prepare('select class, title, text from :table_orders_total where orders_id = :orders_id order by sort_order');
      $Qtotals->bindInt(':orders_id', $id);
      $Qtotals->execute();

      $this->_data['totals'] = $Qtotals->fetchAll();

      $Qhistory = $OSCOM_PDO->prepare('select os.orders_status_name, osh.date_added, osh.comments from :table_orders_status os, :table_orders_status_history osh where osh.orders_id = :orders_id and osh.orders_status_id = os.orders_status_id and os.language_id = :language_id and os.public_flag = "1" order by osh.date_added');
      $Qhistory->bindInt(':orders_id', $id);
      $Qhistory->bindInt(':language_id', $_SESSION['languages_id']);
      $Qhistory->execute();

      while ( $Qhistory->fetch() ) {
        $this->_data['status_history'][] = array('name' => $Qhistory->value('orders_status_name'),
                                                 'date_added' => $Qhistory->value('date_added'),
                                                 'comments' => $Qhistory->value('comments'));
      }
    }

    public function getInfo($key = null) {
      if ( isset($key) ) {
        return $this->_data['info'][$key];
      }

      return $this->_data['info'];
    }

    public function getCustomer($key = null) {
      if ( isset($key) ) {
        return $this->_data['customer'][$key];
      }

      return $this->_data['customer'];
    }

    public function hasShippingAddress() {
      return isset($this->_data['shipping']);
    }

    public function getShippingAddress($key = null) {
      if ( isset($key) ) {
        return $this->_data['shipping'][$key];
      }

      return $this->_data['shipping'];
    }

    public function getBillingAddress($key = null) {
      if ( isset($key) ) {
        return $this->_data['billing'][$key];
      }

      return $this->_data['billing'];
    }

    public function hasShipping() {
      foreach ( $this->_data['totals'] as $t ) {
        if ( $t['class'] == 'ot_shipping' ) {
          return true;
        }
      }

      return false;
    }

    public function getShipping() {
      foreach ( $this->_data['totals'] as $t ) {
        if ( $t['class'] == 'ot_shipping' ) {
          $shipping = $t['title'];

          if ( substr($shipping, -1) == ':' ) {
            $shipping = substr($shipping, 0, -1);
          }

          return strip_tags($shipping);
        }
      }
    }

    public function getProducts() {
      return $this->_data['products'];
    }

    public function getTotal() {
      foreach ( $this->_data['totals'] as $t ) {
        if ( $t['class'] == 'ot_total' ) {
          return strip_tags($t['text']);
        }
      }
    }

    public function getTotals() {
      return $this->_data['totals'];
    }

    public function getStatusHistory() {
      return isset($this->_data['status_history']) ? $this->_data['status_history'] : array();
    }

    static public function canView($id, $customer_id) {
      global $OSCOM_PDO;

      $Qcheck = $OSCOM_PDO->prepare('select o.orders_id from :table_orders o, :table_orders_status s where o.orders_id = :orders_id and o.customers_id = :customers_id and o.orders_status = s.orders_status_id and s.public_flag = "1"');
      $Qcheck->bindInt(':orders_id', $id);
      $Qcheck->bindInt(':customers_id', $customer_id);
      $Qcheck->execute();

      return ($Qcheck->fetch() !== false);
    }
  }
?>
