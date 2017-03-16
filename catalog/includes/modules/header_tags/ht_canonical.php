<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class ht_canonical {
    public $code = 'ht_canonical';
    public $group = 'header_tags';
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->title = MODULE_HEADER_TAGS_CANONICAL_TITLE;
      $this->description = MODULE_HEADER_TAGS_CANONICAL_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_CANONICAL_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_CANONICAL_STATUS == 'True');
      }
    }

    public function execute() {
      global $PHP_SELF, $cPath, $oscTemplate, $category_depth;

      if (basename($PHP_SELF) == 'product_info.php') {
        $oscTemplate->addBlock('<link rel="canonical" href="' . tep_href_link('product_info.php', 'products_id=' . (int)$_GET['products_id'], 'NONSSL', false) . '" />' . PHP_EOL, $this->group);
      } elseif (basename($PHP_SELF) == 'index.php') {
        if (isset($cPath) && tep_not_null($cPath) && ($category_depth == 'products')) {
          $oscTemplate->addBlock('<link rel="canonical" href="' . tep_href_link('index.php', 'view=all&cPath=' . $cPath, 'NONSSL', false) . '" />' . PHP_EOL, $this->group);
        } elseif (isset($_GET['manufacturers_id']) && tep_not_null($_GET['manufacturers_id'])) {
          $oscTemplate->addBlock('<link rel="canonical" href="' . tep_href_link('index.php', 'view=all&manufacturers_id=' . (int)$_GET['manufacturers_id'], 'NONSSL', false) . '" />' . PHP_EOL, $this->group);
        }
      }
      else {
        $view_all_pages = array('products_new.php', 'specials.php');
        if (in_array(basename($PHP_SELF), $view_all_pages)) {
          $oscTemplate->addBlock('<link rel="canonical" href="' . tep_href_link($PHP_SELF, 'view=all', 'NONSSL', false) . '" />' . PHP_EOL, $this->group);
        }
      }
    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_HEADER_TAGS_CANONICAL_STATUS');
    }

    public function install() {
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Canonical Module', 'MODULE_HEADER_TAGS_CANONICAL_STATUS', 'True', 'Do you want to enable the Canonical module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    public function remove() {
      tep_db_query("delete from :table_configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_HEADER_TAGS_CANONICAL_STATUS', 'MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER');
    }
  }
