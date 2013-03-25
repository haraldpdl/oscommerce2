<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  $any_out_of_stock = 0;
?>

<h1><?php echo HEADING_TITLE; ?></h1>

<?php echo osc_draw_form('cart_quantity', osc_href_link('cart', 'update'), 'post', null, true); ?>

<div class="contentContainer">
  <h2><?php echo TABLE_HEADING_PRODUCTS; ?></h2>

  <div class="contentText">
    <table border="0" width="100%" cellspacing="0" cellpadding="0">

<?php
  foreach ( $_SESSION['cart']->get_products() as $p ) {
    echo '      <tr>';

    $products_name = '<table border="0" cellspacing="2" cellpadding="2">' .
                     '  <tr>' .
                     '    <td align="center"><a href="' . osc_href_link('products', 'id=' . $p['id']) . '">' . osc_image(DIR_WS_IMAGES . $p['image'], $p['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></td>' .
                     '    <td valign="top"><a href="' . osc_href_link('products', 'id=' . $p['id']) . '"><strong>' . $p['name'] . '</strong></a>';

    if (STOCK_CHECK == 'true') {
      $stock_check = osc_check_stock($p['id'], $p['quantity']);
      if (osc_not_null($stock_check)) {
        $any_out_of_stock = 1;

        $products_name .= $stock_check;
      }
    }

    if (isset($p['attributes']) && is_array($p['attributes'])) {
      foreach ( $p['attributes'] as $pa ) {
        $products_name .= '<br /><small><i> - ' . $pa['option'] . ' ' . $pa['value'] . '</i></small>' .
                          osc_draw_hidden_field('id[' . $p['id'] . '][' . $pa['option_id'] . ']', $pa['value_id']);
      }
    }

    $products_name .= '<br /><br />' . osc_draw_input_field('cart_quantity[]', $p['quantity'], 'size="4"') . osc_draw_hidden_field('products_id[]', $p['id']) . osc_draw_button(IMAGE_BUTTON_UPDATE, 'refresh') . '&nbsp;&nbsp;&nbsp;' . TEXT_OR . '<a href="' . osc_href_link('cart', 'remove&id=' . $p['id'] . '&formid=' . md5($_SESSION['sessiontoken'])) . '">' . TEXT_REMOVE . '</a>';

    $products_name .= '    </td>' .
                      '  </tr>' .
                      '</table>';

    echo '        <td valign="top">' . $products_name . '</td>' .
         '        <td align="right" valign="top"><strong>' . $currencies->display_price($p['final_price'], osc_get_tax_rate($p['tax_class_id']), $p['quantity']) . '</strong></td>' .
         '      </tr>';
  }
?>

    </table>

    <p align="right"><strong><?php echo SUB_TITLE_SUB_TOTAL; ?> <?php echo $currencies->format($_SESSION['cart']->show_total()); ?></strong></p>

<?php
  if ($any_out_of_stock == 1) {
    if (STOCK_ALLOW_CHECKOUT == 'true') {
?>

    <p class="stockWarning" align="center"><?php echo OUT_OF_STOCK_CAN_CHECKOUT; ?></p>

<?php
    } else {
?>

    <p class="stockWarning" align="center"><?php echo OUT_OF_STOCK_CANT_CHECKOUT; ?></p>

<?php
    }
  }
?>

  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo osc_draw_button(IMAGE_BUTTON_CHECKOUT, 'play', osc_href_link('checkout', '', 'SSL'), 'success'); ?></span>
  </div>

<?php
  $initialize_checkout_methods = $OSCOM_Payment->checkout_initialization_method();

  if (!empty($initialize_checkout_methods)) {
?>

  <p align="right" style="clear: both; padding: 15px 50px 0 0;"><?php echo TEXT_ALTERNATIVE_CHECKOUT_METHODS; ?></p>

<?php
    reset($initialize_checkout_methods);
    while (list(, $value) = each($initialize_checkout_methods)) {
?>

  <p align="right"><?php echo $value; ?></p>

<?php
    }
  }
?>

</div>

</form>
