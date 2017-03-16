<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_EC_Cfg_order_status_id {
    public $default = '0';
    public $title;
    public $description;
    public $sort_order = 800;

    public function __construct() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_ec_order_status_id_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_ec_order_status_id_desc');
    }

    public function getSetField() {
      global $OSCOM_PayPal;

      $statuses_array = array(array('id' => '0', 'text' => $OSCOM_PayPal->getDef('cfg_ec_order_status_id_default')));

      $statuses_query = tep_db_query("select orders_status_id, orders_status_name from :table_orders_status where language_id = '" . (int)$_SESSION['languages_id'] . "' order by orders_status_name");
      while ($statuses = tep_db_fetch_array($statuses_query)) {
        $statuses_array[] = array('id' => $statuses['orders_status_id'],
                                  'text' => $statuses['orders_status_name']);
      }

      $input = tep_draw_pull_down_menu('order_status_id', $statuses_array, OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID, 'id="inputEcOrderStatusId"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputEcOrderStatusId">{$this->title}</label>

    {$this->description}
  </p>

  <div>
    {$input}
  </div>
</div>
EOT;

      return $result;
    }
  }
