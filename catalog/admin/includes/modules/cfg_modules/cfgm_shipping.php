<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_shipping {
    public $code = 'shipping';
    public $directory;
    public $language_directory = DIR_FS_CATALOG_LANGUAGES;
    public $key = 'MODULE_SHIPPING_INSTALLED';
    public $title;
    public $template_integration = false;

    public function __construct() {
      $this->directory = DIR_FS_CATALOG_MODULES . 'shipping/';
      $this->title = MODULE_CFG_MODULE_SHIPPING_TITLE;
    }
  }
