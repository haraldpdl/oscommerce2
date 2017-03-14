<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class ht_category_seo {
    public $code = 'ht_category_seo';
    public $group = 'header_tags';
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->title = MODULE_HEADER_TAGS_CATEGORY_SEO_TITLE;
      $this->description = MODULE_HEADER_TAGS_CATEGORY_SEO_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_CATEGORY_SEO_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_CATEGORY_SEO_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_CATEGORY_SEO_STATUS == 'True');
      }
    }

    public function execute() {
      global $PHP_SELF, $oscTemplate, $categories, $current_category_id;

      if ( (basename($PHP_SELF) == 'index.php') && ($current_category_id > 0) ){
        $meta_info_query = tep_db_query("select categories_seo_description, categories_seo_keywords from categories_description where categories_id = '" . (int)$current_category_id  . "' and language_id = '" . (int)$_SESSION['languages_id'] . "'");
        $meta_info = tep_db_fetch_array($meta_info_query);

        if (tep_not_null($meta_info['categories_seo_description'])) {
          $oscTemplate->addBlock('<meta name="description" content="' . tep_output_string($meta_info['categories_seo_description']) . '" />' . PHP_EOL, $this->group);
        }
        if ( (tep_not_null($meta_info['categories_seo_keywords'])) && (MODULE_HEADER_TAGS_CATEGORY_SEO_KEYWORDS_STATUS == 'True') ) {
          $oscTemplate->addBlock('<meta name="keywords" content="' . tep_output_string($meta_info['categories_seo_keywords']) . '" />' . PHP_EOL, $this->group);
        }
      }
    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_HEADER_TAGS_CATEGORY_SEO_STATUS');
    }

    public function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Category Meta Module', 'MODULE_HEADER_TAGS_CATEGORY_SEO_STATUS', 'True', 'Do you want to allow Category Meta Tags to be added to the page header?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Category Meta Description', 'MODULE_HEADER_TAGS_CATEGORY_SEO_DESCRIPTION_STATUS', 'True', 'These help your site and your sites visitors.', '6', '0', 'tep_cfg_select_option(array(\'True\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Category Meta Keywords', 'MODULE_HEADER_TAGS_CATEGORY_SEO_KEYWORDS_STATUS', 'False', 'These are almost pointless.  If you are into the Chinese Market select True (for Baidu Search Engine) otherwise select False.', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_CATEGORY_SEO_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    public function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_HEADER_TAGS_CATEGORY_SEO_STATUS', 'MODULE_HEADER_TAGS_CATEGORY_SEO_DESCRIPTION_STATUS', 'MODULE_HEADER_TAGS_CATEGORY_SEO_KEYWORDS_STATUS', 'MODULE_HEADER_TAGS_CATEGORY_SEO_SORT_ORDER');
    }
  }
