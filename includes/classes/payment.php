<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  require(DIR_FS_CATALOG . 'includes/classes/payment_abstract.php');

  class payment {
    protected $_modules = array();
    protected $_order;

    public function __construct(order $OSCOM_Order) {
      $this->_order = $OSCOM_Order;

      if ( defined('MODULE_PAYMENT_INSTALLED') && osc_not_null(MODULE_PAYMENT_INSTALLED) ) {
        $installed = explode(';', MODULE_PAYMENT_INSTALLED);

        foreach ( $installed as $file ) {
          $code = substr($file, 0, strrpos($file, '.'));

          $this->load($code);
        }

/*
// if there is only one payment method, select it as default because in
// checkout_confirmation.php the $payment variable is being assigned the
// $_POST['payment'] value which will be empty (no radio button selection possible)
        if ( (osc_count_payment_modules() == 1) && (!isset($_SESSION['payment']) || !isset($GLOBALS[$_SESSION['payment']]) || !is_object($GLOBALS[$_SESSION['payment']])) ) {
          for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
            if ( $GLOBALS[$include_modules[$i]['class']]->enabled ) {
              $_SESSION['payment'] = $include_modules[$i]['class'];
              break;
            }
          }
        }

        if ( (osc_not_null($module)) && (in_array($module, $this->modules)) && (isset($GLOBALS[$module]->form_action_url)) ) {
          $this->form_action_url = $GLOBALS[$module]->form_action_url;
        }
*/
      }
    }

    public function getModules() {
      return $this->_modules;
    }

    public function get($module = null) {
      if ( !isset($module) ) {
        $module = $this->_order->getBilling('id');
      }

      return $this->_modules[$module];
    }

    public function exists($module) {
      return array_key_exists($module, $this->_modules);
    }

    public function getJavascriptValidation() {
      $js = '';

      if ( !empty($this->_modules) ) {
        $js = '<script>' . "\n" .
              'function check_form() {' . "\n" .
              '  var error = 0;' . "\n" .
              '  var error_message = "' . JS_ERROR . '";' . "\n" .
              '  var payment_value = null;' . "\n" .
              '  if (document.checkout_payment.payment.length) {' . "\n" .
              '    for (var i=0; i<document.checkout_payment.payment.length; i++) {' . "\n" .
              '      if (document.checkout_payment.payment[i].checked) {' . "\n" .
              '        payment_value = document.checkout_payment.payment[i].value;' . "\n" .
              '      }' . "\n" .
              '    }' . "\n" .
              '  } else if (document.checkout_payment.payment.checked) {' . "\n" .
              '    payment_value = document.checkout_payment.payment.value;' . "\n" .
              '  } else if (document.checkout_payment.payment.value) {' . "\n" .
              '    payment_value = document.checkout_payment.payment.value;' . "\n" .
              '  }' . "\n\n";

        foreach ( $this->_modules as $m ) {
          $js .= $m->getJavascriptValidation();
        }

        $js .= "\n" . '  if (payment_value == null) {' . "\n" .
               '    error_message = error_message + "' . JS_ERROR_NO_PAYMENT_MODULE_SELECTED . '";' . "\n" .
               '    error = 1;' . "\n" .
               '  }' . "\n\n" .
               '  if (error == 1) {' . "\n" .
               '    alert(error_message);' . "\n" .
               '    return false;' . "\n" .
               '  } else {' . "\n" .
               '    return true;' . "\n" .
               '  }' . "\n" .
               '}' . "\n" .
               '</script>' . "\n";
      }

      return $js;
    }

    function checkout_initialization_method() {
      $initialize_array = array();

      foreach ( $this->_modules as $m ) {
        $button = $m->getCheckoutButton();

        if ( !empty($button) ) {
          $initialize_array[] = $button;
        }
      }

      return $initialize_array;
    }

    public function getSelectionFields() {
      $selection_array = array();

      foreach ( $this->_modules as $m ) {
        $selection = $m->getSelectionField();

        if ( is_array($selection) ) {
          $selection_array[] = $selection;
        }
      }

      return $selection_array;
    }

    function pre_confirmation_check() {
      $this->get()->runBeforeConfirmation();
    }

    function confirmation() {
      return $this->get()->runConfirmation();
    }

    function process_button() {
      return $this->get()->getProcessButton();
    }

    function before_process() {
      return $this->get()->runBeforeProcess();
    }

    function after_process() {
      return $this->get()->runAfterProcess();
    }

    function get_error() {
      return $this->get()->getError();
    }

    protected function load($code) {
      include(DIR_FS_CATALOG . 'includes/modules/payment/' . $code . '.php');

      if ( is_subclass_of($code, 'payment_abstract') ) {
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
