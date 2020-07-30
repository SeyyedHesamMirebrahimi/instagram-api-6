<?php
defined('ALTUMCODE') || die();

// $example_reports_result = $database->query("SELECT * FROM `instagram_users` WHERE `is_demo` = 1 AND `is_featured` = 1");
$example_reports_result = $database->query("SELECT * FROM `instagram_users` ORDER BY `id` DESC LIMIT 6");
?>
<div class="container insta-profile-area">
    <div class="row">
<?php while($source_account = $example_reports_result->fetch_object()): ?>
<?php 
    $details = json_decode($source_account->details,true);
    // print_r($details['average_likes']);
?>
    <div class="single-card single-card-wraper col-lg-4 col-md-6 col-sm-12 card card-shadow mt-5 mb-1 zoomer">
        <?php /*
        <div class="background-image-div insta-card card-body card-position-fix">
            <?php if(isset($details['background_image']) && !empty($details['background_image'])){ ?>
            <img src="<?= $details['background_image']?>" alt="image">
            <?php }else{ ?>
            <img src="assets/images/directory.jpg" alt="image">
            <?php } ?>
        </div>
        */ ?>
        <div class="card-body1 card-details card-border">
            <div class=" card-content d-flex flex-column flex-sm-column flex-wrap">

                <div class="card-profile-image d-flex justify-content-center justify-content-sm-start">
                    <?php if(!empty($source_account->profile_picture_url)): ?>
                        <img src="<?= $source_account->profile_picture_url ?>" onerror="$(this).attr('src', ($(this).data('failover')))" data-failover="<?= $settings->url . ASSETS_ROUTE ?>images/default_avatar.png" class="img-fluid rounded-circle instagram-avatar" alt="<?= $source_account->full_name ?>" />
                    <?php endif ?>

                    <span class="fa-stack fa-xs source-badge-position" style="vertical-align: top;">
                        <i class="fa fa-fw fa-circle text-<?= $plugin_identifier ?> fa-stack-2x"></i>
                        <i class="fab fa-fw fa-<?= $language->{$plugin_identifier}->global->icon ?> fa-stack-1x fa-inverse"></i>
                    </span>
                </div>

                <div class=" card-profile-name d-flex justify-content-center justify-content-sm-start">
                    <div class="row d-flex flex-column">
                        <p class="m-0">
                            <a href="<?= 'https://instagram.com/'.$source_account->username ?>" target="_blank" class="text-dark" rel="nofollow"><?= '@' . $source_account->username ?></a>
                        </p>

                        <h1>
                            <a class="text-dark user-name" href="report/<?= $source_account->username ?>/<?= $plugin_identifier ?>"><?= $source_account->full_name ?></a>

                            <?php if($source_account->is_private): ?>
                                <span data-toggle="tooltip" title="<?= $language->instagram->report->display->private ?>"><i class="fa fa-fw fa-lock user-private-badge"></i></span>
                            <?php endif ?>

                            <?php if($source_account->is_verified): ?>
                                <span data-toggle="tooltip" title="<?= $language->instagram->report->display->verified ?>"><i class="fa fa-fw fa-check-circle user-verified-badge"></i></span>
                            <?php endif ?>
                        </h1>

                        

                    </div>
                </div>

                <div class=" card-profile-details d-flex justify-content-around align-items-center mt-4 mt-lg-0">
                    <div class="col d-flex flex-column justify-content-center">
                        <?= $language->instagram->report->display->followers ?>
                        <p class="report-header-number"><?= nr($source_account->followers, 0, true) ?></p>
                    </div>

                    <div class="col d-flex flex-column justify-content-center">
                        <?= $language->instagram->report->display->uploads ?>
                        <p class="report-header-number"><?= nr($source_account->uploads,0, true) ?></p>
                    </div>

                    <div class="col d-flex flex-column justify-content-center">
                        <?= $language->instagram->report->display->engagement_rate ?>
                        <p class="report-header-number">
                            <?php if($source_account->is_private): ?>
                                N/A
                            <?php else: ?>
                                <?= nr($source_account->average_engagement_rate, 2) ?>%
                            <?php endif ?>
                        </p>
                    </div>
                </div>
                <div class=" card-profile-details d-flex justify-content-around align-items-center mt-4 mt-lg-0">
                    <div class="col d-flex flex-column justify-content-center">
                        Average Likes
                        <p class="report-header-number"><?= nr(intval(str_replace(",","",$details['average_likes'])), 0, true) ?></p>
                    </div>

                    <div class="col d-flex flex-column justify-content-center">
                        Average Comments
                        <p class="report-header-number"><?= nr(intval(str_replace(",","",$details['average_comments'])), 0, true) ?></p>
                    </div>

                    <div class="col d-flex flex-column justify-content-center">
                        Average Views
                        <p class="report-header-number">
                            <?= nr(intval(str_replace(",","",$details['average_likes'])), 0, true) ?>
                        </p>
                    </div>
                </div>

                <div class=" card-profile-btn d-flex justify-content-center justify-content-sm-start">
                    <div class="row d-flex flex-column">
                        <a href="report/<?= $source_account->username ?>/<?= $plugin_identifier ?>" class="text-dark" rel="nofollow">   
                            <button class="btn btn-success btn-profile">
                                View Report
                            </button>
                        </a>
                        


                    </div>
                </div>
            </div>
        </div>
    </div>
	
<?php endwhile ?>

</div>
</div>








<?php
/*
defined('ALTUMCODE') || die();

$example_reports_result = $database->query("SELECT * FROM `instagram_users` WHERE `is_demo` = 1 AND `is_featured` = 1");
?>

<?php while($source_account = $example_reports_result->fetch_object()): ?>
    <div class="card card-shadow mt-5 mb-1 zoomer">
        <div class="card-body">
            <div class="d-flex flex-column flex-sm-row flex-wrap">

                <div class="col-sm-4 col-md-3 col-lg-2 d-flex justify-content-center justify-content-sm-start">
                    <?php if(!empty($source_account->profile_picture_url)): ?>
                        <img src="<?= $source_account->profile_picture_url ?>" onerror="$(this).attr('src', ($(this).data('failover')))" data-failover="<?= $settings->url . ASSETS_ROUTE ?>images/default_avatar.png" class="img-fluid rounded-circle instagram-avatar" alt="<?= $source_account->full_name ?>" />
                    <?php endif ?>

                    <span class="fa-stack fa-xs source-badge-position" style="vertical-align: top;">
                        <i class="fa fa-fw fa-circle text-<?= $plugin_identifier ?> fa-stack-2x"></i>
                        <i class="fab fa-fw fa-<?= $language->{$plugin_identifier}->global->icon ?> fa-stack-1x fa-inverse"></i>
                    </span>
                </div>

                <div class="col-sm-8 col-md-9 col-lg-5 d-flex justify-content-center justify-content-sm-start">
                    <div class="row d-flex flex-column">
                        <p class="m-0">
                            <a href="<?= 'https://instagram.com/'.$source_account->username ?>" target="_blank" class="text-dark" rel="nofollow"><?= '@' . $source_account->username ?></a>
                        </p>
                            <?php 
                                $details = json_decode($source_account->details,true);
                                // print_r($details['average_likes']);
                            ?>
                        

                        <h1>
                            <a class="text-dark" href="report/<?= $source_account->username ?>/<?= $plugin_identifier ?>"><?= $source_account->full_name ?></a>

                            <?php if($source_account->is_private): ?>
                                <span data-toggle="tooltip" title="<?= $language->instagram->report->display->private ?>"><i class="fa fa-fw fa-lock user-private-badge"></i></span>
                            <?php endif ?>

                            <?php if($source_account->is_verified): ?>
                                <span data-toggle="tooltip" title="<?= $language->instagram->report->display->verified ?>"><i class="fa fa-fw fa-check-circle user-verified-badge"></i></span>
                            <?php endif ?>
                        </h1>

                        <small class="text-muted"><?= $source_account->description ?></small>

                    </div>
                </div>

                <div class="col-md-12 col-lg-5 d-flex justify-content-around align-items-center mt-4 mt-lg-0">
                    <div class="col d-flex flex-column justify-content-center">
                        <?= $language->instagram->report->display->followers ?>
                        <p class="report-header-number"><?= nr($source_account->followers, 0, true) ?></p>
                    </div>

                    <div class="col d-flex flex-column justify-content-center">
                        <?= $language->instagram->report->display->uploads ?>
                        <p class="report-header-number"><?= nr($source_account->uploads,0, true) ?></p>
                    </div>

                    <div class="col d-flex flex-column justify-content-center">
                        <?= $language->instagram->report->display->engagement_rate ?>
                        <p class="report-header-number">
                            <?php if($source_account->is_private): ?>
                                N/A
                            <?php else: ?>
                                <?= nr($source_account->average_engagement_rate, 2) ?>%
                            <?php endif ?>
                        </p>
                    </div>
                    <div class="col d-flex flex-column justify-content-center">
                        <?php /*
                    <form class="form-inline d-inline-flex search_form" action="" method="GET">
                        <div style="display: none;">
                        <div class="dropdown my-2">
                            <button class="btn btn-light index-source-button dropdown-toggle border-0" data-source="instagram" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Instagram
                            </button>

                            <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 47px, 0px); top: 0px; left: 0px; will-change: transform;">
                                    <a class="dropdown-item source-select-item" href="#" data-source="twitter">Twitter</a>
                                    <a class="dropdown-item source-select-item" href="#" data-source="instagram">Instagram</a>
                            </div>
                        </div>
                        <div class="index-input-div">
                            <input class="form-control index-search-input border-0 form-control-lg source_search_input" type="text" placeholder="Enter instagram username or profile link.." aria-label="Enter twitter username.." value="<?= $source_account->username ?>">
                        </div>
                        </div>
                            <button type="submit" class="btn index-submit-button border-0 d-inline-block btn-instagram">Search</button>
                        </form>
                         ?>
                        <a class="text-dark btn btn-instagram" href="report/<?= $source_account->username ?>/<?= $plugin_identifier ?>">View Profile</a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endwhile 



1*/?>
