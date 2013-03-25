<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_shipping {
    public static function execute(app $app) {
      global $OSCOM_Breadcrumb, $OSCOM_Order;

// if the order contains only virtual products, forward the customer to the checkout page as
// a shipping address is not needed
      if ( !$OSCOM_Order->requireShipping() ) {
        osc_redirect(osc_href_link('checkout', null, 'SSL'));
      }

      $app->setContentFile('shipping.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_SHIPPING, osc_href_link('checkout', 'shipping', 'SSL'));
    }
  }
?>
