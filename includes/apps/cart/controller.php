<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'order.php');

  class app_cart extends app {
    public function __construct() {
      global $OSCOM_Breadcrumb, $OSCOM_Order;

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE, osc_href_link('cart'));

      if ( $_SESSION['cart']->count_contents() > 0 ) {
        $OSCOM_Order = new order();

        if ( !$OSCOM_Order->hasBillingAddress() ) {
          $OSCOM_Order->loadPaymentOptions();
        }
      } else {
        $this->_content_file = 'empty.php';
      }
    }
  }
?>
