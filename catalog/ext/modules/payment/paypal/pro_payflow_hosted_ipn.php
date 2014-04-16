<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  if (!defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_HS_STATUS') || (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_HS_STATUS  != 'True')) {
    exit;
  }

  require(DIR_WS_LANGUAGES . $language . '/modules/payment/paypal_pro_payflow_hs.php');
  require('includes/modules/payment/paypal_pro_payflow_hs.php');

  $result = false;

  if ( isset($HTTP_POST_VARS['txn_id']) && !empty($HTTP_POST_VARS['txn_id']) ) {
    $paypal_pro_payflow_hs = new paypal_pro_payflow_hs();

    $result = $paypal_pro_payflow_hs->getTransactionDetails($HTTP_POST_VARS['txn_id']);
  }

  if ( is_array($result) && isset($result['ACK']) && (($result['ACK'] == 'Success') || ($result['ACK'] == 'SuccessWithWarning')) ) {
    $pppfhs_result = $result;

    $paypal_pro_payflow_hs->verifyTransaction(true);
  }

  require('includes/application_bottom.php');
?>
