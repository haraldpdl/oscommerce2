<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class cfgm_dashboard {
    public $code = 'dashboard';
    public $directory;
    public $language_directory;
    public $key = 'MODULE_ADMIN_DASHBOARD_INSTALLED';
    public $title;
    public $template_integration = false;

    public function __construct() {
      $this->directory = DIR_FS_ADMIN . 'includes/modules/dashboard/';
      $this->language_directory = DIR_FS_ADMIN . 'includes/languages/';
      $this->title = MODULE_CFG_MODULE_DASHBOARD_TITLE;
    }
  }
