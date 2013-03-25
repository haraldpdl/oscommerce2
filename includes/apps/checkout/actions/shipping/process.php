<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_shipping_process {
    public static function execute(app $app) {
      global $OSCOM_Order;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        if ( osc_not_null($_POST['comments']) ) {
          $OSCOM_Order->setInfo('comments', trim($_POST['comments']));
        }

        if ( $OSCOM_Order->hasShippingRates() ) {
          if ( isset($_POST['shipping']) && (strpos($_POST['shipping'], '_') !== false) ) {
            list($module, $method) = explode('_', $_POST['shipping'], 2);

            if ( $OSCOM_Order->hasShippingRate($module, $method) ) {
              $quote = $OSCOM_Order->getShippingRate($module, $method);

              if ( !isset($quote['error']) ) {
                $OSCOM_Order->setShipping($module, $method);
              }
            }
          }
        }
      }

      osc_redirect(osc_href_link('checkout', '', 'SSL'));
    }
  }
?>
