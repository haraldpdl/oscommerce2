<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_navbar_modules {
    public $code = 'navbar_modules';
    public $directory;
    public $language_directory = DIR_FS_CATALOG_LANGUAGES;
    public $key = 'MODULE_CONTENT_NAVBAR_INSTALLED';
    public $title;
    public $template_integration = false;

    public function __construct() {
      $this->directory = DIR_FS_CATALOG_MODULES . 'navbar_modules/';
      $this->title = MODULE_CFG_MODULE_CONTENT_NAVBAR_TITLE;
    }
  }
