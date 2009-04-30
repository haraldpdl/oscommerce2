<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  Released under the GNU General Public License
*/

  $paypal_express_ping_button = '';
  if (defined('MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS')) {
    $paypal_express_ping_button = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href=' . tep_href_link('ext/modules/payment/paypal/paypal_express.php', 'action=test', 'SSL') . ' target="_blank" style="text-decoration: underline; font-weight: bold;">API-Berechtigungen &Uuml;berpr&uuml;fen</a></p>';
  }

  define('MODULE_PAYMENT_PAYPAL_EXPRESS_TEXT_TITLE', 'PayPal Express');
  define('MODULE_PAYMENT_PAYPAL_EXPRESS_TEXT_PUBLIC_TITLE', 'PayPal');
  define('MODULE_PAYMENT_PAYPAL_EXPRESS_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.paypal.com/mrb/pal=PS2X9Q773CKG4" target="_blank" style="text-decoration: underline; font-weight: bold;">PayPal Webseite besuchen</a>&nbsp;<a href="javascript:toggleDivBlock(\'paypalExpressInfo\');">(info)</a><span id="paypalExpressInfo" style="display: none;"><br><i>Bei Benutzung dieses Links erh&auml;lt osCommerce f&uuml;r eine Neukundenvermittlung einen kleinen Bonus.</i></span>' . $paypal_express_ping_button);
  define('MODULE_PAYMENT_PAYPAL_EXPRESS_TEXT_BUTTON', 'Express-Kauf mit PayPal');
  define('MODULE_PAYMENT_PAYPAL_EXPRESS_TEXT_COMMENTS', 'Anmerkungen:');
  define('MODULE_PAYMENT_PAYPAL_EXPRESS_EMAIL_PASSWORD', 'An account has automatically been created for you with the following e-mail address and password:' . "\n\n" . 'Store Account E-Mail Address: %s' . "\n" . 'Store Account Password: %s' . "\n\n");

  unset($paypal_express_ping_button);
?>