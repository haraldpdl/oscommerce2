<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_default_currency {
    public $type = 'error';

    public function __construct() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/default_currency.php');
    }

    public function pass() {
      return defined('DEFAULT_CURRENCY');
    }

    public function getMessage() {
      return ERROR_NO_DEFAULT_CURRENCY_DEFINED;
    }
  }
