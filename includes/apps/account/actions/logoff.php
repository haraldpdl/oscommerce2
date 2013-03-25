<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_logoff {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_Breadcrumb;

      $OSCOM_Customer->reset();

      if ( isset($_SESSION['order']) ) {
        unset($_SESSION['order']);
      }

      $_SESSION['cart']->reset();

      if ( SESSION_RECREATE == 'True' ) {
        osc_session_recreate();
      }

      $app->setContentFile('logoff.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_LOGOFF);
    }
  }
?>
