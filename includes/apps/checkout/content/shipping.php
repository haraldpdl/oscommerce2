<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_SHIPPING; ?></h1>

<?php echo osc_draw_form('checkout_address', osc_href_link('checkout', 'shipping&process', 'SSL'), 'post', '', true); ?>

<div class="contentContainer">
  <h2><?php echo TABLE_HEADING_SHIPPING_ADDRESS; ?></h2>

  <div>
    <div class="well pull-right">
      <h5><?php echo TITLE_SHIPPING_ADDRESS; ?></h5>

      <?php echo osc_address_label($OSCOM_Customer->getID(), $OSCOM_Order->getShippingAddress(), true, ' ', '<br />'); ?>
    </div>

    <?php echo TEXT_CHOOSE_SHIPPING_DESTINATION; ?><br /><br /><?php echo osc_draw_button(IMAGE_BUTTON_CHANGE_ADDRESS, 'home', osc_href_link('checkout', 'shipping&address', 'SSL'), 'info'); ?>
  </div>

  <div style="clear: both;"></div>

<?php
  if ( $OSCOM_Order->hasShippingRates() ) {
?>

  <h2><?php echo TABLE_HEADING_SHIPPING_METHOD; ?></h2>

<?php
    if ( $OSCOM_Order->getNumberOfShippingRates() > 1 ) {
?>

  <div>
    <div class="pull-right">
      <?php echo '<strong>' . TITLE_PLEASE_SELECT . '</strong>'; ?>
    </div>

    <p><?php echo TEXT_CHOOSE_SHIPPING_METHOD; ?></p>
  </div>

<?php
    }
?>

  <table border="0" width="100%" cellspacing="0" cellpadding="2" id="sm_listing">

<?php
    foreach ( $OSCOM_Order->getShippingRates() as $module ) {
      if ( count($module['methods']) == 1 ) {
        $checked = ($OSCOM_Order->hasShipping() && ($module['id'] . '_' . $module['methods'][0]['id'] == $OSCOM_Order->getShipping('id')) ? true : false);

        $shipping_cost = $currencies->format(osc_add_tax($module['methods'][0]['cost'], (isset($module['tax_class_id']) ? osc_get_tax_rate($OSCOM_Order->getShipping('tax_class_id'), $OSCOM_Order->getShippingAddress('country_id'), $OSCOM_Order->getShippingAddress('zone_id')) : 0)));

        echo '<tr id="sm_' . $module['id'] . '_' . $module['methods'][0]['id'] . '">' .
             '  <td><strong>' . $module['title'] . (isset($module['methods'][0]['title']) ? ' (' . $module['methods'][0]['title'] . ')' : '') . '</strong>&nbsp;' . (isset($module['icon']) && osc_not_null($module['icon']) ? $module['icon'] : '') . '</td>';

        if ( count($OSCOM_Order->getShippingRates()) > 1 ) {
          echo '  <td>' . $shipping_cost . '</td>' .
               '  <td align="right">' . osc_draw_radio_field('shipping', $module['id'] . '_' . $module['methods'][0]['id'], $checked) . '</td>';
        } else {
          echo '  <td align="right" colspan="2">' . $shipping_cost . osc_draw_hidden_field('shipping', $module['id'] . '_' . $module['methods'][0]['id']) . '</td>';
        }

        echo '</tr>';
      } else {
        echo '<tr>' .
             '  <td colspan="3"><strong>' . $module['title'] . '</strong>&nbsp;' . (isset($module['icon']) && osc_not_null($module['icon']) ? $module['icon'] : '') . '</td>' .
             '</tr>';

        foreach ( $module['methods'] as $methods ) {
          $checked = ($OSCOM_Order->hasShipping() && ($module['id'] . '_' . $methods['id'] == $OSCOM_Order->getShipping('id')) ? true : false);

          $shipping_cost = $currencies->format(osc_add_tax($methods['cost'], (isset($module['tax_class_id']) ? osc_get_tax_rate($OSCOM_Order->getShipping('tax_class_id'), $OSCOM_Order->getShippingAddress('country_id'), $OSCOM_Order->getShippingAddress('zone_id')) : 0)));

          echo '<tr id="sm_' . $module['id'] . '_' . $methods['id'] . '">' .
               '  <td style="padding-left: 15px;">' . $methods['title'] . '</td>';

          if ( count($OSCOM_Order->getShippingRates()) > 1 ) {
            echo '  <td>' . $shipping_cost . '</td>' .
                 '  <td align="right">' . osc_draw_radio_field('shipping', $module['id'] . '_' . $methods['id'], $checked) . '</td>';
          } else {
            echo '  <td align="right" colspan="2">' . $shipping_cost . osc_draw_hidden_field('shipping', $module['id'] . '_' . $methods['id']) . '</td>';
          }

          echo '</tr>';
        }
      }

      if ( isset($module['error']) ) {
        echo '<tr>' .
             '  <td colspan="3">' . $module['error'] . '</td>' .
             '</tr>';
      }
    }
?>

  </table>

<script>
if ( $('#sm_listing [id^=sm_]').length == 1 ) {
  $('#sm_listing [id=sm_' + $('#sm_listing input[name=shipping]').val() + ']').addClass('moduleRowSelected');
} else {
  $('#sm_listing [id=sm_' + $('#sm_listing input[name=shipping]:checked').val() + ']').addClass('moduleRowSelected');
}

$('#sm_listing [id^=sm_]').hover(
  function () {
    $(this).addClass('moduleRowOver');
  },
  function () {
    $(this).removeClass('moduleRowOver');
  }
).click(
  function() {
    $('#sm_listing .moduleRowSelected').removeClass('moduleRowSelected');
    $('input[name=shipping][value=' + $(this).attr('id').substr(3) + ']').prop('checked', true);
    $(this).addClass('moduleRowSelected');
  }
);
</script>

<?php
  }
?>

  <h2><?php echo TABLE_HEADING_COMMENTS; ?></h2>

  <div>
    <?php echo osc_draw_textarea_field('comments', 'soft', '60', '5', $OSCOM_Order->hasInfo('comments') ? $OSCOM_Order->getInfo('comments') : ''); ?>
  </div>

  <div>
    <div class="pull-right"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', null, 'success'); ?></div>

    <div style="width: 60%; padding-top: 5px; padding-left: 15%;">
      <div class="progress">
        <div class="bar" style="width: 33%;"></div>
      </div>

      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td width="33%" class="text-center checkoutBarCurrent"><?php echo CHECKOUT_BAR_DELIVERY; ?></td>
          <td width="33%" class="text-center checkoutBarTo"><?php echo CHECKOUT_BAR_PAYMENT; ?></td>
          <td width="33%" class="text-center checkoutBarTo"><?php echo CHECKOUT_BAR_CONFIRMATION; ?></td>
        </tr>
      </table>
    </div>
  </div>
</div>

</form>
