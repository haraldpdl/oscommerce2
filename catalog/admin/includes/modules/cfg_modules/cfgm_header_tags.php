<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_header_tags {
    public $code = 'header_tags';
    public $directory;
    public $language_directory = DIR_FS_CATALOG_LANGUAGES;
    public $key = 'MODULE_HEADER_TAGS_INSTALLED';
    public $title;
    public $template_integration = true;

    public function __construct() {
      $this->directory = DIR_FS_CATALOG_MODULES . 'header_tags/';
      $this->title = MODULE_CFG_MODULE_HEADER_TAGS_TITLE;
    }
  }
