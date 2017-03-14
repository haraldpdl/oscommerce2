<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class oscTemplate {
    public $_title;
    public $_blocks = array();
    public $_content = array();
    public $_grid_container_width = 12;
    public $_grid_content_width = BOOTSTRAP_CONTENT;
    public $_grid_column_width = 0; // deprecated
    public $_data = array();

    public function __construct() {
      $this->_title = TITLE;
    }

    public function setGridContainerWidth($width) {
      $this->_grid_container_width = $width;
    }

    public function getGridContainerWidth() {
      return $this->_grid_container_width;
    }

    public function setGridContentWidth($width) {
      $this->_grid_content_width = $width;
    }

    public function getGridContentWidth() {
      return $this->_grid_content_width;
    }

    public function setGridColumnWidth($width) {
      $this->_grid_column_width = $width;
    }

    public function getGridColumnWidth() {
      return (12 - BOOTSTRAP_CONTENT) / 2;
    }

    public function setTitle($title) {
      $this->_title = $title;
    }

    public function getTitle() {
      return $this->_title;
    }

    public function addBlock($block, $group) {
      $this->_blocks[$group][] = $block;
    }

    public function hasBlocks($group) {
      return (isset($this->_blocks[$group]) && !empty($this->_blocks[$group]));
    }

    public function getBlocks($group) {
      if ($this->hasBlocks($group)) {
        return implode("\n", $this->_blocks[$group]);
      }
    }

    public function buildBlocks() {
      if ( defined('TEMPLATE_BLOCK_GROUPS') && tep_not_null(TEMPLATE_BLOCK_GROUPS) ) {
        $tbgroups_array = explode(';', TEMPLATE_BLOCK_GROUPS);

        foreach ($tbgroups_array as $group) {
          $module_key = 'MODULE_' . strtoupper($group) . '_INSTALLED';

          if ( defined($module_key) && tep_not_null(constant($module_key)) ) {
            $modules_array = explode(';', constant($module_key));

            foreach ( $modules_array as $module ) {
              $class = basename($module, '.php');

              if ( !class_exists($class) ) {
                if ( file_exists('includes/languages/' . $_SESSION['language'] . '/modules/' . $group . '/' . $module) ) {
                  include('includes/languages/' . $_SESSION['language'] . '/modules/' . $group . '/' . $module);
                }

                if ( file_exists('includes/modules/' . $group . '/' . $module) ) {
                  include('includes/modules/' . $group . '/' . $module);
                }
              }

              if ( class_exists($class) ) {
                $mb = new $class();

                if ( $mb->isEnabled() ) {
                  $mb->execute();
                }
              }
            }
          }
        }
      }
    }

    public function addContent($content, $group) {
      $this->_content[$group][] = $content;
    }

    public function hasContent($group) {
      return (isset($this->_content[$group]) && !empty($this->_content[$group]));
    }

    public function getContent($group) {
      if ( !class_exists('tp_' . $group) && file_exists('includes/modules/pages/tp_' . $group . '.php') ) {
        include('includes/modules/pages/tp_' . $group . '.php');
      }

      if ( class_exists('tp_' . $group) ) {
        $template_page_class = 'tp_' . $group;
        $template_page = new $template_page_class();
        $template_page->prepare();
      }

      foreach ( $this->getContentModules($group) as $module ) {
        if ( !class_exists($module) ) {
          if ( file_exists('includes/modules/content/' . $group . '/' . $module . '.php') ) {
            if ( file_exists('includes/languages/' . $_SESSION['language'] . '/modules/content/' . $group . '/' . $module . '.php') ) {
              include('includes/languages/' . $_SESSION['language'] . '/modules/content/' . $group . '/' . $module . '.php');
            }

            include('includes/modules/content/' . $group . '/' . $module . '.php');
          }
        }

        if ( class_exists($module) ) {
          $mb = new $module();

          if ( $mb->isEnabled() ) {
            $mb->execute();
          }
        }
      }

      if ( class_exists('tp_' . $group) ) {
        $template_page->build();
      }

      if ($this->hasContent($group)) {
        return implode("\n", $this->_content[$group]);
      }
    }

    public function getContentModules($group) {
      $result = array();

      foreach ( explode(';', MODULE_CONTENT_INSTALLED) as $m ) {
        $module = explode('/', $m, 2);

        if ( $module[0] == $group ) {
          $result[] = $module[1];
        }
      }

      return $result;
    }
  }
