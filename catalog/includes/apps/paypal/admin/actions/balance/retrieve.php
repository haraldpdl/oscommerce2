<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  require(DIR_FS_ADMIN . 'includes/classes/currencies.php');
  $currencies = new currencies();

  $ppBalanceResult = array('rpcStatus' => -1);

  if ( isset($_GET['type']) && in_array($_GET['type'], array('live', 'sandbox')) ) {
    $ppBalanceResponse = $OSCOM_PayPal->getApiResult('APP', 'GetBalance', null, $_GET['type']);

    if ( is_array($ppBalanceResponse) && isset($ppBalanceResponse['ACK']) && ($ppBalanceResponse['ACK'] == 'Success') ) {
      $ppBalanceResult['rpcStatus'] = 1;

      $counter = 0;

      while ( true ) {
        if ( isset($ppBalanceResponse['L_AMT' . $counter]) && isset($ppBalanceResponse['L_CURRENCYCODE' . $counter]) ) {
          $balance = $ppBalanceResponse['L_AMT' . $counter];

          if (isset($currencies->currencies[$ppBalanceResponse['L_CURRENCYCODE' . $counter]])) {
            $balance = $currencies->format($balance, false, $ppBalanceResponse['L_CURRENCYCODE' . $counter]);
          }

          $ppBalanceResult['balance'][$ppBalanceResponse['L_CURRENCYCODE' . $counter]] = $balance;

          $counter++;
        } else {
          break;
        }
      }
    }
  }

  echo json_encode($ppBalanceResult);

  exit;
?>
