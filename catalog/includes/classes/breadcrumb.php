<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class breadcrumb {
    public $_trail;

    public function __construct() {
      $this->reset();
    }

    public function reset() {
      $this->_trail = array();
    }

    public function add($title, $link = '') {
      $this->_trail[] = array('title' => $title, 'link' => $link);
    }

    public function trail($separator = NULL) {
      $pos = 1;
      $trail_string = '<ol  itemscope itemtype="http://schema.org/BreadcrumbList" class="breadcrumb">';

      for ($i=0, $n=sizeof($this->_trail); $i<$n; $i++) {
        if (isset($this->_trail[$i]['link']) && tep_not_null($this->_trail[$i]['link'])) {
          $trail_string .= '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . $this->_trail[$i]['link'] . '" itemprop="item"><span itemprop="name">' . $this->_trail[$i]['title'] . '</span></a>';
        } else {
          $trail_string .= '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span itemprop="name">' . $this->_trail[$i]['title'] . '</span>';
        }
        $trail_string .= '<meta itemprop="position" content="' . (int)$pos . '" /></li>' . PHP_EOL;
        $pos++;
      }

      $trail_string .= '</ol>';

      return $trail_string;
    }
  }
