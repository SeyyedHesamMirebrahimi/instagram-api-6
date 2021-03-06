<?php
defined('ALTUMCODE') || die();

if(empty($settings->store_stripe_publishable_key) || empty($settings->store_stripe_secret_key) || empty($settings->store_stripe_webhook_secret)) {
    $_SESSION['info'][] = $language->store->info_message->stripe_not_available;
    User::get_back('store');
}

$method = (isset($parameters[0]) && in_array($parameters[0], ['stripe-success', 'stripe-cancel'])) ? $parameters[0] : false;

/* Return confirmation processing if successfuly */
if($method && $method == 'stripe-success') {
    $_SESSION['success'][] = $language->store->success_message->paid;
    redirect('store');
}

/* Return confirmation processing if failed */
if($method && $method == 'stripe-cancel') {
    $_SESSION['info'][] = $language->store->info_message->canceled;
    redirect('store');
}

/* Initiate Stripe */
\Stripe\Stripe::setApiKey($settings->store_stripe_secret_key);

/* Process form submission */
if(!empty($_POST) && isset($_POST['amount'])) {
    $amount = round(intval($_POST['amount']), 2) * 100;

    /* Create the payment session */
    $stripe_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'name' => $settings->title,
            'description' => $language->store->stripe->description,
            'amount' => $amount,
            'currency' => $settings->store_currency,
            'quantity' => 1,
        ]],
        'client_reference_id' => $account_user_id . '###' . time(),
        'success_url' => url('stripe/stripe-success'),
        'cancel_url' => url('stripe/stripe-cancel'),
    ]);
}



/* Process the webhook */
$payload = @file_get_contents('php://input');
$sig_header = @$_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

if($sig_header) {
    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $settings->store_stripe_webhook_secret
        );

        if($event->type == 'checkout.session.completed') {
            $session = $event->data->object;

            $payment_id = $session->id;
            $payer_id = $session->customer;
            $payer_object = \Stripe\Customer::retrieve($payer_id);
            $payer_email = $payer_object->email;
            $payer_name = $payer_object->name;

            $payment_total = $session->display_items[0]->amount / 100;
            $payment_currency = strtoupper($session->display_items[0]->currency);

            $extra = explode('###', $session->client_reference_id);

            $user_id = (int) $extra[0];

            /* Make sure the transaction is not already existing */
            if(Database::exists('id', 'payments', ['payment_id' => $payment_id, 'type' => 'STRIPE'])) {
                http_response_code(400);
                die();
            }

            /* Add a log into the database */
            Database::insert(
                'payments',
                [
                    'user_id' => $user_id,
                    'type' => 'STRIPE',
                    'email' => $payer_email,
                    'payment_id' => $payment_id,
                    'payer_id' => $payer_id,
                    'name' => $payer_name,
                    'amount' => $payment_total,
                    'currency' => $payment_currency,
                    'date' => $date
                ]
            );

            /* Update the users balance */
            $new_points = (int) intval($payment_total);

            $database->query("UPDATE `users` SET `points` = `points` + {$new_points} WHERE `user_id` = {$user_id}");

            /* Send notification to admin if needed */
            if($settings->admin_new_payment_email_notification && !empty($settings->admin_email_notification_emails)) {

                sendmail(
                    explode(',', $settings->admin_email_notification_emails),
                    sprintf($language->global->email->admin_new_payment_email_notification_subject, 'STRIPE', intval($payment_total), $settings->store_currency),
                    sprintf($language->global->email->admin_new_payment_email_notification_body, intval($payment_total), $settings->store_currency)
                );

            }

            echo 'successful';
        }

    } catch (\UnexpectedValueException $e) {

        // Invalid payload
        http_response_code(400);
        exit();

    } catch (\Stripe\Error\SignatureVerification $e) {

        // Invalid signature
        http_response_code(400);
        exit();

    }
}
