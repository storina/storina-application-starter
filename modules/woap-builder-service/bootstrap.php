<?php

/*
 * Plugin Name: Plugin Boilerplate
 * Description: Plugin boilerplate with api router
 * Version: 1.0
 * Author: Abolfazl Sabagh
 * Author URI: http://asabagh.ir
 * Text Domain: WOAP
 */

if (!defined('ABSPATH')) {
    exit;
}

define("WOAB_PRU", plugin_basename(__FILE__));
define("WOAB_PRT", basename(__DIR__));
define("WOAB_PDU", trailingslashit(WOAP_PDU) . 'modules/woap-builder-service');
define("WOAB_PDP", trailingslashit(WOAP_PDP) . 'modules/woap-builder-service');
define("WOAB_TMP", trailingslashit(WOAB_PDP) . "public/");
define("WOAB_ADM", trailingslashit(WOAB_PDP) . "admin/");

require_once trailingslashit(__DIR__) . "includes/Init.php";
