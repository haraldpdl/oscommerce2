<?php
/*
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( !class_exists('OSCOM_PayPal') ) {
    include(DIR_FS_CATALOG . 'includes/apps/paypal/OSCOM_PayPal.php');
  }

  class hook_admin_products_example {
    function hook_admin_products_example() {
      global $OSCOM_PayPal;

      if ( !isset($OSCOM_PayPal) || !is_object($OSCOM_PayPal) || (get_class($OSCOM_PayPal) != 'OSCOM_PayPal') ) {
        $OSCOM_PayPal = new OSCOM_PayPal();
      }

      $this->_app = $OSCOM_PayPal;

      $this->_app->loadLanguageFile('hooks/admin/products/example.php');
    }

    function listen_productTab() {
      global $base_url; // defined in admin/categories.php

      $tab_title = addslashes($this->_app->getDef('tab_example_title'));
      $tab_link = substr(tep_href_link(FILENAME_CATEGORIES, tep_get_all_get_params()), strlen($base_url)) . '#section_example_content';

      $output = <<<EOD
<script>
$(function() {
  $('#productTabsMain').append('<li><a href="{$tab_link}">{$tab_title}</a></li>');
});
</script>

<div id="section_example_content" style="padding: 10px;">
  <p>Example tab</p>
</div>
EOD;

      return $output;
    }
  }
?>
