<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_boxes {
    public $code = 'boxes';
    public $directory;
    public $language_directory = DIR_FS_CATALOG_LANGUAGES;
    public $key = 'MODULE_BOXES_INSTALLED';
    public $title;
    public $template_integration = true;

    public function __construct() {
      $this->directory = DIR_FS_CATALOG_MODULES . 'boxes/';
      $this->title = MODULE_CFG_MODULE_BOXES_TITLE;
    }
  }
