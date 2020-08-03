<?php
defined('ALTUMCODE') || die();

// $example_reports_result = $database->query("SELECT * FROM `instagram_users` WHERE `is_demo` = 1 AND `is_featured` = 1");
?>
<div class="container insta-profile-area">
    <div class="row">
<?php 
// print_r($search_results);die;
$k = 0;
foreach($search_results['users'] as $res){ 
$folllowers = 0;
$folllowing = 0;
////////////////////////////////////////////////////
if($k<4){
	$instagram = new \InstagramScraper\Instagram();
    $instagram->setUserAgent(get_random_user_agent());

    $is_proxy_request = select_proxy($database, $settings);    
    $instagram::setProxy($is_proxy_request);

    try {
        $source_account_data = $instagram->getAccount($res['user']['username']);
        // $folllowers = $source_account_data->getFollowedByCount();
        // $folllowing = $source_account_data->getFollowsCount();
    } catch (Exception $error) {

        /* Make sure to set the failed request to the proxy */
        if($is_proxy_request) {
            if($error->getCode() == 429) {
                $database->query("UPDATE `proxies` SET `failed_requests` = `failed_requests` + 1, `total_failed_requests` = `total_failed_requests` + 1, `last_date` = '{$date}' WHERE `proxy_id` = {$proxy->proxy_id}");
            } else {
                $database->query("UPDATE `proxies` SET `last_date` = '{$date}' WHERE `proxy_id` = {$proxy->proxy_id}");
            }
        }

        /* Redirect if the account does not have any data */
        if(!$source_account) {
            $_SESSION['error'][] = $error->getCode() == 404 ? $language->instagram->report->error_message->not_found : $error->getMessage();

            redirect();
        }

        /* Set the request as unsuccessful and set the last check date */
        $database->query("UPDATE `instagram_users` SET `last_check_date` = '{$date}' WHERE `id` = {$source_account->id}");
        $request_is_successful = false;
    }

    /* Make sure to set the successful request to the proxy */
    if($is_proxy_request) {

        if($proxy->failed_requests >= $settings->proxy_failed_requests_pause) {
            Database::update('proxies', ['failed_requests' => 0, 'successful_requests' => 1, 'last_date' => $date], ['proxy_id' => $proxy->proxy_id]);
        } else {
            $database->query("UPDATE `proxies` SET `successful_requests` = `successful_requests` + 1, `total_successful_requests` = `total_successful_requests` + 1, `last_date` = '{$date}' WHERE `proxy_id` = {$proxy->proxy_id}");
        }

    }
     ?>
    
    
    <?php
        if((int)$source_account_data->isPrivate()) {
            // $source_account_new->average_engagement_rate = 0;
            // $details = '';
        } else {
            $k = $k+1; 
            try {
                $media_response = $instagram->getPaginateMedias($res['user']['username'], '', $source_account_data);
            } catch (Exception $error) {
                $error_message = $_SESSION['error'][] = $error->getMessage();

                redirect();
            }

            /* Get extra details from last media */
            $likes_array = [];
            $comments_array = [];
            $engagement_rate_array = [];
            $hashtags_array = [];
            $mentions_array = [];
            $top_posts_array = [];
            $top_posts_by_likes = [];
            $top_posts_by_comments = [];
            $details = [];
            $greater_like = 0;
            $best_post_img_link='';
            // print_r($media_response);die;
            /* Go over each recent media post to generate stats */
            if ($media_response && !empty($media_response)) {
                foreach ($media_response['medias'] as $media) {
                    ///custom//
                        if($media->getLikesCount() > $greater_like){
                            $best_post_img_link = $media->getImageHighResolutionUrl();
                        }
                    ////custom ///
                    $likes_array[$media->getShortCode()] = $media->getLikesCount();
                    $comments_array[$media->getShortCode()] = $media->getCommentsCount();
                    $engagement_rate_array[$media->getShortCode()] = nr(($media->getLikesCount() + $media->getCommentsCount()) / ($source_account_data->getFollowedByCount()+1) * 100, 2);
                    $top_posts_by_likes[$media->getShortCode()] = $media->getLikesCount();
                    $top_posts_by_comments[$media->getShortCode()] = $media->getCommentsCount();

                    $hashtags = InstagramHelper::get_hashtags($media->getCaption());

                    foreach ($hashtags as $hashtag) {
                        if (!isset($hashtags_array[$hashtag])) {
                            $hashtags_array[$hashtag] = 1;
                        } else {
                            $hashtags_array[$hashtag]++;
                        }
                    }

                    $mentions = InstagramHelper::get_mentions($media->getCaption());

                    foreach ($mentions as $mention) {
                        if (!isset($mentions_array[$mention])) {
                            $mentions_array[$mention] = 1;
                        } else {
                            $mentions_array[$mention]++;
                        }
                    }

                    /* End if needed */
                    if (count($likes_array) >= $settings->instagram_calculator_media_count) break;
                }
            }

            /* Calculate needed details */
            $details['total_likes'] = array_sum($likes_array);
            $details['total_comments'] = array_sum($comments_array);
            $details['average_comments'] = count($likes_array) > 0 ? $details['total_comments'] / count($comments_array) : 0;
            $details['average_likes'] = count($likes_array) > 0 ? $details['total_likes'] / count($likes_array) : 0;
            // $source_account_new->average_engagement_rate = count($likes_array) > 0 ? number_format(array_sum($engagement_rate_array) / count($engagement_rate_array), 2) : 0;
            $details['ER'] = count($likes_array) > 0 ? number_format(array_sum($engagement_rate_array) / count($engagement_rate_array), 2) : 0;
            /* Do proper sorting */
            // print_r($top_posts_by_likes);echo '<br><br><br>';
            arsort($top_posts_by_likes);
            arsort($top_posts_by_comments);
            arsort($engagement_rate_array);
            arsort($hashtags_array);
            arsort($mentions_array);
            // print_r($top_posts_by_likes);echo '<br><br><br>';
            $top_posts_array = array_slice($engagement_rate_array, 0, 3);
            $top_hashtags_array = array_slice($hashtags_array, 0, 15);
            $top_mentions_array = array_slice($mentions_array, 0, 15);
            $top_posts_by_likes = array_slice($top_posts_by_likes, 0, 3);
            $top_posts_by_comments = array_slice($top_posts_by_comments, 0, 3);
            // print_r($top_posts_by_likes);echo '<br><br><br>';
            /* Get them all together */
            $details['top_hashtags'] = $top_hashtags_array;
            $details['top_mentions'] = $top_mentions_array;
            $details['top_posts'] = $top_posts_array;
            $details['top_posts_by_comments'] = $top_posts_by_comments;
            $details['top_posts_by_likes'] = $top_posts_by_likes;
            $details['background_image'] = $best_post_img_link;
            // $details = json_encode($details); ?>
            
        <div class="single-card single-card-wraper col-lg-4 col-md-6 col-sm-12 card card-shadow mt-5 mb-1 zoomer">
        
        <div class="card-body1 card-details card-border">
            <div class=" card-content d-flex flex-column flex-sm-column flex-wrap">

                <div class="card-profile-image d-flex justify-content-center justify-content-sm-start">
                    <?php if(!empty($source_account_data->getProfilePicUrl())): ?>
                        <img src="<?= $source_account_data->getProfilePicUrl(); ?>" onerror="$(this).attr('src', ($(this).data('failover')))" data-failover="<?= $settings->url . ASSETS_ROUTE ?>images/default_avatar.png" class="img-fluid rounded-circle instagram-avatar" alt="<?= $source_account_data->getFullName() != '' ? $source_account_data->getFullName() : $source_account_new->username; ?>" />
                    <?php endif ?>

                    <span class="fa-stack fa-xs source-badge-position" style="vertical-align: top;">
                        <i class="fa fa-fw fa-circle text-instagram fa-stack-2x"></i>
                        <i class="fab fa-fw fa-<?= $language->instagram->global->icon ?> fa-stack-1x fa-inverse"></i>
                    </span>
                </div>

                <div class=" card-profile-name d-flex justify-content-center justify-content-sm-start">
                    <div class="row d-flex flex-column">
                        <p class="m-0">
                            <a href="<?= 'https://instagram.com/'.$source_account_data->getUsername() ?>" target="_blank" class="text-dark" rel="nofollow"><?= '@' . $source_account_data->getUsername() ?></a>
                        </p>

                        <h1>
                            <a class="text-dark user-name" href="report/<?= $source_account_data->getUsername() ?>/instagram"><?= strlen($source_account_data->getFullName()) > 22 ? substr($source_account_data->getFullName(),0,20) : $source_account_data->getFullName(); ?></a>

                            <?php if((int)$source_account_data->isPrivate()): ?>
                                <span data-toggle="tooltip" title="<?= $language->instagram->report->display->private ?>"><i class="fa fa-fw fa-lock user-private-badge"></i></span>
                            <?php endif ?>

                            <?php if((int) $source_account_data->isVerified()): ?>
                                <span data-toggle="tooltip" title="<?= $language->instagram->report->display->verified ?>"><i class="fa fa-fw fa-check-circle user-verified-badge"></i></span>
                            <?php endif ?>
                        </h1>

                        

                    </div>
                </div>
                <div class=" card-profile-details d-flex justify-content-around align-items-center mt-4 mt-lg-0">
                    <div class="col d-flex flex-column justify-content-center">
                        <?= $language->instagram->report->display->followers ?>
                        <p class="report-header-number"><?= nr($source_account_data->getFollowedByCount(), 0, true) ?></p>
                    </div>

                    <div class="col d-flex flex-column justify-content-center">
                        <?= $language->instagram->report->display->uploads ?>
                        <p class="report-header-number"><?= nr($source_account_data->getMediaCount(),0, true) ?></p>
                    </div>

                    <div class="col d-flex flex-column justify-content-center">
                        <?= $language->instagram->report->display->engagement_rate ?>
                        <p class="report-header-number">
                            <?php if((int)$source_account_data->isPrivate()): ?>
                                N/A
                            <?php else: ?>
                                <?= nr($details['ER'], 2) ?>%
                            <?php endif ?>
                        </p>
                    </div>
                </div>
                <div class=" card-profile-details d-flex justify-content-around align-items-center mt-4 mt-lg-0">
                    <div class="col d-flex flex-column justify-content-center">
                        Average Likes
                        <p class="report-header-number"><?= nr($details['average_likes'], 0, true) ?></p>
                    </div>

                    <div class="col d-flex flex-column justify-content-center">
                        Average Comments
                        <p class="report-header-number"><?= nr($details['average_comments'], 0, true) ?></p>
                    </div>

                    <div class="col d-flex flex-column justify-content-center">
                        Average Views
                        <p class="report-header-number">
                            <?= nr($details['average_likes'], 0, true) ?>
                        </p>
                    </div>
                </div>

                <div class=" card-profile-btn d-flex justify-content-center justify-content-sm-start">
                    <div class="row d-flex flex-column">
                        <a href="report/<?= $source_account_data->getUsername() ?>/instagram" class="text-dark" rel="nofollow">   
                            <button class="btn btn-success btn-profile">
                                View Report
                            </button>
                        </a>
                        


                    </div>
                </div>
            </div>
        </div>
    </div>
                
                
                <?php    }
                
                ?>
                
        
        
        
<?php }else{
    ////////////////////////////////////////////////////
    //////////////////////////////////////////////////





?>
    <div class=" s-card single-card-wraper col-md-4 card card-shadow mt-5 mb-1 zoomer">
        <!--<div class="background-image-div insta-card card-body card-position-fix">-->
        <!--    <img src="assets/images/directory.jpg" alt="image">-->
        <!--</div>-->
        <div class="card-body2 card-details card-border">
            <div class=" card-content d-flex flex-column flex-sm-column flex-wrap">

                <div class="card-profile-image d-flex justify-content-center justify-content-sm-start">
                    <?php if(!empty($res['user']['profile_pic_url'])): ?>
                        <img src="<?= $res['user']['profile_pic_url'] ?>" onerror="$(this).attr('src', ($(this).data('failover')))" data-failover="<?= $settings->url . ASSETS_ROUTE ?>images/default_avatar.png" class="img-fluid rounded-circle in-av" alt="<?= $res['user']['full_name'] ?>" />
                    <?php endif ?>

                    <span class="fa-stack fa-xs source-badge-position" style="vertical-align: top;">
                        <i class="fa fa-fw fa-circle text-instagram fa-stack-2x"></i>
                        <i class="fab fa-fw fa-<?= $language->instagram->global->icon ?> fa-stack-1x fa-inverse"></i>
                    </span>
                </div>

                <div class=" card-profile-name d-flex justify-content-center justify-content-sm-start">
                    <div class="row d-flex flex-column">
                        <p class="m-0">
                            <a href="<?= 'https://instagram.com/'.$res['user']['username'] ?>" target="_blank" class="text-dark u-name" rel="nofollow"><?= '@' . $res['user']['username'] ?></a>
                        </p>

                        <h1>
                            <a class="text-dark u-name" href="report/<?= $res['user']['username'] ?>/instagram"><?= $res['user']['full_name'] ?></a>

                            <?php if($res['user']['is_private']): ?>
                                <span data-toggle="tooltip" title="<?= $language->instagram->report->display->private ?>"><i class="fa fa-fw fa-lock user-private-badge"></i></span>
                            <?php endif ?>

                            <?php if($res['user']['is_verified']): ?>
                                <span data-toggle="tooltip" title="<?= $language->instagram->report->display->verified ?>"><i class="fa fa-fw fa-check-circle user-verified-badge"></i></span>
                            <?php endif ?>
                        </h1>
                        <?php  if($folllowers != 0 && $folllowing !=0){ /*?>
                        <h5>Followers: <?= $folllowers ?></h5>
                        <h5>Following: <?= $folllowing ?></h5>
                        <?php  */ }?>
                        <h5><a class="btn btn-view-report" href="report/<?= $res['user']['username'] ?>/instagram">View Report</a></h5>
                        

                    </div>
                    
                    
                </div>

                
            </div>
        </div>
    </div>
<?php }  

    } ?>

</div>
</div>
