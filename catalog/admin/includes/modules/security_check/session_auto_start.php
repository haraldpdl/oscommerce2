<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_session_auto_start {
    public $type = 'warning';

    public function __construct() {
      include(DIR_FS_ADMIN . 'includes/languages/' . $_SESSION['language'] . '/modules/security_check/session_auto_start.php');
    }

    public function pass() {
      return ((bool)ini_get('session.auto_start') == false);
    }

    public function getMessage() {
      return WARNING_SESSION_AUTO_START;
    }
  }
