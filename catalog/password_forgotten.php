<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require('includes/classes/Yubico.php');

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PASSWORD_FORGOTTEN);
  
  $forgot_action = 'stepone';

  if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'stepone')) {
    $email_address = tep_db_prepare_input($HTTP_POST_VARS['email_address']);

    $check_customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_password, customers_id, customers_authentication_type from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
    if (tep_db_num_rows($check_customer_query)) {
      
      $check_customer = tep_db_fetch_array($check_customer_query);
      if ($check_customer['customers_authentication_type'] < 2) {
      
        $new_password = tep_create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
        $crypted_password = tep_encrypt_password($new_password);

        tep_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . tep_db_input($crypted_password) . "' where customers_id = '" . (int)$check_customer['customers_id'] . "'");

        tep_mail($check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'], $email_address, EMAIL_PASSWORD_REMINDER_SUBJECT, sprintf(EMAIL_PASSWORD_REMINDER_BODY, $new_password), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

        $messageStack->add_session('login', SUCCESS_PASSWORD_SENT, 'success');
        tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        
      } else {
        $process_step = 2;
        $forgot_action = 'process';
      }
      
    } else {
      $process_step = 1;
      $messageStack->add('password_forgotten', TEXT_NO_EMAIL_ADDRESS_FOUND);
    }
  }
  
  if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'process') && isset($HTTP_POST_VARS['formid']) && ($HTTP_POST_VARS['formid'] == $sessiontoken)) {
    $email_address = tep_db_prepare_input($HTTP_POST_VARS['email_address']);
    $password_forgot_type = tep_db_prepare_input($HTTP_POST_VARS['password_forgot_type']);
    $otp = tep_db_prepare_input($HTTP_POST_VARS['otp']);
    $token_customer_id = -1;
    $process_step = 2;
    $forgot_action = 'process';
    
    $update_auth_setting = '';
    $validOTP = true;
    
    if ($password_forgot_type == 1) {
      // verify OTP
      $yubi = &new Auth_Yubico(1, '');
	  try {
	    $auth = $yubi->verify($otp);
	    if (PEAR::isError($auth)) {
	      $validOTP = false;
        } 
	  } catch (Exception $e) {
	    $validOTP = false;
	  }
	  
	  if($validOTP) {
	  	$token_id = substr($otp,0,12);
	    $token_customer_query = tep_db_query("select cu.customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id,  customers_authentication_type from customers cu, customers_yubikey_mapping cym where (cu.customers_id = cym.customers_id) and (cym.customers_yubikey_tokenId = '". tep_db_input($token_id) . "')");
        if (tep_db_num_rows($token_customer_query)) {
          $token_customer = tep_db_fetch_array($token_customer_query);
          $token_customer_id = $token_customer['customers_id'];
        }
	  }
	  
    } else {
      $update_auth_setting = ', customers_authentication_type = 1';
    }

    if ($validOTP) {
      $check_customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_password, customers_id, customers_authentication_type from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
    
      if (tep_db_num_rows($check_customer_query)) {

        $check_customer = tep_db_fetch_array($check_customer_query);
        
        if (($token_customer_id == $check_customer['customers_id']) || ($password_forgot_type != 1) ) {
      
          if ($check_customer['customers_authentication_type'] == 2) {
      
            $new_password = tep_create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
            $crypted_password = tep_encrypt_password($new_password);

            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . tep_db_input($crypted_password) . "'".$update_auth_setting." where customers_id = '" . (int)$check_customer['customers_id'] . "'");
        
            tep_mail($check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'], $email_address, EMAIL_PASSWORD_REMINDER_SUBJECT, sprintf(EMAIL_PASSWORD_REMINDER_BODY, $new_password), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

            if ($password_forgot_type == 1){
              $messageStack->add_session('login', SUCCESS_PASSWORD_SENT, 'success');  
            } else {
              $messageStack->add_session('login', SUCCESS_PASSWORD_SENT_2, 'success');
            }
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
          }
        } else {
          $messageStack->add('password_forgotten', TEXT_INVALID_OTP);
        }
      } else {
        $messageStack->add('password_forgotten', TEXT_NO_EMAIL_ADDRESS_FOUND);
      }
    } else {
      $messageStack->add('password_forgotten', TEXT_INVALID_OTP);
    }
  }

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
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
    <td width="100%" valign="top"><?php echo tep_draw_form('password_forgotten', tep_href_link(FILENAME_PASSWORD_FORGOTTEN, 'action='.$forgot_action, 'SSL'), 'post', '', true); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_password_forgotten.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  if ($messageStack->size('password_forgotten') > 0) {
?>
      <tr>
        <td><?php echo $messageStack->output('password_forgotten'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  }
?>
      <tr>
        <td><table border="0" width="100%" height="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" height="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
              </tr>
              <tr>
                <td class="main" colspan="2"><?php if ($process_step != 2) { echo TEXT_MAIN; } else { echo TEXT_MAIN_STEP_2; } ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo '<b>' . ENTRY_EMAIL_ADDRESS . '</b> ' . tep_draw_input_field('email_address'); ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
              </tr>
<?php
    if ($process_step == 2) {
?>              
              <tr>
                <td><table border="0" width="100%" cellspacing="2" cellpadding="2">
        	      <tr>
            	    <td class="main"><?php echo tep_draw_radio_field('password_forgot_type','1', true) . '&nbsp;'; ?>&nbsp;<?php echo "I forgot my Password"; ?></td>
	              </tr>
	              <tr>
        	        <td class="main"><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b> YubiKey OTP: </b> ' . tep_draw_input_field('otp','','class="yubiKeyInput"'); ?></td>
        	      </tr>
    	          <tr>
        	        <td class="main"><?php echo tep_draw_radio_field('password_forgot_type','2', false) . '&nbsp;'; ?>&nbsp;<?php echo "My YubiKey is lost/damaged"; ?></td>
            	  </tr>
	              <tr>
    	            <td class="main"><?php echo tep_draw_radio_field('password_forgot_type','3', false) . '&nbsp;'; ?>&nbsp;<?php echo "I forgot my Password and my YubiKey is lost/damaged"; ?></td>
        	      </tr>
	            </table></td>
<?php
    }
?>
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
                <td><?php echo '<a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></td>
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
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
