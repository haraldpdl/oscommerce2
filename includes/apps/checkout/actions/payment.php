<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_payment {
    public static function execute(app $app) {
      global $OSCOM_Breadcrumb, $OSCOM_Order, $OSCOM_MessageStack;

      $app->setContentFile('payment.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_PAYMENT, osc_href_link('checkout', 'payment', 'SSL'));

      if ( $OSCOM_Order->hasBillingAddress() ) {
        if ( isset($_GET['payment_error']) && isset($GLOBALS[$_GET['payment_error']]) && is_object($GLOBALS[$_GET['payment_error']]) && ($error = $GLOBALS[$_GET['payment_error']]->get_error()) ) {
          $OSCOM_MessageStack->addError('payment_error', '<strong>' . osc_output_string_protected($error['title']) . '</strong><br />' . osc_output_string_protected($error['error']));
        }
      }
    }
  }
?>
