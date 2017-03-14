<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_session_storage {
    public $type = 'warning';

    public function __construct() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/session_storage.php');
    }

    public function pass() {
      return ((STORE_SESSIONS != '') || (is_dir(tep_session_save_path()) && tep_is_writable(tep_session_save_path())));
    }

    public function getMessage() {
      if (STORE_SESSIONS == '') {
        if (!is_dir(tep_session_save_path())) {
          return WARNING_SESSION_DIRECTORY_NON_EXISTENT;
        } elseif (!tep_is_writable(tep_session_save_path())) {
          return WARNING_SESSION_DIRECTORY_NOT_WRITEABLE;
        }
      }
    }
  }
