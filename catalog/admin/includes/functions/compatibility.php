<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/

// don't display errors
  ini_set('display_errors', false);
  ini_set('html_errors', false);
  ini_set('ignore_repeated_errors', true);

// log errors
  if (is_dir(DIR_FS_CATALOG . 'includes/work') && is_writable(DIR_FS_CATALOG . 'includes/work')) {
    if (!is_dir(DIR_FS_CATALOG . 'includes/work/error_logs')) {
      mkdir(DIR_FS_CATALOG . 'includes/work/error_logs', 0777, true);
    }

    if (is_dir(DIR_FS_CATALOG . 'includes/work/error_logs') && is_writable(DIR_FS_CATALOG . 'includes/work/error_logs')) {
      ini_set('log_errors', true);
      ini_set('error_log', DIR_FS_CATALOG . 'includes/work/error_logs/errors-' . date('Ymd') . '.txt');
    }
  }

////
// Recursively handle magic_quotes_gpc turned off.
// This is due to the possibility of have an array in
// $HTTP_xxx_VARS
// Ie, products attributes
  function do_magic_quotes_gpc(&$ar) {
    if (!is_array($ar)) return false;

    foreach ($ar as $key => $value) {
      if (is_array($ar[$key])) {
        do_magic_quotes_gpc($ar[$key]);
      } else {
        $ar[$key] = addslashes($value);
      }
    }
  }

// handle magic_quotes_gpc turned off.
  if (!get_magic_quotes_gpc()) {
    do_magic_quotes_gpc($_GET);
    do_magic_quotes_gpc($_POST);
    do_magic_quotes_gpc($_COOKIE);
  }

// set default timezone if none exists (PHP 5.3 throws an E_WARNING)
  date_default_timezone_set(defined('CFG_TIME_ZONE') ? CFG_TIME_ZONE : date_default_timezone_get());
?>
