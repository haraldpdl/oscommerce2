<?php
/*
  $Id: account_edit.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require('includes/classes/Yubico.php');

  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// needs to be included earlier to set the success message in the messageStack
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ACCOUNT_AUTHENTICATION);

  if (isset($HTTP_POST_VARS['action']) && ($HTTP_POST_VARS['action'] == 'process')) {
    $authentication_type = tep_db_prepare_input($HTTP_POST_VARS['authentication_type']);
    $yubico_tokenId = tep_db_prepare_input($HTTP_POST_VARS['tokenId']);

    $error = false;
    $assign = false;
    
    if ($yubico_tokenId == '') {
      $token_map_id = $HTTP_POST_VARS['yubikey_delete'];
      $authentication_type = tep_db_prepare_input($HTTP_POST_VARS['authentication_type']);
      
      $token_query = tep_db_query("select customers_yubikey_tokenId, customers_yubikey_mapping_id from " . TABLE_CUSTOMERS_AUTHENTICATION_TYPE . " where customers_id = '" . (int)$customer_id . "'");
      $assigned_token = tep_db_num_rows($token_query);
      
      
      if(!is_null($token_map_id)) {
        $tcount = count($token_map_id);
        $token_string = '';
      
        for($i=0; $i < $tcount; $i++) {
          $token_string .= $token_map_id[$i].',';
        }
        $token_string .= '0';

        if (($authentication_type > 1) && ($assigned_token == $tcount)) {
          $error = true;
          $messageStack->add_session('account_authentications', INFO_YUBIKEY_REMOVE_ALL_ERROR, 'error');
        } else {
          tep_db_query("DELETE FROM ".TABLE_CUSTOMERS_AUTHENTICATION_TYPE." WHERE customers_yubikey_mapping_id IN (".$token_string.")");
        }
      }
      
      if (($assigned_token == 0) && ($authentication_type > 1)) {
      	$error = true;
        $messageStack->add_session('account_authentications', INFO_YUBIKEY_REMOVE_ALL_ERROR, 'error');
      }

      if(!$error) {
        $account_info_query = tep_db_query("select customers_authentication_type, customers_gender, customers_firstname, customers_lastname, customers_dob, customers_email_address, customers_telephone, customers_fax from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
        $account_info = tep_db_fetch_array($account_info_query);
      
        if ($account_info['customers_authentication_type'] == $authentication_type) {
          // no updates
          $error = true; 
        }
      }

      if(!$error) {
        tep_db_query("UPDATE " . TABLE_CUSTOMERS . " SET customers_authentication_type = ".$authentication_type." WHERE customers_id = ".(int)$customer_id );
        $messageStack->add_session('account_authentications', SUCCESS_ACCOUNT_AUTH_UPDATED, 'success');
      }
      
      tep_redirect(tep_href_link(FILENAME_ACCOUNT_AUTHENTICATION, '', 'SSL'));
    } else {
      $token_customers_id = $customer_id;
      $yubico_token_id = substr($yubico_tokenId, 0, 12);
	  
      $tok_check_query = tep_db_query("select customers_id as CustID from " . TABLE_CUSTOMERS_AUTHENTICATION_TYPE . " where customers_yubikey_tokenId = '" . $yubico_token_id . "'");
      if ($tok_check = tep_db_fetch_array($tok_check_query)) {
        $token_customers_id = $tok_check['CustID'];
        $assign = true;
      }
		
      // validate the OTP
      $yubi = &new Auth_Yubico(1, '');
      try {
        $auth = $yubi->verify($yubico_tokenId);
        if (PEAR::isError($auth)) {
          $error = true;
        }
      } catch (Exception $e) {
        $error = true;
      }
      
      if ($error) {
        $messageStack->add_session('account_authentications', INFO_YUBIKEY_TOKENID_INVALID_ERROR, 'error');
        tep_redirect(tep_href_link(FILENAME_ACCOUNT_AUTHENTICATION, '', 'SSL'));
      } else {
        if($assign) {
          $messageStack->add_session('account_authentications', INFO_YUBIKEY_TOKENID_ASSIGN_ERROR,'error');
        } else {
          tep_db_query("INSERT INTO " . TABLE_CUSTOMERS_AUTHENTICATION_TYPE . "(customers_id, customers_yubikey_tokenId) VALUES (". $customer_id .",'" . $yubico_token_id . "')");
          
          tep_db_query("UPDATE " . TABLE_CUSTOMERS . " SET customers_authentication_type = ". $authentication_type. " WHERE customers_id = " .(int)$customer_id );
          $messageStack->add_session('account_authentications', SUCCESS_ACCOUNT_AUTH_UPDATED, 'success');
        }
      }
      
      tep_redirect(tep_href_link(FILENAME_ACCOUNT_AUTHENTICATION, '', 'SSL'));
    }
  }

  $account_query = tep_db_query("select customers_authentication_type, customers_gender, customers_firstname, customers_lastname, customers_dob, customers_email_address, customers_telephone, customers_fax from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
  $account = tep_db_fetch_array($account_query);
  $token_query = tep_db_query("select customers_yubikey_tokenId, customers_yubikey_mapping_id from " . TABLE_CUSTOMERS_AUTHENTICATION_TYPE . " where customers_id = '" . (int)$customer_id . "'");
  while($token_record = tep_db_fetch_array($token_query)) {
    $account_tokens [] = $token_record;
  }

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
    <td width="100%" valign="top"><?php echo tep_draw_form('account_authentications', tep_href_link(FILENAME_ACCOUNT_AUTHENTICATION, '', 'SSL'), 'post', 'onSubmit="return check_form(account_authentications);"') . tep_draw_hidden_field('action', 'process'); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
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
        <td class="main"><b><?php echo MY_AUTHENTICATION_TITLE; ?></b></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" class="infoBox">
          <tr>
            <td><table border="0" width="100%" cellspacing="1" cellpadding="2">
              <tr class="infoBoxContents">
                <td><table border="0" width="100%" cellspacing="2" cellpadding="2">
        	  <tr>
            	    <td class="main"><?php echo tep_draw_radio_field('authentication_type','1',(($account['customers_authentication_type'] == '1') ? true : false)) . '&nbsp;'; ?>&nbsp;<?php echo ENTRY_CUSTOMER_AUTH_1_TEXT; ?></td>
	            </tr>
    	          <tr>
        	    <td class="main"><?php echo tep_draw_radio_field('authentication_type','2',(($account['customers_authentication_type'] == '2') ? true : false)) . '&nbsp;'; ?>&nbsp;<?php echo ENTRY_CUSTOMER_AUTH_2_TEXT; ?></td>
            	  </tr>
	       	</table></td>              
              </tr>
	      <tr class="infoBoxContents">
	        <td><table border="0" width="100%" cellspacing="2" cellpadding="2">
	          <tr>
	            <td class="main" width="30%"><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.YUBICO_TOKEN_ID; ?></td>
	            <td class="main" width="70%"><?php echo tep_draw_input_field('tokenId','','class="yubiKeyInput"');?></td>
	            <td align="left" class="main" width="30%"><?php echo tep_image_submit ('button_add_yubikey.gif', 'Add');?></td>
	          </tr>
	          <?php if (sizeof($account_tokens) > 0) {?>
	          <tr>
	            <td colspan="3">
<?php
                      $info_box_contents = array();
                      $info_box_contents[0][] = array('align' => 'center',
                                                      'params' => 'class="productListing-heading" width="25%"',
                                                      'text' => 'Remove');

                      $info_box_contents[0][] = array('params' => 'class="productListing-heading"',
                                                      'text' => 'YubiKey Token ID');
                      // Loop for Tokens
                      for ($i=0, $n=sizeof($account_tokens); $i<$n; $i++) {
                        $info_box_contents[$i+1][] = array('align' => 'center',
                                                           'params' => 'class="productListing-data" valign="top"',
                                                           'text' => tep_draw_checkbox_field('yubikey_delete[]', $account_tokens[$i]['customers_yubikey_mapping_id']));

                        $info_box_contents[$i+1][] = array('params' => 'class="productListing-data"',
                                                           'text' => $account_tokens[$i]['customers_yubikey_tokenId']);
                        
                      }
                                                                                                            
                      new contentBox($info_box_contents);
?>	                
	            </td>
                  </tr>
	          <?php }?>
	        </table></td>
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
