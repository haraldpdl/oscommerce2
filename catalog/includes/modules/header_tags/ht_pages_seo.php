<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  class ht_pages_seo {
    public $code = 'ht_pages_seo';
    public $group = 'header_tags';
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->title = MODULE_HEADER_TAGS_PAGES_SEO_TITLE;
      $this->description = MODULE_HEADER_TAGS_PAGES_SEO_DESCRIPTION;
      $this->description .= '<div class="secWarning">' . MODULE_HEADER_TAGS_PAGES_SEO_HELPER . '</div>';

      if ( defined('MODULE_HEADER_TAGS_PAGES_SEO_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_PAGES_SEO_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_PAGES_SEO_STATUS == 'True');
      }
    }

    public function execute() {
      global $oscTemplate;

      if ( (defined('META_SEO_TITLE')) && (strlen(META_SEO_TITLE) > 0) ) {
        $oscTemplate->setTitle(tep_output_string(META_SEO_TITLE)  . MODULE_HEADER_TAGS_PAGES_SEO_SEPARATOR . $oscTemplate->getTitle());
      }
      if ( (defined('META_SEO_DESCRIPTION')) && (strlen(META_SEO_DESCRIPTION) > 0) ) {
        $oscTemplate->addBlock('<meta name="description" content="' . tep_output_string(META_SEO_DESCRIPTION) . '" />' . "\n", $this->group);
      }
      if ( (defined('META_SEO_KEYWORDS')) && (strlen(META_SEO_KEYWORDS) > 0) ) {
        $oscTemplate->addBlock('<meta name="keywords" content="' . tep_output_string(META_SEO_KEYWORDS) . '" />' . "\n", $this->group);
      }

    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_HEADER_TAGS_PAGES_SEO_STATUS');
    }

    public function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Pages SEO Module', 'MODULE_HEADER_TAGS_PAGES_SEO_STATUS', 'True', 'Do you want to allow this module to write SEO to your Pages?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_PAGES_SEO_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    public function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_HEADER_TAGS_PAGES_SEO_STATUS', 'MODULE_HEADER_TAGS_PAGES_SEO_SORT_ORDER');
    }
  }
