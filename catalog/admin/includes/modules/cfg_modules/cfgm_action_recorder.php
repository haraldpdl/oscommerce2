<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_action_recorder {
    public $code = 'action_recorder';
    public $directory;
    public $language_directory = DIR_FS_CATALOG_LANGUAGES;
    public $key = 'MODULE_ACTION_RECORDER_INSTALLED';
    public $title;
    public $template_integration = false;

    public function __construct() {
      $this->directory = DIR_FS_CATALOG_MODULES . 'action_recorder/';
      $this->title = MODULE_CFG_MODULE_ACTION_RECORDER_TITLE;
    }
  }
