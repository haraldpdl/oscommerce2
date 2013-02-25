<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products_action_new {
    public static function execute(app $app) {
      global $breadcrumb;

      $app->setContentFile('new.php');

      $breadcrumb->add(NAVBAR_TITLE_NEW, osc_href_link('products', 'new'));
    }
  }
?>
