<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'http_client.php');
  require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'order.php');

  class app_checkout extends app {
    public function __construct() {
      global $OSCOM_Breadcrumb, $OSCOM_Customer, $OSCOM_Order, $OSCOM_NavigationHistory, $OSCOM_Payment, $OSCOM_PDO;

      if ( $_SESSION['cart']->count_contents() < 1 ) {
        osc_redirect(osc_href_link('cart'));
      }

      if ( !$OSCOM_Customer->isLoggedOn() ) {
        $OSCOM_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link('account', 'login', 'SSL'));
      }

      $OSCOM_Order = new order();

      if ( (STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') && $OSCOM_Order->hasOutOfStockProducts() ) {
        osc_redirect(osc_href_link('cart'));
      }

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE, osc_href_link('checkout', '', 'SSL'));

      if ( $OSCOM_Order->requireShipping() ) {
        if ( !$OSCOM_Order->hasShippingAddress() ) {
          if ( $OSCOM_Customer->hasDefaultAddress() ) {
            $OSCOM_Order->setShippingAddress($OSCOM_Customer->getDefaultAddressID());
          } elseif ( !isset($_GET['shipping']) ) {
            osc_redirect(osc_href_link('checkout', 'shipping&address', 'SSL'));
          }
        }

        if ( !$OSCOM_Order->hasShipping() ) {
          if ( !isset($_GET['shipping']) ) {
            osc_redirect(osc_href_link('checkout', 'shipping', 'SSL'));
          }
        }
      }

      if ( !$OSCOM_Order->requireShipping() || ($OSCOM_Order->hasShippingAddress() && $OSCOM_Order->hasShipping() && !isset($_GET['shipping'])) ) {
        if ( !$OSCOM_Order->hasBillingAddress() ) {
          if ( $OSCOM_Order->hasShippingAddress() ) {
            $OSCOM_Order->setBillingAddress($OSCOM_Order->getShippingAddress());
          } elseif ( $OSCOM_Customer->hasDefaultAddress() ) {
            $OSCOM_Order->setBillingAddress($OSCOM_Customer->getDefaultAddressID());
          } elseif ( !isset($_GET['payment']) ) {
            osc_redirect(osc_href_link('checkout', 'payment&address', 'SSL'));
          }
        }

        if ( !$OSCOM_Order->hasBilling() ) {
          if ( !isset($_GET['payment']) ) {
            osc_redirect(osc_href_link('checkout', 'payment', 'SSL'));
          }
        }
      }

      if ( (!$OSCOM_Order->requireShipping() || ($OSCOM_Order->hasShippingAddress() && $OSCOM_Order->hasShipping())) && $OSCOM_Order->hasBillingAddress() && $OSCOM_Order->hasBilling() ) {
        $OSCOM_Payment->pre_confirmation_check();
      }
    }
  }
?>
