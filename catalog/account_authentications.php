<?php
/*
  $Id: account_edit.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require('includes/classes/yubico.php');

  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  $customer_tokens_array = array();

  $customer_tokens_query = tep_db_query("select customers_yubikey_mapping_id, customers_yubikey_tokenId from " . TABLE_CUSTOMERS_AUTHENTICATION_TYPE . " where customers_id = '" . (int)$customer_id . "'");
  while ($customer_tokens = tep_db_fetch_array($customer_tokens_query)) {
    $customer_tokens_array[$customer_tokens['customers_yubikey_mapping_id']] = $customer_tokens['customers_yubikey_tokenId'];
  }

// needs to be included earlier to set the success message in the messageStack
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ACCOUNT_AUTHENTICATION);

  if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'process') && isset($HTTP_POST_VARS['formid']) && ($HTTP_POST_VARS['formid'] == $sessiontoken)) {
    $authentication_type = (int)$HTTP_POST_VARS['authentication_type'];
    $yubico_tokenId = tep_db_prepare_input($HTTP_POST_VARS['tokenId']);
    $token_map_id = $HTTP_POST_VARS['yubikey_delete'];

    $error = false;
    $redirect_to = 'account';

// Insert new YubiKey OTP
    if (!empty($yubico_tokenId)) {
      $tok_check_query = tep_db_query("select customers_id from " . TABLE_CUSTOMERS_AUTHENTICATION_TYPE . " where customers_id = '" . (int)$customer_id . "' and customers_yubikey_tokenId = '" . tep_db_input(substr($yubico_tokenId, 0, 12)) . "' limit 1");
      if (tep_db_num_rows($tok_check_query) < 1) {
// validate the OTP
        $yubi = new Auth_Yubico();

        if ($yubi->verify($yubico_tokenId)) {
          tep_db_query("insert into " . TABLE_CUSTOMERS_AUTHENTICATION_TYPE . " (customers_id, customers_yubikey_tokenId) values ('" . (int)$customer_id . "', '" . tep_db_input(substr($yubico_tokenId, 0, 12)) . "')");
          $mapping_id = tep_db_insert_id();

          $customer_tokens_array[$mapping_id] = substr($yubico_tokenId, 0, 12);

          $redirect_to = 'authentication_method';
        } else {
          $error = true;

          $messageStack->add('account_authentications', INFO_YUBIKEY_TOKENID_INVALID_ERROR, 'error');
        }
      } else {
        $error = true;

        $messageStack->add('account_authentications', INFO_YUBIKEY_TOKENID_ASSIGN_ERROR, 'error');
      }
    }

// Delete selected YubiKey OTPs
    if (!empty($token_map_id) && is_array($token_map_id)) {
      $tokens_delete_safe_array = array();

      foreach ($token_map_id as $delete_token_id) {
        if (is_numeric($delete_token_id) && isset($customer_tokens_array[$delete_token_id])) {
          $tokens_delete_safe_array[] = (int)$delete_token_id;

          unset($customer_tokens_array[$delete_token_id]);
        }
      }

      if (!empty($tokens_delete_safe_array)) {
        tep_db_query("delete from " . TABLE_CUSTOMERS_AUTHENTICATION_TYPE . " where customers_id = '" . (int)$customer_id . "' and customers_yubikey_mapping_id in (" . implode(',', $tokens_delete_safe_array) . ")");

        $redirect_to = 'authentication_method';
      }
    }

// Assign authentication scheme
    $check_query = tep_db_query("select customers_authentication_type from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
    $check = tep_db_fetch_array($check_query);

    $set_authentication_type = $check['customers_authentication_type'];

    if (empty($customer_tokens_array) && in_array($check['customers_authentication_type'], array('2', '3'))) {
      $set_authentication_type = 1;
    } elseif (empty($customer_tokens_array) && in_array($authentication_type, array('2', '3'))) {
      $set_authentication_type = 1;

      $error = true;

      $messageStack->add('account_authentications', INFO_YUBIKEY_REQUIRED_ERROR, 'error');
    } elseif (($authentication_type != $check['customers_authentication_type']) && in_array($authentication_type, array(1, 2, 3))) {
      $set_authentication_type = $authentication_type;
    }

    if ($set_authentication_type != $check['customers_authentication_type']) {
      tep_db_query("update " . TABLE_CUSTOMERS . " set customers_authentication_type = '" . (int)$set_authentication_type . "' where customers_id = '" . (int)$customer_id . "'");

      $messageStack->add_session('account_authentications', INFO_ACCOUNT_AUTH_UPDATED_SUCCESS, 'success');

      $redirect_to = 'authentication_method';
    }

    if ($error == false) {
// reset session token
      $sessiontoken = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());

      if ($redirect_to == 'account') {
        tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
      } else {
        tep_redirect(tep_href_link(FILENAME_ACCOUNT_AUTHENTICATION, '', 'SSL'));
      }
    }
  }

  $account_query = tep_db_query("select customers_authentication_type, customers_gender, customers_firstname, customers_lastname, customers_dob, customers_email_address, customers_telephone, customers_fax from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
  $account = tep_db_fetch_array($account_query);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_AUTHENTICATION, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<?php require('includes/form_check.js.php'); ?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<div class="container">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><?php echo tep_draw_form('account_authentications', tep_href_link(FILENAME_ACCOUNT_AUTHENTICATION, 'action=process', 'SSL'), 'post', 'onsubmit="return check_form(account_authentications);"', true); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_account.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  if ($messageStack->size('account_authentications') > 0) {
?>
      <tr>
        <td><?php echo $messageStack->output('account_authentications'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  }
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><b><?php echo MY_AUTHENTICATION_TITLE; ?></b></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><?php echo tep_draw_radio_field('authentication_type', '1', (($account['customers_authentication_type'] == '1') ? true : false)) . '&nbsp;' . ENTRY_CUSTOMER_AUTH_1_TEXT; ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo tep_draw_radio_field('authentication_type', '2', (($account['customers_authentication_type'] == '2') ? true : false)) . '&nbsp;' . ENTRY_CUSTOMER_AUTH_2_TEXT; ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo tep_draw_radio_field('authentication_type', '3', (($account['customers_authentication_type'] == '3') ? true : false)) . '&nbsp;' . ENTRY_CUSTOMER_AUTH_3_TEXT; ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><b><?php echo MY_YUBIKEYS_TITLE; ?></b></td>
          </tr>
        </table></td>
      </tr>
<?php
  if (!empty($customer_tokens_array)) {
?>
      <tr>
        <td>
<?php
    $info_box_contents = array();
    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading" width="25%"',
                                    'text' => TABLE_HEADING_REMOVE);

    $info_box_contents[0][] = array('params' => 'class="productListing-heading"',
                                    'text' => TABLE_HEADING_YUBIKEY_TOKEN_ID);
// Loop for Tokens
    $i=1;
    foreach ($customer_tokens_array as $token_id => $token) {
      $info_box_contents[$i][] = array('align' => 'center',
                                       'params' => 'class="productListing-data" valign="top"',
                                       'text' => tep_draw_checkbox_field('yubikey_delete[]', $token_id));

      $info_box_contents[$i][] = array('params' => 'class="productListing-data"',
                                       'text' => $token);

      $i++;
    }

    new contentBox($info_box_contents);
?>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  }
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><?php echo ENTRY_NEW_YUBIKEY; ?></td>
                <td class="main"><?php echo tep_draw_input_field('tokenId', '', 'class="yubiKeyInput"');?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></td>
                <td align="right"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></td>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></form></td>
<!-- body_text_eof //-->
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    </table></td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
