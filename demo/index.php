<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require __DIR__ . '/../vendor/autoload.php';

$config = new Payname\ApiConfig('your-store-id', 'your-secret-key');

// Uncomment this line to enable simple auth
// Simple auth must be activated in your Payname account
// $config->setSimpleAuthEnabled(true);

$oauthManager = new Payname\Auth\OAuthTokenManager($config);

if(!empty($_POST)) {
    // Read POST inputs when Payname API returns oauth credentials after a new access token request
    if(isset($_POST['access_token']) && isset($_POST['refresh_token'])) {
        $oauthManager->getTokensFromInput();
    }

    // Current form payment has been submitted
    if(!empty($_POST['card_number']) && !empty($_POST['card_expiry_year']) && !empty($_POST['card_expiry_month']) && !empty($_POST['card_cvv'])) {
        try {
            // Create a new card with informations given
            $card = new Payname\Models\Card();
            $card->setNumber($_POST['card_number']);
            $card->setCvv($_POST['card_cvv']);
            $card->setExpiry($_POST['card_expiry_month'], $_POST['card_expiry_year']);

            // Create payment
            $payment = new Payname\Api\Payment($config);
            $payment->setAmount($_POST['amount']);
            $payment->setCard($card);
            $payment->setOrderId(mt_rand(1,99999));

            // Read response
            $response = $payment->create();

            if($response->isSuccess()) {
                // Confirm payment
                $confirmResponse = $payment->confirm();

                if($confirmResponse->isSuccess()) {
                    echo '<div style="color: green">Thanks ! Your payment of ' . $payment->getAmount() . ' euros has been confirmed !</div>';
                }
            }
        } catch(Payname\Exception\ApiResponseException $error) {
            echo '<div style="color: red">Payment has been refused : ' . $error->getMessage() .' (' . $error->getResponse()->getErrorCode() . ')</div>';
        } catch(Payname\Exception\ApiException $error) {
            echo '<div style="color: red">Payment error : ' . $error->getMessage() .'</div>';
        }
    }
} else {
    // Always request an access token before sending a new payment
    // If you already have a valid one, it was extracted from the cache
    // If not, a new one is request with the given refresh token
    $oauthManager->requestAccessToken();
}
?>
<h1>Credit card payment example</h1>
<p>Enter your credit card informations to make a payment of <strong>20.00 euros</strong>.</p>

<form action="index.php" method="post">
    <input type="hidden" name="amount" value="20" />
    <input type="hidden" name="order_id" value="1" />

    <label for="card_number">Card Number</label><br />
    <input type="text" name="card_number" value="" /><br />

    <label for="card_expiry_year">Expiration Date</label><br />
    <input type="text" name="card_expiry_year" value="" placeholder="YYYY" />
    <input type="text" name="card_expiry_month" value="" placeholder="mm" /><br />

    <label for="card_cvv">CVV</label><br />
    <input type="text" name="card_cvv" value="" /><br />

    <input type="submit" value="Pay" />
</form>
