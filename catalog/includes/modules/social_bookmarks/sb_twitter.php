<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class sb_twitter {
    public $code = 'sb_twitter';
    public $title;
    public $description;
    public $sort_order;
    public $icon = 'twitter.png';
    public $enabled = false;

    public function __construct() {
      $this->title = MODULE_SOCIAL_BOOKMARKS_TWITTER_TITLE;
      $this->public_title = MODULE_SOCIAL_BOOKMARKS_TWITTER_PUBLIC_TITLE;
      $this->description = MODULE_SOCIAL_BOOKMARKS_TWITTER_DESCRIPTION;

      if ( defined('MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_TWITTER_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS == 'True');
      }
    }

    public function getOutput() {
      return '<a href="http://twitter.com/home?status=' . urlencode(tep_href_link('product_info.php', 'products_id=' . $_GET['products_id'], 'NONSSL', false)) . '" target="_blank"><img src="images/social_bookmarks/' . $this->icon . '" border="0" title="' . tep_output_string_protected($this->public_title) . '" alt="' . tep_output_string_protected($this->public_title) . '" /></a>';
    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function getIcon() {
      return $this->icon;
    }

    public function getPublicTitle() {
      return $this->public_title;
    }

    public function check() {
      return defined('MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS');
    }

    public function install() {
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Twitter Module', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS', 'True', 'Do you want to allow products to be shared through Twitter?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    public function remove() {
      tep_db_query("delete from :table_configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_SORT_ORDER');
    }
  }
