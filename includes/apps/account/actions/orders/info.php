<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

 require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'order_info.php');

  class app_account_action_orders_info {
    public static function execute(app $app) {
      global $OSCOM_Breadcrumb, $OSCOM_Customer, $OSCOM_OrderInfo;

      if ( !isset($_GET['id']) || !is_numeric($_GET['id']) || !orderInfo::canView($_GET['id'], $OSCOM_Customer->getID()) ) {
        osc_redirect(osc_href_link('account', 'orders', 'SSL'));
      }

      $OSCOM_OrderInfo = new orderInfo($_GET['id'], $OSCOM_Customer->getID());

      $app->setContentFile('orders_info.php');

      $OSCOM_Breadcrumb->add(sprintf(NAVBAR_TITLE_ORDERS_INFO, $_GET['id']), osc_href_link('account', 'orders&info&id=' . $_GET['id'], 'SSL'));
    }
  }
?>
