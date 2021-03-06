<?php
defined('ALTUMCODE') || die();

/* Initiation */
set_time_limit(0);

/* *****************/
/* Unlocked reports validity */
/* *****************/

/* Make sure to clean unlocked reports which are expired */
$database->query("DELETE FROM `unlocked_reports` WHERE `expiration_date` < NOW() AND `expiration_date` <> 0");


/* *****************/
/* Email reporting */
/* *****************/

if($settings->email_reports) {

    switch ($settings->email_reports_frequency) {
        case 'DAILY':
            $timestampdiff = 'DAY';
            $timestampdiff_compare = '0';
            break;

        case 'WEEKLY':
            $timestampdiff = 'DAY';
            $timestampdiff_compare = '6';

            break;

        case 'MONTHLY':
            $timestampdiff = 'MONTH';
            $timestampdiff_compare = '0';

            break;
    }

    /* Include other sources email cron */
    foreach($plugins->plugins as $plugin_identifier => $value) {
        if($plugins->exists_and_active($plugin_identifier)) {
            require_once $plugins->require($plugin_identifier, 'controllers/cron_emails');
        }
    }
}

/* **********************/
/* Accounts Checking    */
/* **********************/
$is_proxy_request = select_proxy($database, $settings);

/* Include other sources accounts cron */
foreach($plugins->plugins as $plugin_identifier => $value) {
    if($plugins->exists_and_active($plugin_identifier)) {
        require_once $plugins->require($plugin_identifier, 'controllers/cron_reports');
    }
}

$controller_has_view = false;
