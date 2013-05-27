<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  abstract class payment_abstract {
    protected $_code;
    protected $_title;
    protected $_public_title;
    protected $_description;
    protected $_order_status_id = 0;
    protected $_billing_zone_class_id;
    protected $_sort_order;
    protected $_installed = false;
    protected $_enabled = true;
    protected $_order;

    abstract protected function initialize();
    abstract protected function getParams();

    public function __construct(order $OSCOM_Order = null) {
      $this->_code = get_class($this);

      if ( file_exists(DIR_FS_CATALOG . 'includes/languages/' . $_SESSION['language'] . '/modules/payment/' . $this->_code . '.php') ) {
        include(DIR_FS_CATALOG . 'includes/languages/' . $_SESSION['language'] . '/modules/payment/' . $this->_code . '.php');
      }

      if ( isset($OSCOM_Order) ) {
        $this->_order = $OSCOM_Order;
      }

      $this->initialize();
    }

    public function getCode() {
      return $this->_code;
    }

    public function getTitle() {
      return $this->_title;
    }

    public function getPublicTitle() {
      return isset($this->_public_title) && !empty($this->_public_title) ? $this->_public_title : $this->_title;
    }

    public function getDescription() {
      return $this->_description;
    }

    public function getSortOrder() {
      return $this->_sort_order;
    }

    public function isInstalled() {
      return $this->_installed;
    }

    public function isEnabled() {
      return $this->_enabled;
    }

    public function getOrderStatusID() {
      return $this->_order_status_id;
    }

    public function hasValidBillingZone() {
      global $OSCOM_PDO;

      $result = true;

      if ( isset($this->_billing_zone_class_id) && ($this->_billing_zone_class_id > 0) ) {
        $result = false;

        $Qcheck = $OSCOM_PDO->prepare('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
        $Qcheck->bindInt(':geo_zone_id', $this->_billing_zone_class_id);
        $Qcheck->bindInt(':geo_zone_id', $this->_order->getBillingAddress('country_id'));
        $Qcheck->execute();

        while ( $Qcheck->fetch() ) {
          if ( $Qcheck->valueInt('zone_id') < 1 ) {
            $result = true;
            break;
          } elseif ( $Qcheck->valueInt('zone_id') == $this->_order->getBillingAddress('zone_id') ) {
            $result = true;
            break;
          }
        }
      }

      return $result;
    }

    public function getCheckoutButton() {
      return false;
    }

    public function getJavascriptValidation() {
      return false;
    }

    public function getSelectionField() {
      return array('id' => $this->_code,
                   'module' => $this->getPublicTitle());
    }

    public function runBeforeConfirmation() {
      return false;
    }

    public function runConfirmation() {
      return false;
    }

    public function getProcessButton() {
      return false;
    }

    public function runBeforeProcess() {
      return false;
    }

    public function runAfterProcess() {
      return false;
    }

    public function getError() {
      return false;
    }

    public function install($parameter = null) {
      global $OSCOM_PDO;

      $params = $this->getParams();

      if ( isset($parameter) ) {
        if ( isset($params[$parameter]) ) {
          $params = array($parameter => $params[$parameter]);
        } else {
          $params = array();
        }
      }

      foreach ( $params as $key => $data ) {
        $sql_data_array = array('configuration_title' => $data['title'],
                                'configuration_key' => $key,
                                'configuration_value' => (isset($data['value']) ? $data['value'] : 'null'),
                                'configuration_description' => $data['desc'],
                                'configuration_group_id' => '6',
                                'sort_order' => '0',
                                'date_added' => 'now()');

        if ( isset($data['set_func']) ) {
          $sql_data_array['set_function'] = $data['set_func'];
        }

        if ( isset($data['use_func']) ) {
          $sql_data_array['use_function'] = $data['use_func'];
        }

        $OSCOM_PDO->perform('configuration', $sql_data_array);
      }
    }

    public function uninstall() {
      global $OSCOM_PDO;

      $OSCOM_PDO->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->getKeys()) . '")');
    }

    public function getKeys() {
      $keys = array_keys($this->getParams());

      if ( $this->isInstalled() ) {
        foreach ( $keys as $key ) {
          if ( !defined($key) ) {
            $this->install($key);
          }
        }
      }

      return $keys;
    }
  }
?>
