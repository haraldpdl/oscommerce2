<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class securityCheckExtended_version_check {
    public $type = 'warning';
    public $has_doc = true;

    public function __construct() {
      include(DIR_FS_ADMIN . 'includes/languages/' . $_SESSION['language'] . '/modules/security_check/extended/version_check.php');

      $this->title = MODULE_SECURITY_CHECK_EXTENDED_VERSION_CHECK_TITLE;
    }

    public function pass() {
      $cache_file = DIR_FS_CACHE . 'oscommerce_version_check.cache';

      return file_exists($cache_file) && (filemtime($cache_file) > strtotime('-30 days'));
    }

    public function getMessage() {
      return '<a href="' . tep_href_link('version_check.php') . '">' . MODULE_SECURITY_CHECK_EXTENDED_VERSION_CHECK_ERROR . '</a>';
    }
  }
