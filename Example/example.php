<?php

use PSpell\Config;

include(dirname(__DIR__) . '/BasSDK.php');
include(dirname(__DIR__) . '/BasSDKService.php');
include(dirname(__DIR__) . '/Initialization.php');

$initial = Initialization::getInstance();
$initial->Initialize(ENVIRONMENT::SANDBOX);

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (array_key_exists('login', $_POST)) {
        login();
    } else if (array_key_exists('user_info', $_POST)) {
        UserInfoV2();
    } else if (array_key_exists('initiate_payment', $_POST)) {
        InitiatePayment();
    } else if (array_key_exists('check_payment_status', $_POST)) {
        CheckPaymentStatus();
    }
}

// PHP Functions

function login()
{
    if (BasSDK::GetEnvironment() !== ENVIRONMENT::SANDBOX) {
        if (isset($_COOKIE['isInBasSuperApp'])) {
?>
            <script>
                var clientId = <?php echo BasSDK::GetClientId(); ?>;
                var x = await oauthToken(clientId);
            </script>
<?php
        }
    }
}

function UserInfoV2()
{
    $authCode = isset($_COOKIE['AuthCode']) ? $_COOKIE['AuthCode'] : null;
    if (!is_null($authCode)) {
        echo "AuthCode: " . htmlspecialchars($authCode) . nl2br("\n");

        $user_response = BasSDK::GetUserInfo($authCode);
        if (is_null($user_response)) {
            echo "GetUserInfo: Status= 0\nError Can't get Token";
            return;
        }

        $user_status = $user_response['status'];
        if ($user_status == 1) {
            $user_name = $user_response['data']['user_name'];
            echo "GetUserInfo: Status= " . $user_status . " User_Name: " . $user_name;
        } else {
            echo "GetUserInfo: Status= " . $user_status . " Code: " . $user_response['code'];
        }
    } else {
        echo "AuthCode cookie not found.";
    }
}

function InitiatePayment()
{
    $callBackUrl = "";
    $orderid = (string) time();
    $amount = rand(100, 10000);
    $customerInfoId = "75b32f99-5fe6-496f-8849-a5dedeb0a65f";
    $order = BasSDK::InitPayment($orderid, $amount, $callBackUrl, customerInfoId: $customerInfoId);

    echo "<pre>";
    print_r($order);
    echo "</pre>";
}

function CheckPaymentStatus()
{
    $orderid = "1729191828";
    $order_status = BasSDK::CheckPaymentStatus($orderid);

    echo "<pre>";
    print_r($order_status);
    echo "</pre>";
}

?>
