<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $current_version = tep_get_version();

  $releases = null;
  $new_versions = array();
  $check_message = array();

  if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.oscommerce.com/index.php?RPC&GetReleases&v=' . str_replace('.', '_', $current_version));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if ( file_exists(DIR_FS_CATALOG . 'includes/cacert.pem') ) {
      curl_setopt($ch, CURLOPT_CAINFO, DIR_FS_CATALOG . 'includes/cacert.pem');
    }

    $response = trim(curl_exec($ch));
    curl_close($ch);
  } else {
    if ($fp = @fsockopen('ssl://www.oscommerce.com', 443, $errno, $errstr, 30)) {
      $header = 'GET /index.php?RPC&GetReleases&v=' . str_replace('.', '_', $current_version) . ' HTTP/1.0' . "\r\n" .
                'Host: www.oscommerce.com' . "\r\n" .
                'Connection: close' . "\r\n\r\n";

      fwrite($fp, $header);

      $res = '';
      while (!feof($fp)) {
        $res .= fgets($fp, 1024);
      }

      fclose($fp);

      $res = explode("\r\n\r\n", $res); // split header and content

      if (isset($res[1]) && !empty($res[1])) {
        $response = $res[1];
      }
    }
  }

  if (isset($response) && !empty($response)) {
    $response = @json_decode($response, true);

    if (json_last_error() === JSON_ERROR_NONE) {
      $releases = $response;
    }
  }

  if (is_array($releases) && !empty($releases)) {
    $serialized = serialize($releases);
    if ($f = @fopen(DIR_FS_CACHE . 'oscommerce_version_check.cache', 'w')) {
      fwrite ($f, $serialized, strlen($serialized));
      fclose($f);
    }

    foreach ($releases as $release) {
      if (version_compare($release['version'], $current_version, '>')) {
        $new_versions[] = $release;
      }
    }

    if (!empty($new_versions)) {
      $check_message = array('class' => 'secWarning',
                             'message' => sprintf(VERSION_UPGRADES_AVAILABLE, tep_output_string_protected($new_versions[0]['version'])));
    } else {
      $check_message = array('class' => 'secSuccess',
                             'message' => VERSION_RUNNING_LATEST);
    }
  } else {
    $check_message = array('class' => 'secError',
                           'message' => ERROR_COULD_NOT_CONNECT);
  }

  require('includes/template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="smallText"><?php echo TITLE_INSTALLED_VERSION . ' <strong>osCommerce Online Merchant v' . $current_version . '</strong>'; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><div class="<?php echo $check_message['class']; ?>">
          <p class="smallText"><?php echo $check_message['message']; ?></p>
        </div></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
  if (!empty($new_versions)) {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_VERSION; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_RELEASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>

<?php
    foreach ($new_versions as $version) {
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                <td class="dataTableContent"><?php echo '<a href="' . tep_output_string_protected($version['news_link']) . '" target="_blank">osCommerce Online Merchant v' . tep_output_string_protected($version['version']) . '</a>'; ?></td>
                <td class="dataTableContent"><?php echo tep_output_string_protected(tep_date_long($version['date'])); ?></td>
                <td class="dataTableContent" align="right"><?php echo '<a href="' . tep_output_string_protected($version['news_link']) . '" target="_blank">' . tep_image('images/icon_info.gif', IMAGE_ICON_INFO) . '</a>'; ?>&nbsp;</td>
              </tr>
<?php
    }
?>
            </table></rd>
          </tr>
        </table></td>
      </tr>
<?php
  }
?>
    </table>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
