<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_header_search {
    public $code;
    public $group;
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_HEADER_SEARCH_TITLE;
      $this->description = MODULE_CONTENT_HEADER_SEARCH_DESCRIPTION;
      $this->description .= '<div class="secWarning">' . MODULE_CONTENT_BOOTSTRAP_ROW_DESCRIPTION . '</div>';

      if ( defined('MODULE_CONTENT_HEADER_SEARCH_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_HEADER_SEARCH_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_HEADER_SEARCH_STATUS == 'True');
      }
    }

    public function execute() {
      global $oscTemplate, $request_type;

      $content_width = MODULE_CONTENT_HEADER_SEARCH_CONTENT_WIDTH;


      $search_box = '<div class="searchbox-margin">';
      $search_box .= tep_draw_form('quick_find', tep_href_link('advanced_search_result.php', '', $request_type, false), 'get', 'class="form-horizontal"');
      $search_box .= '  <div class="input-group">' .
                          tep_draw_input_field('keywords', '', 'required placeholder="' . TEXT_SEARCH_PLACEHOLDER . '"', 'search') . '<span class="input-group-btn"><button type="submit" class="btn btn-info"><i class="fa fa-search"></i></button></span>' .
                      '  </div>';
      $search_box .=  tep_hide_session_id() . '</form>';
      $search_box .= '</div>';

      ob_start();
      include('includes/modules/content/' . $this->group . '/templates/search.php');
      $template = ob_get_clean();

      $oscTemplate->addContent($template, $this->group);
    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_CONTENT_HEADER_SEARCH_STATUS');
    }

    public function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Search Box Module', 'MODULE_CONTENT_HEADER_SEARCH_STATUS', 'True', 'Do you want to enable the Search Box content module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Width', 'MODULE_CONTENT_HEADER_SEARCH_CONTENT_WIDTH', '4', 'What width container should the content be shown in?', '6', '1', 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_HEADER_SEARCH_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    public function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_CONTENT_HEADER_SEARCH_STATUS', 'MODULE_CONTENT_HEADER_SEARCH_CONTENT_WIDTH', 'MODULE_CONTENT_HEADER_SEARCH_SORT_ORDER');
    }
  }
