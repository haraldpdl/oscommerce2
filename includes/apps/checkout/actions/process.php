<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_process {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_Order, $OSCOM_Payment, $OSCOM_PDO, $insert_id, $currencies;

      if ( isset($OSCOM_Payment->get()->form_action_url) ) {
        if ( !isset($_POST['formid']) || ($_POST['formid'] != $_SESSION['sessiontoken']) ) {
          osc_redirect(osc_href_link('cart'));
        }
      }

// load the before_process function from the payment modules
      $OSCOM_Payment->before_process();

      $Qcustomer = $OSCOM_PDO->prepare('select c.customers_firstname, c.customers_lastname, c.customers_telephone, c.customers_email_address, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, co.countries_id, co.countries_name, co.countries_iso_code_2, co.countries_iso_code_3, co.address_format_id, ab.entry_state from " . TABLE_CUSTOMERS . " c, " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) left join " . TABLE_COUNTRIES . " co on (ab.entry_country_id = co.countries_id) where c.customers_id = :customers_id and c.customers_default_address_id = ab.address_book_id and c.customers_id = ab.customers_id');
      $Qcustomer->bindInt(':customers_id', $OSCOM_Customer->getID());
      $Qcustomer->execute();

      $sql_data_array = array('customers_id' => $OSCOM_Customer->getID(),
                              'customers_name' => $Qcustomer->value('customers_firstname') . ' ' . $Qcustomer->value('customers_lastname'),
                              'customers_company' => $Qcustomer->value('entry_company'),
                              'customers_street_address' => $Qcustomer->value('entry_street_address'),
                              'customers_suburb' => $Qcustomer->value('entry_suburb'),
                              'customers_city' => $Qcustomer->value('entry_city'),
                              'customers_postcode' => $Qcustomer->value('entry_postcode'),
                              'customers_state' => (osc_not_null($Qcustomer->value('entry_state')) ? $Qcustomer->value('entry_state') : $Qcustomer->value('zone_name')),
                              'customers_country' => $Qcustomer->value('countries_name'),
                              'customers_telephone' => $Qcustomer->value('customers_telephone'),
                              'customers_email_address' => $Qcustomer->value('customers_email_address'),
                              'customers_address_format_id' => $Qcustomer->value('address_format_id'),
                              'delivery_name' => $OSCOM_Order->getShippingAddress('firstname') . ' ' . $OSCOM_Order->getShippingAddress('lastname'),
                              'delivery_company' => $OSCOM_Order->getShippingAddress('company'),
                              'delivery_street_address' => $OSCOM_Order->getShippingAddress('street_address'),
                              'delivery_suburb' => $OSCOM_Order->getShippingAddress('suburb'),
                              'delivery_city' => $OSCOM_Order->getShippingAddress('city'),
                              'delivery_postcode' => $OSCOM_Order->getShippingAddress('postcode'),
                              'delivery_state' => $OSCOM_Order->getShippingAddress('state'),
                              'delivery_country' => osc_get_country_name($OSCOM_Order->getShippingAddress('country_id')),
                              'delivery_address_format_id' => osc_get_address_format_id($OSCOM_Order->getShippingAddress('country_id')),
                              'billing_name' => $OSCOM_Order->getBillingAddress('firstname') . ' ' . $OSCOM_Order->getBillingAddress('lastname'),
                              'billing_company' => $OSCOM_Order->getBillingAddress('company'),
                              'billing_street_address' => $OSCOM_Order->getBillingAddress('street_address'),
                              'billing_suburb' => $OSCOM_Order->getBillingAddress('suburb'),
                              'billing_city' => $OSCOM_Order->getBillingAddress('city'),
                              'billing_postcode' => $OSCOM_Order->getBillingAddress('postcode'),
                              'billing_state' => $OSCOM_Order->getBillingAddress('state'),
                              'billing_country' => osc_get_country_name($OSCOM_Order->getBillingAddress('country_id')),
                              'billing_address_format_id' => osc_get_address_format_id($OSCOM_Order->getBillingAddress('country_id')),
                              'payment_method' => $OSCOM_Order->getBilling('title'),
                              'date_purchased' => 'now()',
                              'orders_status' => $OSCOM_Order->getInfo('order_status_id'),
                              'currency' => $_SESSION['currency'],
                              'currency_value' => $currencies->get_value($_SESSION['currency']));

      osc_db_perform(TABLE_ORDERS, $sql_data_array);

      $insert_id = osc_db_insert_id();

      foreach ( $OSCOM_Order->getTotals() as $t ) {
        $sql_data_array = array('orders_id' => $insert_id,
                                'title' => $t['title'],
                                'text' => $t['text'],
                                'value' => $t['value'],
                                'class' => $t['code'],
                                'sort_order' => $t['sort_order']);

        osc_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
      }

      $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => $OSCOM_Order->getInfo('order_status_id'),
                              'date_added' => 'now()',
                              'customer_notified' => $customer_notification,
                              'comments' => $OSCOM_Order->hasInfo('comments') ? $OSCOM_Order->getInfo('comments') : '');

      osc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

// initialized for the email confirmation
      $products_ordered = '';

      foreach ( $_SESSION['cart']->get_products() as $p ) {
        if (STOCK_LIMITED == 'true') {
          if (DOWNLOAD_ENABLED == 'true') {
            $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                FROM " . TABLE_PRODUCTS . " p
                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                ON p.products_id=pa.products_id
                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                ON pa.products_attributes_id=pad.products_attributes_id
                                WHERE p.products_id = '" . osc_get_prid($p['id']) . "'";

// Will work with only one option for downloadable products
// otherwise, we have to build the query dynamically with a loop
            $products_attributes = (isset($p['attributes'])) ? $p['attributes'] : '';

            if (is_array($products_attributes)) {
              $stock_query_raw .= " AND pa.options_id = '" . (int)$p['attributes'][0]['option_id'] . "' AND pa.options_values_id = '" . (int)$p['attributes'][0]['value_id'] . "'";
            }

            $stock_query = osc_db_query($stock_query_raw);
          } else {
            $stock_query = osc_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . osc_get_prid($p['id']) . "'");
          }

          if (osc_db_num_rows($stock_query) > 0) {
            $stock_values = osc_db_fetch_array($stock_query);

// do not decrement quantities if products_attributes_filename exists
            if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
              $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
            } else {
              $stock_left = $stock_values['products_quantity'];
            }

            osc_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . (int)$stock_left . "' where products_id = '" . osc_get_prid($p['id']) . "'");

            if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
              osc_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . osc_get_prid($p['id']) . "'");
            }
          }
        }

// Update products_ordered (for bestsellers list)
        osc_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $p['quantity']) . " where products_id = '" . osc_get_prid($p['id']) . "'");

        $sql_data_array = array('orders_id' => $insert_id,
                                'products_id' => osc_get_prid($p['id']),
                                'products_model' => $p['model'],
                                'products_name' => $p['name'],
                                'products_price' => $p['price'],
                                'final_price' => $p['final_price'],
                                'products_tax' => osc_get_tax_rate($p['tax_class_id'], $OSCOM_Order->getTaxAddress('country_id'), $OSCOM_Order->getTaxAddress('zone_id')),
                                'products_quantity' => $p['quantity']);

        osc_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);

        $order_products_id = osc_db_insert_id();

//------insert customer choosen option to order--------
        $attributes_exist = '0';
        $products_ordered_attributes = '';

        if (isset($p['attributes'])) {
          $attributes_exist = '1';

          foreach ( $p['attributes'] as $pa ) {
            if (DOWNLOAD_ENABLED == 'true') {
              $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                   from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                   left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                   on pa.products_attributes_id=pad.products_attributes_id
                                   where pa.products_id = '" . (int)$p['id'] . "'
                                   and pa.options_id = '" . (int)$pa['option_id'] . "'
                                   and pa.options_id = popt.products_options_id
                                   and pa.options_values_id = '" . (int)$pa['value_id'] . "'
                                   and pa.options_values_id = poval.products_options_values_id
                                   and popt.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                   and popt.language_id = poval.language_id";

              $attributes = osc_db_query($attributes_query);
            } else {
              $attributes = osc_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int)$p['id'] . "' and pa.options_id = '" . (int)$pa['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . (int)$pa['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int)$_SESSION['languages_id'] . "' and popt.language_id = poval.language_id");
            }

            $attributes_values = osc_db_fetch_array($attributes);

            $sql_data_array = array('orders_id' => $insert_id,
                                    'orders_products_id' => $order_products_id,
                                    'products_options' => $attributes_values['products_options_name'],
                                    'products_options_values' => $attributes_values['products_options_values_name'],
                                    'options_values_price' => $attributes_values['options_values_price'],
                                    'price_prefix' => $attributes_values['price_prefix']);

            osc_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

            if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && osc_not_null($attributes_values['products_attributes_filename'])) {
              $sql_data_array = array('orders_id' => $insert_id, 
                                      'orders_products_id' => $order_products_id,
                                      'orders_products_filename' => $attributes_values['products_attributes_filename'],
                                      'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                                      'download_count' => $attributes_values['products_attributes_maxcount']);

              osc_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
            }

            $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
          }
        }

//------insert customer choosen option eof ----
        $products_ordered .= $p['quantity'] . ' x ' . $p['name'] . ' (' . $p['model'] . ') = ' . $currencies->display_price($p['final_price'], osc_get_tax_rate($p['tax_class_id'], $OSCOM_Order->getTaxAddress('country_id'), $OSCOM_Order->getTaxAddress('zone_id')), $p['quantity']) . $products_ordered_attributes . "\n";
      }

// lets start with the email confirmation
      $email_order = STORE_NAME . "\n" .
                     EMAIL_SEPARATOR . "\n" .
                     EMAIL_TEXT_ORDER_NUMBER . ' ' . $insert_id . "\n" .
                     EMAIL_TEXT_INVOICE_URL . ' ' . osc_href_link('account', 'orders&id=' . $insert_id, 'SSL', false) . "\n" .
                     EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";

      if ( $OSCOM_Order->hasInfo('comments') ) {
        $email_order .= osc_db_output($OSCOM_Order->getInfo('comments')) . "\n\n";
      }

      $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      $products_ordered .
                      EMAIL_SEPARATOR . "\n";

      foreach ( $OSCOM_Order->getTotals() as $t ) {
        $email_order .= strip_tags($t['title']) . ' ' . strip_tags($t['text']) . "\n";
      }

      if ( $OSCOM_Order->requireShipping() ) {
        $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        osc_address_label($OSCOM_Customer->getID(), $OSCOM_Order->getShippingAddress(), 0, '', "\n") . "\n";
      }

      $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      osc_address_label($OSCOM_Customer->getID(), $OSCOM_Order->getBillingAddress(), 0, '', "\n") . "\n\n";

      $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      $OSCOM_Order->getBilling('title') . "\n\n";

      if ( isset($OSCOM_Payment->get()->email_footer) ) {
        $email_order .= $OSCOM_Payment->get()->email_footer . "\n\n";
      }

      osc_mail($Qcustomer->value('customers_firstname') . ' ' . $Qcustomer->value('customers_lastname'), $Qcustomer->value('customers_email_address'), EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

// send emails to other people
      if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
        osc_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      }

// load the after_process function from the payment modules
      $OSCOM_Payment->after_process();

      osc_redirect(osc_href_link('checkout', 'success', 'SSL'));
    }
  }
?>
