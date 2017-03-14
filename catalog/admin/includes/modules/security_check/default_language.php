<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_default_language {
    public $type = 'error';

    public function __construct() {
      include(DIR_FS_ADMIN . 'includes/languages/' . $_SESSION['language'] . '/modules/security_check/default_language.php');
    }

    public function pass() {
      return defined('DEFAULT_LANGUAGE');
    }

    public function getMessage() {
      return ERROR_NO_DEFAULT_LANGUAGE_DEFINED;
    }
  }
