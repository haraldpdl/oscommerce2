<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<script type="text/javascript"><!--
var selected;

function selectRowEffect(object, buttonSelect) {
  if (!selected) {
    if (document.getElementById) {
      selected = document.getElementById('defaultSelected');
    } else {
      selected = document.all['defaultSelected'];
    }
  }

  if (selected) selected.className = 'moduleRow';
  object.className = 'moduleRowSelected';
  selected = object;

// one button is not an array
  if (document.checkout_payment.payment[0]) {
    document.checkout_payment.payment[buttonSelect].checked=true;
  } else {
    document.checkout_payment.payment.checked=true;
  }
}

function rowOverEffect(object) {
  if (object.className == 'moduleRow') object.className = 'moduleRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'moduleRowOver') object.className = 'moduleRow';
}
//--></script>
<?php echo $OSCOM_Payment->getJavascriptValidation(); ?>

<h1><?php echo HEADING_TITLE_PAYMENT; ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists('payment_error') ) {
    echo $OSCOM_MessageStack->get('payment_error');
  }
?>

<?php echo osc_draw_form('checkout_payment', osc_href_link('checkout', 'payment&process', 'SSL'), 'post', 'onsubmit="return check_form();"', true); ?>

<div class="contentContainer">
  <h2><?php echo TABLE_HEADING_BILLING_ADDRESS; ?></h2>

  <div class="contentText">
    <div class="ui-widget infoBoxContainer" style="float: right;">
      <div class="ui-widget-header infoBoxHeading"><?php echo TITLE_BILLING_ADDRESS; ?></div>

      <div class="ui-widget-content infoBoxContents">
        <?php echo osc_address_label($OSCOM_Customer->getID(), $OSCOM_Order->getBillingAddress(), true, ' ', '<br />'); ?>
      </div>
    </div>

    <?php echo TEXT_SELECTED_BILLING_DESTINATION; ?><br /><br /><?php echo osc_draw_button(IMAGE_BUTTON_CHANGE_ADDRESS, 'home', osc_href_link('checkout', 'payment&address', 'SSL'), 'info'); ?>
  </div>

  <div style="clear: both;"></div>

  <h2><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></h2>

<?php
  if ( count($OSCOM_Payment->getModules()) > 1 ) {
?>

  <div class="contentText">
    <div style="float: right;">
      <?php echo '<strong>' . TITLE_PLEASE_SELECT . '</strong>'; ?>
    </div>

    <?php echo TEXT_SELECT_PAYMENT_METHOD; ?>
  </div>

<?php
    } else {
?>

  <div class="contentText">
    <?php echo TEXT_ENTER_PAYMENT_INFORMATION; ?>
  </div>

<?php
    }
?>

  <div class="contentText">

<?php
  $radio_buttons = 0;
  foreach ( $OSCOM_Payment->getSelectionFields() as $m ) {
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
    if ( ($OSCOM_Order->hasBilling() && ($m['id'] == $OSCOM_Order->getBilling('id'))) || (count($OSCOM_Payment->getModules()) == 1) ) {
      echo '      <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
    } else {
      echo '      <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
    }
?>

        <td><strong><?php echo $m['module']; ?></strong></td>
        <td align="right">

<?php
    if ( count($OSCOM_Payment->getModules()) > 1 ) {
      echo osc_draw_radio_field('payment', $m['id'], ($OSCOM_Order->hasBilling() && ($m['id'] == $OSCOM_Order->getBilling('id'))));
    } else {
      echo osc_draw_hidden_field('payment', $m['id']);
    }
?>

        </td>
      </tr>

<?php
    if ( isset($m['error']) ) {
?>

      <tr>
        <td colspan="2"><?php echo $m['error']; ?></td>
      </tr>

<?php
    } elseif ( isset($m['fields']) && is_array($m['fields']) ) {
?>

      <tr>
        <td colspan="2"><table border="0" cellspacing="0" cellpadding="2">

<?php
      foreach ( $m['fields'] as $f ) {
?>

          <tr>
            <td><?php echo $f['title']; ?></td>
            <td><?php echo $f['field']; ?></td>
          </tr>

<?php
      }
?>

        </table></td>
      </tr>

<?php
    }
?>

    </table>

<?php
    $radio_buttons++;
  }
?>

  </div>

  <h2><?php echo TABLE_HEADING_COMMENTS; ?></h2>

  <div class="contentText">
    <?php echo osc_draw_textarea_field('comments', 'soft', '60', '5', $OSCOM_Order->hasInfo('comments') ? $OSCOM_Order->getInfo('comments') : ''); ?>
  </div>

  <div class="contentText">
    <div style="float: left; width: 60%; padding-top: 5px; padding-left: 15%;">
      <div class="progress">
        <div class="bar" style="width: 66%;"></div>
      </div>

      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td align="center" width="33%" class="checkoutBarFrom"><?php echo '<a href="' . osc_href_link('checkout', 'shipping', 'SSL') . '" class="checkoutBarFrom">' . CHECKOUT_BAR_DELIVERY . '</a>'; ?></td>
          <td align="center" width="33%" class="checkoutBarCurrent"><?php echo CHECKOUT_BAR_PAYMENT; ?></td>
          <td align="center" width="33%" class="checkoutBarTo"><?php echo CHECKOUT_BAR_CONFIRMATION; ?></td>
        </tr>
      </table>
    </div>

    <div style="float: right;"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', null, 'success'); ?></div>
  </div>
</div>

</form>
