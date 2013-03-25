<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_ORDERS_INFO; ?></h1>

<div class="contentContainer">
  <h2><?php echo sprintf(HEADING_ORDER_NUMBER, $OSCOM_OrderInfo->getInfo('id')) . ' <span class="contentText">(' . $OSCOM_OrderInfo->getInfo('status') . ')</span>'; ?></h2>

  <div class="contentText">
    <div>
      <span style="float: right;"><?php echo HEADING_ORDER_TOTAL . ' ' . $OSCOM_OrderInfo->getTotal(); ?></span>
      <?php echo HEADING_ORDER_DATE . ' ' . osc_date_long($OSCOM_OrderInfo->getInfo('date_purchased')); ?>
    </div>

    <table border="0" width="100%" cellspacing="1" cellpadding="2">
      <tr>

<?php
  if ( $OSCOM_OrderInfo->hasShippingAddress() ) {
?>
        <td width="30%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td><strong><?php echo HEADING_DELIVERY_ADDRESS; ?></strong></td>
          </tr>
          <tr>
            <td><?php echo osc_address_format($OSCOM_OrderInfo->getShippingAddress('format_id'), $OSCOM_OrderInfo->getShippingAddress(), 1, ' ', '<br />'); ?></td>
          </tr>
<?php
    if ( $OSCOM_OrderInfo->hasShipping() ) {
?>
          <tr>
            <td><strong><?php echo HEADING_SHIPPING_METHOD; ?></strong></td>
          </tr>
          <tr>
            <td><?php echo $OSCOM_OrderInfo->getShipping(); ?></td>
          </tr>
<?php
    }
?>
        </table></td>
<?php
  }
?>
        <td width="<?php echo ($OSCOM_OrderInfo->hasShipping() ? '70%' : '100%'); ?>" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if ( count($OSCOM_OrderInfo->getInfo('tax_groups')) > 1 ) {
?>
          <tr>
            <td colspan="2"><strong><?php echo HEADING_PRODUCTS; ?></strong></td>
            <td align="right"><strong><?php echo HEADING_TAX; ?></strong></td>
            <td align="right"><strong><?php echo HEADING_TOTAL; ?></strong></td>
          </tr>
<?php
  } else {
?>
          <tr>
            <td colspan="2"><strong><?php echo HEADING_PRODUCTS; ?></strong></td>
            <td align="right"><strong><?php echo HEADING_TOTAL; ?></strong></td>
          </tr>
<?php
  }

  foreach ( $OSCOM_OrderInfo->getProducts() as $p ) {
    echo '          <tr>' . "\n" .
         '            <td align="right" valign="top" width="30">' . $p['quantity'] . '&nbsp;x&nbsp;</td>' . "\n" .
         '            <td valign="top">' . $p['name'];

    if ( isset($p['attributes']) ) {
      foreach ( $p['attributes'] as $pa ) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $pa['option'] . ': ' . $pa['value'] . '</i></small></nobr>';
      }
    }

    echo '</td>' . "\n";

    if ( count($OSCOM_OrderInfo->getInfo('tax_groups')) > 1 ) {
      echo '            <td valign="top" align="right">' . osc_display_tax_value($p['tax']) . '%</td>' . "\n";
    }

    echo '            <td align="right" valign="top">' . $currencies->format(osc_add_tax($p['final_price'], $p['tax']) * $p['quantity'], true, $OSCOM_OrderInfo->getInfo('currency'), $OSCOM_OrderInfo->getInfo('currency_value')) . '</td>' . "\n" .
         '          </tr>' . "\n";
  }
?>
        </table></td>
      </tr>
    </table>
  </div>

  <h2><?php echo HEADING_BILLING_INFORMATION; ?></h2>

  <div class="contentText">
    <table border="0" width="100%" cellspacing="1" cellpadding="2">
      <tr>
        <td width="30%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td><strong><?php echo HEADING_BILLING_ADDRESS; ?></strong></td>
          </tr>
          <tr>
            <td><?php echo osc_address_format($OSCOM_OrderInfo->getBillingAddress('format_id'), $OSCOM_OrderInfo->getBillingAddress(), 1, ' ', '<br />'); ?></td>
          </tr>
          <tr>
            <td><strong><?php echo HEADING_PAYMENT_METHOD; ?></strong></td>
          </tr>
          <tr>
            <td><?php echo $OSCOM_OrderInfo->getInfo('payment_method'); ?></td>
          </tr>
        </table></td>
        <td width="70%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  foreach ( $OSCOM_OrderInfo->getTotals() as $t ) {
    echo '          <tr>' . "\n" .
         '            <td align="right" width="100%">' . $t['title'] . '</td>' . "\n" .
         '            <td align="right">' . $t['text'] . '</td>' . "\n" .
         '          </tr>' . "\n";
  }
?>
        </table></td>
      </tr>
    </table>
  </div>

  <h2><?php echo HEADING_ORDER_HISTORY; ?></h2>

  <div class="contentText">
    <table border="0" width="100%" cellspacing="1" cellpadding="2">
      <tr>
        <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  foreach ( $OSCOM_OrderInfo->getStatusHistory() as $status ) {
    echo '          <tr>' . "\n" .
         '            <td valign="top" width="70">' . osc_date_short($status['date_added']) . '</td>' . "\n" .
         '            <td valign="top" width="70">' . $status['name'] . '</td>' . "\n" .
         '            <td valign="top">' . (empty($status['comments']) ? '&nbsp;' : nl2br(osc_output_string_protected($status['comments']))) . '</td>' . "\n" .
         '          </tr>' . "\n";
  }
?>
        </table></td>
      </tr>
    </table>
  </div>

<?php
  if (DOWNLOAD_ENABLED == 'true') include(DIR_WS_MODULES . 'downloads.php');
?>

  <div class="buttonSet">
    <?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'arrow-left', osc_href_link('account', 'orders' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'SSL')); ?>
  </div>
</div>
