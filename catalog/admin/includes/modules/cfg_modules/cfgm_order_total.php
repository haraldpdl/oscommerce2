<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_order_total {
    public $code = 'order_total';
    public $directory;
    public $language_directory = DIR_FS_CATALOG_LANGUAGES;
    public $key = 'MODULE_ORDER_TOTAL_INSTALLED';
    public $title;
    public $template_integration = false;

    public function __construct() {
      $this->directory = DIR_FS_CATALOG_MODULES . 'order_total/';
      $this->title = MODULE_CFG_MODULE_ORDER_TOTAL_TITLE;
    }
  }
