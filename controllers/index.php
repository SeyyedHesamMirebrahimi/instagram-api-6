<?php

defined('ALTUMCODE') || die();

$controller_has_container = false;

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $query = $_POST['query'];
    $_SESSION['queryy'] = $query;
    $instagram = new \InstagramScraper\Instagram();
//	$instagram = $instagram->withCredentials('mubahsar__mehmood','NEWKM@power1122');
    $instagram->setUserAgent(get_random_user_agent());

    $is_proxy_request = false;

    // print_r($date);die;
    /* Check if we need to use a proxy */

    if($settings->proxy) {

        /* Select a proxy from the database */
        $proxy = $database->query("
            SELECT *
            FROM `proxies`
            WHERE
                (`failed_requests` < {$settings->proxy_failed_requests_pause})
                OR
                (`failed_requests` >= {$settings->proxy_failed_requests_pause} AND '{$date}' > DATE_ADD(`last_date`, INTERVAL {$settings->proxy_pause_duration} MINUTE))
            ORDER BY `last_date` ASC
        ");

        // print_r($proxy);die;

        if($proxy->num_rows) {

            $proxy = $proxy->fetch_object();

            $rand = rand(1, 10);

            /* Give it a 50 - 50 percent chance to choose from the server or from the proxy in case the proxy is not exclusive */
            if($settings->proxy_exclusive || (!$settings->proxy_exclusive && $rand > 5)) {

                $is_proxy_request = [
                    'address' => $proxy->address,
                    'port'    => $proxy->port,
                    'tunnel'  => true,
                    'timeout' => $settings->proxy_timeout,
                    'auth'    => [
                        'user' => $proxy->username,
                        'pass' => $proxy->password,
                        'method' => $proxy->method
                    ]
                ];

            }

        }

    }

    if($is_proxy_request) {
        $instagram::setProxy($is_proxy_request);
        //$GLOBALS['proxy'] = $is_proxy_request;
    }

    $search_results = $instagram->generalSearch($query);
}

    
/* Include the aos library */
add_event('head', function() {
    echo '<link href="assets/css/aos.min.css" rel="stylesheet" media="screen">';
});

add_event('footer', function() {
    echo '<script src="assets/js/aos.min.js"></script>';

    echo <<<ALTUM
<script>
    $(document).ready(() => {
        AOS.init({
            delay: 50,
            duration: 600
        });
    });
</script>
ALTUM;

});
