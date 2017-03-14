<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class ht_gpublisher {
    public $code = 'ht_gpublisher';
    public $group = 'header_tags';
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->title = MODULE_HEADER_TAGS_GPUBLISHER_TITLE;
      $this->description = MODULE_HEADER_TAGS_GPUBLISHER_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_GPUBLISHER_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_GPUBLISHER_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_GPUBLISHER_STATUS == 'True');
      }
    }

    public function execute() {
      global $oscTemplate;

      $oscTemplate->addBlock('<link rel="publisher" href="' . tep_output_string(MODULE_HEADER_TAGS_GPUBLISHER_ID) . '" />' . PHP_EOL, $this->group);
    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_HEADER_TAGS_GPUBLISHER_STATUS');
    }

    public function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable G+ Publisher Module', 'MODULE_HEADER_TAGS_GPUBLISHER_STATUS', 'True', 'Add G+ Publisher Link to your shop?  You MUST have a BUSINESS G+ account.  Once installed and configured, don\'t forget to link your G+ page back to your website.<br><br><b>Helper Links:</b><br>http://www.google.com/+/business/<br>http://www.advancessg.com/googles-relpublisher-tag-is-for-all-business-and-brand-websites-not-just-publishers/', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('G+ Publisher Address', 'MODULE_HEADER_TAGS_GPUBLISHER_ID', '', 'Your G+ URL.', '6', '0', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_GPUBLISHER_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    public function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_HEADER_TAGS_GPUBLISHER_STATUS', 'MODULE_HEADER_TAGS_GPUBLISHER_ID', 'MODULE_HEADER_TAGS_GPUBLISHER_SORT_ORDER');
    }
  }
