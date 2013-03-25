<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class usps extends shipping_abstract {
    protected $_types;
    protected $_countries;
    protected $_machinable = 'True';
    protected $_container = 'None';
    protected $_size = 'Regular';
    protected $_pounds;
    protected $_ounces;
    protected $_service = 'All';

    protected function initialize() {
      global $OSCOM_PDO;

      $this->_title = MODULE_SHIPPING_USPS_TITLE;
      $this->_description = MODULE_SHIPPING_USPS_DESCRIPTION;
      $this->_icon = DIR_WS_ICONS . 'shipping_usps.gif';
      $this->_installed = defined('MODULE_SHIPPING_USPS_STATUS');

      if ( isset($this->_order) ) {
        $this->_enabled = (MODULE_SHIPPING_USPS_STATUS == 'True') ? true : false;
        $this->_sort_order = MODULE_SHIPPING_USPS_SORT_ORDER;
        $this->_tax_class_id = MODULE_SHIPPING_USPS_TAX_CLASS;
        $this->_shipping_zone_class_id = MODULE_SHIPPING_USPS_ZONE;

        if ( $this->isEnabled() && !$this->hasValidShippingZone() ) {
          $this->_enabled = false;
        }

        $this->_types = array(
// Domestic Types
          'Express Mail',
          'Express Mail Flat Rate Envelope',
          'Priority Mail',
          'Priority Mail Flat Rate Envelope',
          'Priority Mail Small Flat Rate Box',
          'Priority Mail Medium Flat Rate Box',
          'Priority Mail Large Flat Rate Box',
          'First-Class Mail Flat',
          'First-Class Mail Parcel',
          'Parcel Post',
          'Bound Printed Matter',
          'Media Mail',
          'Library Mail',
// International Types
          'Global Express Guaranteed (GXG)',
          'Global Express Guaranteed Non-Document Rectangular',
          'Global Express Guaranteed Non-Document Non-Rectangular',
          'USPS GXG Envelopes',
          'Express Mail International',
          'Express Mail International Flat Rate Envelope',
          'Priority Mail International',
          'Priority Mail International Large Flat Rate Box',
          'Priority Mail International Medium Flat Rate Box',
          'Priority Mail International Small Flat Rate Box',
          'Priority Mail International Flat Rate Envelope',
          'First-Class Mail International Package',
          'First-Class Mail International Large Envelope'
        );

        $this->_countries = $this->getCountries();
      }
    }

// class methods
    public function getQuote() {
      $shipping_weight = $_SESSION['cart']->show_weight();

// USPS doesnt accept zero weight
      if ( $shipping_weight < 0.1 ) {
        $shipping_weight = 0.1;
      }

      $shipping_pounds = floor($shipping_weight);
      $shipping_ounces = round(16 * ($shipping_weight - floor($shipping_weight)));

      $this->setWeight($shipping_pounds, $shipping_ounces);

      $shipping_boxes = 1;

      if ( $shipping_weight > SHIPPING_MAX_WEIGHT ) {
        $shipping_boxes = ceil($shipping_weight / SHIPPING_MAX_WEIGHT);
      }

      $uspsQuote = $this->getQuoteFromUSPS();

      if ( is_array($uspsQuote) ) {
        if ( isset($uspsQuote['error']) ) {
          $data = array('error' => $uspsQuote['error']);
        } else {
          $data = array();
          $size = count($uspsQuote);
          for ($i=0; $i<$size; $i++) {
            list($type, $cost) = each($uspsQuote[$i]);

// echo "USPS $type @ $cost<br />";
            if ( ($method == '' && in_array($type, $this->_types)) || $method == $type ) {
              $data[] = array('id' => $type,
                              'title' => $type,
                              'cost' => ($cost + MODULE_SHIPPING_USPS_HANDLING) * $shipping_boxes);
            }
          }
        }
      } else {
        $data = array('error' => MODULE_SHIPPING_USPS_ERROR);
      }

      return $data;
    }

    protected function getParams() {
      $params = array('MODULE_SHIPPING_USPS_STATUS' => array('title' => 'Enable USPS Shipping',
                                                             'desc' => 'Do you want to offer USPS shipping?',
                                                             'value' => 'True',
                                                             'set_func' => 'osc_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_SHIPPING_USPS_USERID' => array('title' => 'USPS User ID',
                                                           'desc' => 'Enter the USPS USERID assigned to you.',
                                                           'value' => ''),
                      'MODULE_SHIPPING_USPS_PASSWORD' => array('title' => 'USPS Password',
                                                             'desc' => 'Enter the USPS Password assigned to you.',
                                                             'value' => ''),
                     'MODULE_SHIPPING_USPS_HANDLING' => array('title' => 'Handling Fee',
                                                              'desc' => 'Handling fee for this shipping method.',
                                                              'value' => '0'),
                      'MODULE_SHIPPING_USPS_TAX_CLASS' => array('title' => 'Tax Class',
                                                                'desc' => 'Use the following tax class on the shipping cost.',
                                                                'value' => '0',
                                                                'use_func' => 'osc_get_tax_class_title',
                                                                'set_func' => 'osc_cfg_pull_down_tax_classes('),
                      'MODULE_SHIPPING_USPS_ZONE' => array('title' => 'Shipping Zone',
                                                           'desc' => 'If a zone is selected, only enable this shipping method for that zone.',
                                                           'value' => '0',
                                                           'use_func' => 'osc_get_zone_class_title',
                                                           'set_func' => 'osc_cfg_pull_down_zone_classes('),
                      'MODULE_SHIPPING_USPS_SORT_ORDER' => array('title' => 'Sort Order',
                                                                 'desc' => 'Sort order of display.',
                                                                 'value' => '0'));

      return $params;
    }

    public function setService($service) {
      $this->_service = $service;
    }

    public function setWeight($pounds, $ounces = 0) {
      $this->_pounds = $pounds;
      $this->_ounces = $ounces;
    }

    public function setContainer($container) {
      $this->_container = $container;
    }

    public function setSize($size) {
      $this->_size = $size;
    }

    public function setMachinable($machinable) {
      $this->_machinable = $machinable;
    }

    protected function getQuoteFromUSPS() {
      $country = osc_get_countries_with_iso_codes($this->_order->getShippingAddress('country_id'));

      if ( $this->_order->getShippingAddress('country_id') == SHIPPING_ORIGIN_COUNTRY ) {
        $dest_zip = str_replace(' ', '', $this->_order->getShippingAddress('postcode'));

        if ( ($country['countries_iso_code_2'] == 'US') && (strlen($dest_zip) > 5) ) {
          $dest_zip = substr($dest_zip, 0, 5);
        }

	      $request = '<RateV3Request USERID="' . MODULE_SHIPPING_USPS_USERID . '">' .
	                 '  <Package ID="0">' .
	                 '    <Service>' . $this->_service . '</Service>' .
                   '    <ZipOrigination>' . SHIPPING_ORIGIN_ZIP . '</ZipOrigination>' .
                   '    <ZipDestination>' . $dest_zip . '</ZipDestination>' .
	                 '    <Pounds>' . $this->_pounds . '</Pounds>' .
	                 '    <Ounces>' . $this->_ounces . '</Ounces>' .
	                 '    <Container>' . $this->_container . '</Container>' .
                   '    <Size>' . $this->_size . '</Size>' .
                   '    <Machinable>' . $this->_machinable . '</Machinable>' .
	                 '  </Package>' .
                   '</RateV3Request>';

        $request = 'API=RateV3&XML=' . urlencode($request);
      } else {
        $request  = '<IntlRateRequest USERID="' . MODULE_SHIPPING_USPS_USERID . '">' .
                    '  <Package ID="0">' .
                    '    <Pounds>' . $this->_pounds . '</Pounds>' .
                    '    <Ounces>' . $this->_ounces . '</Ounces>' .
                    '    <MailType>Package</MailType>' .
		                '    <GXG>' .
		                '      <Length>12</Length>' .
                    '      <Width>12</Width>' .
                    '      <Height>12</Height>' .
		                '      <POBoxFlag>N</POBoxFlag>' .
                    '      <GiftFlag>N</GiftFlag>' .
		                '    </GXG>' .
		                '    <ValueOfContents>50</ValueOfContents>' .
                    '    <Country>' . $this->_countries[$country['countries_iso_code_2']] . '</Country>' .
                    '  </Package>' .
                    '</IntlRateRequest>';

        $request = 'API=IntlRate&XML=' . urlencode($request);
      }

      $body = '';

      if ( !class_exists('httpClient') ) {
        include('includes/classes/http_client.php');
      }

      $http = new httpClient();

      if ( $http->Connect('production.shippingapis.com', 80) ) {
        $http->addHeader('Host', 'production.shippingapis.com');
        $http->addHeader('User-Agent', 'osCommerce');
        $http->addHeader('Connection', 'Close');

        if ( $http->Get('/shippingapi.dll?' . $request) ) {
          $body = $http->getBody();
        }

        $http->Disconnect();
      } else {
        return false;
      }

      $response = array();

      while ( true ) {
        if ( $start = strpos($body, '<Package ID=') ) {
          $body = substr($body, $start);
          $end = strpos($body, '</Package>');
          $response[] = substr($body, 0, $end+10);
          $body = substr($body, $end+9);
        } else {
          break;
        }
      }

      $rates = array();

      if ( $this->_order->getShippingAddress('country_id') == SHIPPING_ORIGIN_COUNTRY ) {
        if ( count($response) == '1' ) {
          if ( preg_match('/<Error>/', $response[0]) ) {
            $number = preg_match('/<Number>(.*)<\/Number>/', $response[0], $regs);
            $number = $regs[1];

            $description = preg_match('/<Description>(.*)<\/Description>/', $response[0], $regs);
            $description = $regs[1];

            return array('error' => $number . ' - ' . $description);
          }
        }

        $n = count($response);
        for ($i=0; $i<$n; $i++) {
          $resp = $response[$i];
          $pos = 0;

          while (1) {
            $pos = strpos($response[$i], '<Postage', $pos);

            if ( $pos === false ) {
              break;
            }

            $end = strpos($response[$i], '</Postage>', $pos);

            if ( $end === false ) {
              break;
            }

            $resp = substr($response[$i], $pos, $end-$pos);

            $service = preg_match('/<MailService>(.*)<\/MailService>/', $resp, $regs);
            $service = $regs[1];

            $postage = preg_match('/<Rate>(.*)<\/Rate>/', $resp, $regs);
            $postage = $regs[1];

            $pos = $end;

            $rates[] = array($service => $postage);
          }
        }
      } else {
        if ( preg_match('/<Error>/', $response[0]) ) {
          $number = preg_match('/<Number>(.*)<\/Number>/', $response[0], $regs);
          $number = $regs[1];

          $description = preg_match('/<Description>(.*)<\/Description>/', $response[0], $regs);
          $description = $regs[1];

          return array('error' => $number . ' - ' . $description);
        } else {
          $body = $response[0];
          $services = array();

          while ( true ) {
            if ( $start = strpos($body, '<Service ID=') ) {
              $body = substr($body, $start);
              $end = strpos($body, '</Service>');
              $services[] = substr($body, 0, $end+10);
              $body = substr($body, $end+9);
            } else {
              break;
            }
          }

          $size = count($services);
          for ($i=0, $n=$size; $i<$n; $i++) {
            if ( strpos($services[$i], '<Postage>') ) {
              $service = preg_match('/<SvcDescription>(.*)<\/SvcDescription>/', $services[$i], $regs);
              $service = $regs[1];

              $postage = preg_match('/<Postage>(.*)<\/Postage>/', $services[$i], $regs);
              $postage = $regs[1];

              if ( isset($this->_service) && ($service != $this->_service) ) {
                continue;
              }

              $rates[] = array($service => $postage);
            }
          }
        }
      }

      return ((count($rates) > 0) ? $rates : false);
    }

    protected function getCountries() {
      $list = array('AF' => 'Afghanistan',
                    'AL' => 'Albania',
                    'DZ' => 'Algeria',
                    'AD' => 'Andorra',
                    'AO' => 'Angola',
                    'AI' => 'Anguilla',
                    'AG' => 'Antigua and Barbuda',
                    'AR' => 'Argentina',
                    'AM' => 'Armenia',
                    'AW' => 'Aruba',
                    'AU' => 'Australia',
                    'AT' => 'Austria',
                    'AZ' => 'Azerbaijan',
                    'BS' => 'Bahamas',
                    'BH' => 'Bahrain',
                    'BD' => 'Bangladesh',
                    'BB' => 'Barbados',
                    'BY' => 'Belarus',
                    'BE' => 'Belgium',
                    'BZ' => 'Belize',
                    'BJ' => 'Benin',
                    'BM' => 'Bermuda',
                    'BT' => 'Bhutan',
                    'BO' => 'Bolivia',
                    'BA' => 'Bosnia-Herzegovina',
                    'BW' => 'Botswana',
                    'BR' => 'Brazil',
                    'VG' => 'British Virgin Islands',
                    'BN' => 'Brunei Darussalam',
                    'BG' => 'Bulgaria',
                    'BF' => 'Burkina Faso',
                    'MM' => 'Burma',
                    'BI' => 'Burundi',
                    'KH' => 'Cambodia',
                    'CM' => 'Cameroon',
                    'CA' => 'Canada',
                    'CV' => 'Cape Verde',
                    'KY' => 'Cayman Islands',
                    'CF' => 'Central African Republic',
                    'TD' => 'Chad',
                    'CL' => 'Chile',
                    'CN' => 'China',
                    'CX' => 'Christmas Island (Australia)',
                    'CC' => 'Cocos Island (Australia)',
                    'CO' => 'Colombia',
                    'KM' => 'Comoros',
                    'CG' => 'Congo (Brazzaville),Republic of the',
                    'ZR' => 'Congo, Democratic Republic of the',
                    'CK' => 'Cook Islands (New Zealand)',
                    'CR' => 'Costa Rica',
                    'CI' => 'Cote d\'Ivoire (Ivory Coast)',
                    'HR' => 'Croatia',
                    'CU' => 'Cuba',
                    'CY' => 'Cyprus',
                    'CZ' => 'Czech Republic',
                    'DK' => 'Denmark',
                    'DJ' => 'Djibouti',
                    'DM' => 'Dominica',
                    'DO' => 'Dominican Republic',
                    'TP' => 'East Timor (Indonesia)',
                    'EC' => 'Ecuador',
                    'EG' => 'Egypt',
                    'SV' => 'El Salvador',
                    'GQ' => 'Equatorial Guinea',
                    'ER' => 'Eritrea',
                    'EE' => 'Estonia',
                    'ET' => 'Ethiopia',
                    'FK' => 'Falkland Islands',
                    'FO' => 'Faroe Islands',
                    'FJ' => 'Fiji',
                    'FI' => 'Finland',
                    'FR' => 'France',
                    'GF' => 'French Guiana',
                    'PF' => 'French Polynesia',
                    'GA' => 'Gabon',
                    'GM' => 'Gambia',
                    'GE' => 'Georgia, Republic of',
                    'DE' => 'Germany',
                    'GH' => 'Ghana',
                    'GI' => 'Gibraltar',
                    'GB' => 'Great Britain and Northern Ireland',
                    'GR' => 'Greece',
                    'GL' => 'Greenland',
                    'GD' => 'Grenada',
                    'GP' => 'Guadeloupe',
                    'GT' => 'Guatemala',
                    'GN' => 'Guinea',
                    'GW' => 'Guinea-Bissau',
                    'GY' => 'Guyana',
                    'HT' => 'Haiti',
                    'HN' => 'Honduras',
                    'HK' => 'Hong Kong',
                    'HU' => 'Hungary',
                    'IS' => 'Iceland',
                    'IN' => 'India',
                    'ID' => 'Indonesia',
                    'IR' => 'Iran',
                    'IQ' => 'Iraq',
                    'IE' => 'Ireland',
                    'IL' => 'Israel',
                    'IT' => 'Italy',
                    'JM' => 'Jamaica',
                    'JP' => 'Japan',
                    'JO' => 'Jordan',
                    'KZ' => 'Kazakhstan',
                    'KE' => 'Kenya',
                    'KI' => 'Kiribati',
                    'KW' => 'Kuwait',
                    'KG' => 'Kyrgyzstan',
                    'LA' => 'Laos',
                    'LV' => 'Latvia',
                    'LB' => 'Lebanon',
                    'LS' => 'Lesotho',
                    'LR' => 'Liberia',
                    'LY' => 'Libya',
                    'LI' => 'Liechtenstein',
                    'LT' => 'Lithuania',
                    'LU' => 'Luxembourg',
                    'MO' => 'Macao',
                    'MK' => 'Macedonia, Republic of',
                    'MG' => 'Madagascar',
                    'MW' => 'Malawi',
                    'MY' => 'Malaysia',
                    'MV' => 'Maldives',
                    'ML' => 'Mali',
                    'MT' => 'Malta',
                    'MQ' => 'Martinique',
                    'MR' => 'Mauritania',
                    'MU' => 'Mauritius',
                    'YT' => 'Mayotte (France)',
                    'MX' => 'Mexico',
                    'MD' => 'Moldova',
                    'MC' => 'Monaco (France)',
                    'MN' => 'Mongolia',
                    'MS' => 'Montserrat',
                    'MA' => 'Morocco',
                    'MZ' => 'Mozambique',
                    'NA' => 'Namibia',
                    'NR' => 'Nauru',
                    'NP' => 'Nepal',
                    'NL' => 'Netherlands',
                    'AN' => 'Netherlands Antilles',
                    'NC' => 'New Caledonia',
                    'NZ' => 'New Zealand',
                    'NI' => 'Nicaragua',
                    'NE' => 'Niger',
                    'NG' => 'Nigeria',
                    'KP' => 'North Korea (Korea, Democratic People\'s Republic of)',
                    'NO' => 'Norway',
                    'OM' => 'Oman',
                    'PK' => 'Pakistan',
                    'PA' => 'Panama',
                    'PG' => 'Papua New Guinea',
                    'PY' => 'Paraguay',
                    'PE' => 'Peru',
                    'PH' => 'Philippines',
                    'PN' => 'Pitcairn Island',
                    'PL' => 'Poland',
                    'PT' => 'Portugal',
                    'QA' => 'Qatar',
                    'RE' => 'Reunion',
                    'RO' => 'Romania',
                    'RU' => 'Russia',
                    'RW' => 'Rwanda',
                    'SH' => 'Saint Helena',
                    'KN' => 'Saint Kitts (St. Christopher and Nevis)',
                    'LC' => 'Saint Lucia',
                    'PM' => 'Saint Pierre and Miquelon',
                    'VC' => 'Saint Vincent and the Grenadines',
                    'SM' => 'San Marino',
                    'ST' => 'Sao Tome and Principe',
                    'SA' => 'Saudi Arabia',
                    'SN' => 'Senegal',
                    'YU' => 'Serbia-Montenegro',
                    'SC' => 'Seychelles',
                    'SL' => 'Sierra Leone',
                    'SG' => 'Singapore',
                    'SK' => 'Slovak Republic',
                    'SI' => 'Slovenia',
                    'SB' => 'Solomon Islands',
                    'SO' => 'Somalia',
                    'ZA' => 'South Africa',
                    'GS' => 'South Georgia (Falkland Islands)',
                    'KR' => 'South Korea (Korea, Republic of)',
                    'ES' => 'Spain',
                    'LK' => 'Sri Lanka',
                    'SD' => 'Sudan',
                    'SR' => 'Suriname',
                    'SZ' => 'Swaziland',
                    'SE' => 'Sweden',
                    'CH' => 'Switzerland',
                    'SY' => 'Syrian Arab Republic',
                    'TW' => 'Taiwan',
                    'TJ' => 'Tajikistan',
                    'TZ' => 'Tanzania',
                    'TH' => 'Thailand',
                    'TG' => 'Togo',
                    'TK' => 'Tokelau (Union) Group (Western Samoa)',
                    'TO' => 'Tonga',
                    'TT' => 'Trinidad and Tobago',
                    'TN' => 'Tunisia',
                    'TR' => 'Turkey',
                    'TM' => 'Turkmenistan',
                    'TC' => 'Turks and Caicos Islands',
                    'TV' => 'Tuvalu',
                    'UG' => 'Uganda',
                    'UA' => 'Ukraine',
                    'AE' => 'United Arab Emirates',
                    'UY' => 'Uruguay',
                    'UZ' => 'Uzbekistan',
                    'VU' => 'Vanuatu',
                    'VA' => 'Vatican City',
                    'VE' => 'Venezuela',
                    'VN' => 'Vietnam',
                    'WF' => 'Wallis and Futuna Islands',
                    'WS' => 'Western Samoa',
                    'YE' => 'Yemen',
                    'ZM' => 'Zambia',
                    'ZW' => 'Zimbabwe');

      return $list;
    }
  }
?>
