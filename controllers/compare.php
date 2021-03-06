<?php
defined('ALTUMCODE') || die();

$source = isset($parameters[0]) && in_array($parameters[0], $sources) ? Database::clean_string($parameters[0]) : reset($sources);
$user_one = isset($parameters[1]) ? Database::clean_string($parameters[1]) : false;
$user_two = isset($parameters[2]) ? Database::clean_string($parameters[2]) : false;

$table = $source . '_users';
$column = $source != 'youtube' ? 'username' : 'youtube_id';

/* We need to check if the user already exists in our database */
switch($source) {
    case 'youtube':

        $source_account_one = $database->query("SELECT * FROM `youtube_users` WHERE `youtube_id` = '{$user_one}' OR `username` = '{$user_one}' LIMIT 1")->fetch_object() ?? false;
        $source_account_two = $database->query("SELECT * FROM `youtube_users` WHERE `youtube_id` = '{$user_two}' OR `username` = '{$user_two}' LIMIT 1")->fetch_object() ?? false;

        break;

    default:
        $source_account_one = $user_one ? Database::get('*', $table, ['username' => $user_one]) : false;
        $source_account_two = $user_two ? Database::get('*', $table, ['username' => $user_two]) : false;

}


/* Check if the searched accounts are existing to the database */
if($user_one && !$source_account_one) {
    $_SESSION['info'][] = sprintf($language->compare->info_message->user_not_found, $user_one, '<a href="' . url('report/' . $user_one . '/' . $source) . '">' . $user_one . '</a>');
}

if($user_two && !$source_account_two) {
    $_SESSION['info'][] = sprintf($language->compare->info_message->user_not_found, $user_two, '<a href="' . url('report/' . $user_two . '/' . $source) . '">' . $user_two . '</a>');
}



/* Make sure the user has at least one report purchased if needed */
$access = true;
/*
if(
    $user_one &&
    $user_two &&
    $source_account_one &&
    $source_account_two &&

    (
        $settings->store_unlock_report_price == '0' ||

        (
            $settings->store_unlock_report_price != '0' && User::logged_in() &&

            (
                User::has_valid_report($source_account_one->id) ||
                User::has_valid_report($source_account_two->id) ||
                ($source_account_one->is_demo && $source_account_two->is_demo) ||
                $account->type
            )
        )
    )
) {
    $access = true;
} else {
    $access = false;

    if(!User::logged_in() && $settings->store_unlock_report_price != '0') {
        $_SESSION['error'][] = $language->compare->error_message->no_access;
    } else if($user_one && $user_two) {
        $_SESSION['error'][] = $language->compare->error_message->no_access_purchase;
    }
}
*/

if($user_one && $source_account_one && $user_two && $source_account_two) {
    $user_one = $source_account_one->username;
    $user_two = $source_account_two->username;

    if($plugins->exists_and_active($source)) {
        require_once $plugins->require($source, 'controllers/compare');
    }
}

/* Insert the chartjs library */
add_event('head', function() {
    global $settings;

    echo '<script src="' . $settings->url . ASSETS_ROUTE . 'js/Chart.bundle.min.js"></script>';
});

/* Custom title */
add_event('title', function() {
    global $page_title;
    global $language;

    $page_title = $language->compare->title;
});
