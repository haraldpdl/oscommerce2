<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_success {
    public static function execute(app $app) {
      $_SESSION['cart']->reset(true);

// unregister session variables used during checkout
      unset($_SESSION['order']);

      $app->setContentFile('success.php');
    }
  }
?>
