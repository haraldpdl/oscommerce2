<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class bm_currencies {
    public $code = 'bm_currencies';
    public $group = 'boxes';
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->title = MODULE_BOXES_CURRENCIES_TITLE;
      $this->description = MODULE_BOXES_CURRENCIES_DESCRIPTION;

      if ( defined('MODULE_BOXES_CURRENCIES_STATUS') ) {
        $this->sort_order = MODULE_BOXES_CURRENCIES_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_CURRENCIES_STATUS == 'True');

        $this->group = ((MODULE_BOXES_CURRENCIES_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    public function execute() {
      global $PHP_SELF, $currencies, $request_type, $oscTemplate;

      if (substr(basename($PHP_SELF), 0, 8) != 'checkout') {
        if (isset($currencies) && is_object($currencies) && (count($currencies->currencies) > 1)) {
          $currencies_array = array();

          foreach ($currencies->currencies as $key => $value) {
            $currencies_array[] = array('id' => $key, 'text' => $value['title']);
          }

          $hidden_get_variables = '';

          foreach ($_GET as $key => $value) {
            if ( is_string($value) && ($key != 'currency') && ($key != session_name()) && ($key != 'x') && ($key != 'y') ) {
              $hidden_get_variables .= tep_draw_hidden_field($key, $value);
            }
          }

          $form_output = tep_draw_form('currencies', tep_href_link($PHP_SELF, '', $request_type, false), 'get') . tep_draw_pull_down_menu('currency', $currencies_array, $_SESSION['currency'], 'onchange="this.form.submit();" style="width: 100%"') . $hidden_get_variables . tep_hide_session_id() . '</form>';

          ob_start();
          include('includes/modules/boxes/templates/currencies.php');
          $data = ob_get_clean();

          $oscTemplate->addBlock($data, $this->group);
        }
      }
    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_BOXES_CURRENCIES_STATUS');
    }

    public function install() {
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Currencies Module', 'MODULE_BOXES_CURRENCIES_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_CURRENCIES_CONTENT_PLACEMENT', 'Right Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_CURRENCIES_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    public function remove() {
      tep_db_query("delete from :table_configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_BOXES_CURRENCIES_STATUS', 'MODULE_BOXES_CURRENCIES_CONTENT_PLACEMENT', 'MODULE_BOXES_CURRENCIES_SORT_ORDER');
    }
  }
