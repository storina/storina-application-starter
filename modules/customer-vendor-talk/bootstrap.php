<?php

/**
 * Description: Live talk with vendor and customer
 * Text Domain: CRN
 */

if (!defined('ABSPATH')) {
    exit;
}

define("CRN_FILE", STORINA_FILE);
define("CRN_PDU", trailingslashit(STORINA_PDU) . 'modules/vendor-talk');
define("CRN_PDP", trailingslashit(STORINA_PDP) . 'modules/vendor-talk');
define("CRN_TMP", trailingslashit(VOS_PDP) . "public");
define("CRN_ADM", trailingslashit(VOS_PDP) . "admin");

global $crn_init;
require_once trailingslashit(__DIR__) . "includes/Init.php";
