<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_config_file_catalog {
    public $type = 'warning';

    public function __construct() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/config_file_catalog.php');
    }

    public function pass() {
      return (file_exists(DIR_FS_CATALOG . 'includes/configure.php') && !tep_is_writable(DIR_FS_CATALOG . 'includes/configure.php'));
    }

    public function getMessage() {
      return WARNING_CONFIG_FILE_WRITEABLE;
    }
  }
