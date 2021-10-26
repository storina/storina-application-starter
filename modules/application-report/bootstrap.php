<?php

/*
 * Description: new extension for application showing online users and product view count and search expression
 * Text Domain: VOS
 */

if (!defined('ABSPATH')) {
    exit;
}

define("VOS_FILE", STORINA_FILE);
define("VOS_PDU", trailingslashit(WOAP_PDU) . 'modules/application-report');
define("VOS_PDP", trailingslashit(WOAP_PDP) . 'modules/application-report');
define("VOS_TMP", trailingslashit(VOS_PDP) . "public");
define("VOS_ADM", trailingslashit(VOS_PDP) . "admin");

require_once trailingslashit(__DIR__) . "includes/Init.php";
