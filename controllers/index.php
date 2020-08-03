<?php

defined('ALTUMCODE') || die();

$controller_has_container = false;

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $query = $_POST['query'];
    $_SESSION['queryy'] = $query;
    $instagram = new \InstagramScraper\Instagram();
//	$instagram = $instagram->withCredentials('mubahsar__mehmood','NEWKM@power1122');
    $instagram->setUserAgent(get_random_user_agent());

}

$is_proxy_request = select_proxy($database, $settings);
$instagram::setProxy($is_proxy_request);

$search_results = $instagram->generalSearch($query);
// var_dump($search_results);
// exit;

    
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
