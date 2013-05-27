<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  require(DIR_FS_CATALOG . 'includes/classes/shipping_abstract.php');

  class shipping {
    protected $_modules = array();
    protected $_order;

    public function __construct(order $OSCOM_Order) {
      $this->_order = $OSCOM_Order;

      if ( defined('MODULE_SHIPPING_INSTALLED') && osc_not_null(MODULE_SHIPPING_INSTALLED) ) {
        $installed = explode(';', MODULE_SHIPPING_INSTALLED);

        if ( in_array('free.php', $installed) ) {
          if ( $this->load('free') ) {
            $installed = array();
          } else {
            unset($installed[array_search('free.php', $installed)]);
          }
        }

        foreach ( $installed as $file ) {
          $code = substr($file, 0, strrpos($file, '.'));

          $this->load($code);
        }
      }
    }

    public function getQuotes() {
      $quotes = array();

      if ( is_array($this->_modules) && !empty($this->_modules) ) {
        foreach ( $this->_modules as $m ) {
          $quote = $m->getQuote();

          if ( is_array($quote) && !empty($quote) ) {
            $data = array('id' => $m->getCode(),
                          'title' => $m->getPublicTitle(),
                          'methods' => $quote);

            if ( $m->hasTaxClass() ) {
              $data['tax_class_id'] = $m->getTaxClass();
            }

            if ( $m->hasIcon() ) {
              $data['icon'] = $m->getIcon();
            }

            if ( isset($quote['error']) ) {
              $data['error'] = $quote['error'];

              unset($data['methods']['error']);
            }

            $quotes[] = $data;
          }
        }
      }

      return $quotes;
    }

    protected function load($code) {
      include(DIR_FS_CATALOG . 'includes/modules/shipping/' . $code . '.php');

      if ( is_subclass_of($code, 'shipping_abstract') ) {
        $module = new $code($this->_order);

        if ( $module->isEnabled() ) {
          $this->_modules[$code] = $module;

          return true;
        }
      }

      return false;
    }
  }
?>
