<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class objectInfo {

// class constructor
    public function __construct(array $object_array) {
      $this->apply($object_array);
    }

    public function apply(array $object_array) {
      foreach ($object_array as $key => $value) {
        $this->$key = tep_db_prepare_input($value);
      }
    }
  }
