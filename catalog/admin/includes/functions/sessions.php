<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if (STORE_SESSIONS == 'mysql') {
    function _sess_open($save_path, $session_name) {
      return true;
    }

    function _sess_close() {
      return true;
    }

    function _sess_read($key) {
      $value_query = tep_db_query("select value from :table_sessions where sesskey = '" . tep_db_input($key) . "'");
      $value = tep_db_fetch_array($value_query);

      if (isset($value['value'])) {
        return $value['value'];
      }

      return '';
    }

    function _sess_write($key, $value) {
      $check_query = tep_db_query("select 1 from :table_sessions where sesskey = '" . tep_db_input($key) . "'");

      if ( tep_db_num_rows($check_query) > 0 ) {
        $result = tep_db_query("update :table_sessions set expiry = '" . tep_db_input(time()) . "', value = '" . tep_db_input($value) . "' where sesskey = '" . tep_db_input($key) . "'");
      } else {
        $result = tep_db_query("insert into :table_sessions values ('" . tep_db_input($key) . "', '" . tep_db_input(time()) . "', '" . tep_db_input($value) . "')");
      }

      return $result !== false;
    }

    function _sess_destroy($key) {
      $result = tep_db_query("delete from :table_sessions where sesskey = '" . tep_db_input($key) . "'");

      return $result !== false;
    }

    function _sess_gc($maxlifetime) {
      $result = tep_db_query("delete from :table_sessions where expiry < '" . (time() - $maxlifetime) . "'");

      return $result !== false;
    }

    session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
  }

  function tep_session_start() {
    $sane_session_id = true;

    if ( isset($_GET[session_name()]) ) {
      if ( (SESSION_FORCE_COOKIE_USE == 'True') || (preg_match('/^[a-zA-Z0-9,-]+$/', $_GET[session_name()]) == false) ) {
        unset($_GET[session_name()]);

        $sane_session_id = false;
      }
    }

    if ( isset($_POST[session_name()]) ) {
      if ( (SESSION_FORCE_COOKIE_USE == 'True') || (preg_match('/^[a-zA-Z0-9,-]+$/', $_POST[session_name()]) == false) ) {
        unset($_POST[session_name()]);

        $sane_session_id = false;
      }
    }

    if ( isset($_COOKIE[session_name()]) ) {
      if ( preg_match('/^[a-zA-Z0-9,-]+$/', $_COOKIE[session_name()]) == false ) {
        $session_data = session_get_cookie_params();

        setcookie(session_name(), '', time()-42000, $session_data['path'], $session_data['domain']);
        unset($_COOKIE[session_name()]);

        $sane_session_id = false;
      }
    }

    if ($sane_session_id == false) {
      tep_redirect(tep_href_link('index.php', '', 'SSL', false));
    }

    register_shutdown_function('session_write_close');

    return session_start();
  }

  function tep_session_destroy() {
    if ( isset($_COOKIE[session_name()]) ) {
      $session_data = session_get_cookie_params();

      setcookie(session_name(), '', time()-42000, $session_data['path'], $session_data['domain']);
      unset($_COOKIE[session_name()]);
    }

    return session_destroy();
  }
?>
