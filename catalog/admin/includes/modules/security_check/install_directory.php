<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class securityCheck_install_directory {
    public $type = 'warning';

    public function __construct() {
      include(DIR_FS_ADMIN . 'includes/languages/' . $_SESSION['language'] . '/modules/security_check/install_directory.php');
    }

    public function pass() {
      return !file_exists(DIR_FS_CATALOG . 'install');
    }

    public function getMessage() {
      return WARNING_INSTALL_DIRECTORY_EXISTS;
    }
  }
