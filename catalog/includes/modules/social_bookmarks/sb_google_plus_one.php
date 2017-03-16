<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class sb_google_plus_one {
    public $code = 'sb_google_plus_one';
    public $title;
    public $description;
    public $sort_order;
    public $icon;
    public $enabled = false;

    public function __construct() {
      $this->title = MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_TITLE;
      $this->public_title = MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_PUBLIC_TITLE;
      $this->description = MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_DESCRIPTION;

      if ( defined('MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS == 'True');
      }
    }

    public function getOutput() {
      global $lng;

      if (!isset($lng) || (isset($lng) && !is_object($lng))) {
        include('includes/classes/language.php');
        $lng = new language;
      }

      foreach ($lng->catalog_languages as $lkey => $lvalue) {
        if ($lvalue['id'] == $_SESSION['languages_id']) {
          $language_code = $lkey;
          break;
        }
      }

      $output = '<div class="g-plusone" data-href="' . tep_href_link('product_info.php', 'products_id=' . $_GET['products_id'], 'NONSSL', false) . '" data-size="' . strtolower(MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SIZE) . '" data-annotation="' . strtolower(MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ANNOTATION) . '"';

      if (MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ANNOTATION == 'Inline') {
        $output.= ' data-width="' . (int)MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_WIDTH . '" data-align="' . strtolower(MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ALIGN) . '"';
      }

      $output .= '></div>';

      $output .= '<script>
  if ( typeof window.___gcfg == "undefined" ) {
    window.___gcfg = { };
  }

  if ( typeof window.___gcfg.lang == "undefined" ) {
    window.___gcfg.lang = "' . tep_output_string_protected($language_code) . '";
  }

  (function() {
    var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
    po.src = \'https://apis.google.com/js/plusone.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>';

      return $output;
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
      return defined('MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS');
    }

    public function install() {
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Google+ +1 Button Module', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS', 'True', 'Do you want to allow products to be recommended through Google+ +1 Button?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Button Size', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SIZE', 'Small', 'Sets the size of the button.', '6', '1', 'tep_cfg_select_option(array(\'Small\', \'Medium\', \'Standard\', \'Tall\'), ', now())");
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Annotation', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ANNOTATION', 'None', 'The annotation to display next to the button.', '6', '1', 'tep_cfg_select_option(array(\'None\', \'Bubble\', \'Inline\'), ', now())");
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Inline Width', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_WIDTH', '120', 'The width of the inline annotation in pixels (minimum 120).', '6', '0', now())");
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Inline Alignment', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ALIGN', 'Left', 'The alignment of the inline annotation.', '6', '1', 'tep_cfg_select_option(array(\'Left\', \'Right\'), ', now())");
      tep_db_query("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    public function remove() {
      tep_db_query("delete from :table_configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SIZE', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ANNOTATION', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_WIDTH', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ALIGN', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SORT_ORDER');
    }
  }
