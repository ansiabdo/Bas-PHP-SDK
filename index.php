<?php

use PSpell\Config;

include('BasSDK.php');
include('BasSDKHelper.php');

function init($env)
{

    if ($env === "sandbox") {
        $initil = BasSDK::Initialize(
            $env,
            clientId: '453a95c0-1efa-4c9c-8341-392eb44d34f2',
            clientSecret: '453a95c0-1efa--83ac-146eb2654d16',
            appId: 'ac90ddd1-6627-4ae9-b268-98c17bd8ee6c',
            openId: '848a8f7d-3058-ab15-a90b-132816bef156',
            mKey: '',
        );
    } else {
        $initil = BasSDK::Initialize(
            $env,
            clientId: '653ed1ff-59cb-41aa-8e7f-0dc5b885a024',
            clientSecret: 'd93fbc8c-877b-4b8d-b822-e6dcdaf4b429',
            appId: '8f4871cb-b5ed-487e-baae-b6301f29db08',
            openId: '848a8f7d-3058-ab15-83ac-146eb2654d16',
            mKey: 'cmJsckQ1Nlh1S0FZVjJqQg==',
        );
    }


    echo 'Intialized environment : ' . BasSDK::GetEnvironmentValue();
    return '<script src="bassdk.js" type="text/javascript"></script>';
}

init(ENVIRONMENT::STAGING);
?>
<!DOCTYPE html>
<html>

<head>
    <script src="bassdk.js" type="text/javascript"></script>
    <title>
        Bas Sdk
    </title>
</head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<body style="text-align:left;font-size:3vw">

    <h1 style="color:deeppink;">
        Bas SDK
    </h1>

    <h4>
        LOGIN
    </h4>
    <form method="post">
        <?php
        if (BasSDK::GetEnvironmentValue() !== "sandbox"):
        ?>
            <div style="text-align: center;">
                <!-- <input type="submit" name="initialize" class="button" value="initialize" /> -->
                <input type="submit" name="login" class="button" value="login" />
                <input type="submit" name="userinfo" class="button" value="userinfo" />
                <input type="submit" name="order" class="button" value="New order" />
                <input type="submit" name="status" class="button" value="Order status" />
            </div>
        <?php
        endif;
        ?>
        <?php
        if (BasSDK::GetEnvironmentValue() === "sandbox"):
        ?>
            <br>
            <br>
            <div style="text-align: center;">
                <h4>SandBox Environment</h4>
            </div>
            <br>
            <div style="text-align: center;">
                <input type="submit" name="simulate_userinfo" class="button" value="Simulate Userinfo" />
                <input type="submit" name="simulateOrder" class="button" value="Simulate order" />
            </div>
        <?php
        endif;
        ?>

    </form>

    <?php

    if (array_key_exists('login', $_POST)) {
        login();
    } else if (array_key_exists('userinfo', $_POST)) {
        UserinfoV2();
    } else if (array_key_exists('simulate_userinfo', $_POST)) {
        SimulateUserInfo();
    } else if (array_key_exists('order', $_POST)) {
        orderV2();
    } else if (array_key_exists('status', $_POST)) {
        CheckStatusV2();
    } else if (array_key_exists('initialize', $_POST)) {
        initialize();
    } else if (array_key_exists('simulateOrder', $_POST)) {
        Simulateorder();
    }

    function initialize()
    {

        //ConfigProperties::SetEnvironment(ENVIRONMENT::STAGING);
        //echo ConfigProperties::GetEnvironmentValue();
        // $initil = BasSDK::Initialize(ENVIRONMENT::STAGING,
        // mKey:'AAECAwQFBgcICQoLDA0ODw==',
        // appId:'fdd4b312-c451-40fc-adc5-7c2ed83c4324',
        // clientId:'395b0e88-ad46-4692-b797-cedbd2f33d1f',
        // clientSecret:'2215d201-9c2c-4870-b5fd-a7cfc6268b83' );
        //echo('intialized environment  kxis: '.ConfigProperties::$environment->value);

    }

    function login()
    {
        if (BasSDK::GetEnvironmentValue() !== "sandbox") {
    ?>
            <script>
                var x = await oauthToken(' . BasSDK::GetClientId() . ');
            </script>
    <?php
        }

        echo "This is Button1 that is selected";

    }

    function SimulateUserInfo(): void
    {
        $clientId = BasSDK::GetClientId();
        $response = BasSDK::SimulateMobileFetchAuthAsync(clientId: $clientId);
        $status    = $response->status;
        $code      = $response->code;

        //$authid =   $response->data->authId;
        echo "Status: " . $status, nl2br("\n");

        echo "Code: " . $code, nl2br("\n");
        //echo "AuthId: " . $authid, nl2br("\n");

    }

    function UserinfoV2()
    {

        // $authCode = "B0CAE2FC89B9E5C6D9D8B5DF2AE5DAF94D13491E9376E11469119DD1A2FB3375";
        $authCode = isset($_COOKIE['AuthCode']) ? $_COOKIE['AuthCode'] : null;
        if (!is_null($authCode)) {
            echo "AuthCode: " . htmlspecialchars($authCode) . nl2br("\n");

            $user_response = BasSDK::getUserInfoV2($authCode);
            if (is_null($user_response)) {
                echo nl2br("\n"), "GetUserInfo: Status= 0", nl2br("\n"), nl2br("\n");
                echo "Error Can't get Token";
                return;
            }
            $user_status    = $user_response['status'];
            $user_code      = $user_response['code'];
            if ($user_status == 1) {
                $open_id =   $user_response['data']['open_id'];
                $user_name = $user_response['data']['user_name'];
                $name =      $user_response['data']['name'];
                $phone =     $user_response['data']['phone'];

                echo "GetUserInfo: Status= " . $user_status . " User_Name: " . $user_name, nl2br("\n"), nl2br("\n");
                // echo "\n My Name: " . $name;
                // echo "\n Open Id: " . $open_id;
            } else {
                echo "GetUserInfo: Status= " . $user_status . " Code: " . $user_code, nl2br("\n"), nl2br("\n");
                $message = $user_response['messages'][0];
                echo $message;
            }
        } else {
            echo "AuthCode cookie not found." . nl2br("\n");
        }
    }

    function Simulateorder(): void
    {
        $amount = "1000";
        $orderid = "785428564443638994";
        //$orderid = "1499725e-db64-4ab5-b91b-e33cd62410e4";
        $trxToken = "8jUAvVzp3IjR3ZCsJ2bpSrJomMF72O5sUFk3ODU0Mjg1NjQ0NDM2Mzg5OTQ=";
        $appId = BasSDK::GetAppId();

        $response = BasSDK::SimulateMobilePaymentAsync($orderid, $appId, trxToken: $trxToken, amount: $amount);
        // $status =   $response->status;
        //print_r($response);
        echo $response;
    }

    function orderV2()
    {

        $callBackUrl = "";
        $orderid = (string) time();    
        $amount = rand(100, 10000);
        //$name = "Test";
        $name = "75b32f99-5fe6-496f-8849-a5dedeb0a65f";
        $order = BasSDK::Init($orderid, $amount, $callBackUrl, customerInfoId: $name, orderDetails: ["id" => 100]);
        //$order = BasSDKHelper::init_payment($orderid, $amount, $callBackUrl, customerInfoName: $name);
        //echo "<pre>";
        print_r($order);
        //echo $order;
        //echo "</pre>";
        //echo $order;
       // $trxToken = $order['body']['trxToken'];
        //$orderid = $order['body']['order']['orderId'];
       // echo "trxToken" . $trxToken;
    }





    function CheckStatusV2()
    {
       // $orderid = "785428564443638994";
        $orderid = "1499725e-db64-4ab5-b91b-e33cd62410e4";
        $appId = BasSDK::GetAppId();
        $amount = rand(100, 10000);

        $order_status = BasSDK::CheckStatus($orderid);
        echo "<pre>";  // Optional: this adds better formatting for arrays in HTML
        // echo $order_status["body"]["trxStatus"];
        print_r($order_status);
        echo "</pre>";
    }

    function CheckStatus()
    {
        if (isset($_COOKIE["PaymentResult"])) {
            // Decode the cookie if it exists
            $invoice = json_decode($_COOKIE["PaymentResult"], true); // `true` to convert it to an associative array
            // You can now safely access $invoice
            print_r($invoice);
        } else {
            // Handle the case where the cookie is not set
            echo "PaymentResult cookie not found.";
        }
    }
    function order()
    {

        $callBackUrl = "";
        $orderid = "78542856444363899";
        $appId = BasSDK::GetAppId();
        $amount = rand(100, 10000);
        $order = BasSDK::Init($orderid, $amount, $callBackUrl, customerInfoId: 10, orderDetails: ["id" => 100]);
        //echo $order;
        $trxToken = $order['body']['trxToken'];
        $orderid = $order['body']['order']['orderId'];
        echo "trxToken: " . $trxToken, nl2br("\n");
        echo "orderid: " . $orderid . nl2br("\n");
        echo "<div id=orderid>";
        // echo htmlspecialchars($orderid); // put as the div content
        echo "</div>";
        echo "<div id=trxToken>";
        echo htmlspecialchars($trxToken); // put as the div content
        echo "</div>";
        echo "<div id=amount>";
        echo htmlspecialchars($amount); // put as the div content
        echo "</div>";
        echo "<div id=appid>";
        echo htmlspecialchars($appId); // put as the div content
        echo "</div>";

        echo '<script>getPayment();</script>';
    }


    ?>


</body>

</html>