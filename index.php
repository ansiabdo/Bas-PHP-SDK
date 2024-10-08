<?php

use PSpell\Config;

include('BasSDK.php');


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
        <input type="submit" name="initialize" class="button" value="initialize" />
        <input type="submit" name="login" class="button" value="login" />

        <input type="submit" name="userinfo" class="button" value="userinfo" />
        <input type="submit" name="order" class="button" value="New order" />
        <input type="submit" name="status" class="button" value="Order status" />
    </form>

    <?php
    if (array_key_exists('login', $_POST)) {
        login();
    } else if (array_key_exists('userinfo', $_POST)) {
        userinfo();
    } else if (array_key_exists('order', $_POST)) {
        order();
    } else if (array_key_exists('status', $_POST)) {
        CheckStatus();
    }
    else if (array_key_exists('initialize', $_POST)) {
        initialize();
    }
    function initialize()
    {   
        $initil = BasSDK::Initialize(ENVIRONMENT::SANDBOX,
        mKey:'AAECAwQFBgcICQoLDA0ODw==',
        appId:'fdd4b312-c451-40fc-adc5-7c2ed83c4324',
        clientId:'395b0e88-ad46-4692-b797-cedbd2f33d1f',
        clientSecret:'2215d201-9c2c-4870-b5fd-a7cfc6268b83' );

    }
    function login()
    {
        $response = '<script>oauthToken();</script>';

        echo "This is Button1 that is selected";
        echo $response;
    }
    function order()
    {

        $callBackUrl = "";
        $orderid = rand();
        $appId = "453a95c0-1efa-4c9c-8341-392eb44d34f2";
        $amount = rand(100, 10000);
        $order = BasSDK::Init($orderid, $amount, $callBackUrl, customerInfoId: 10, orderDetails: ["id" => 100]);
        //echo $order;
        $trxToken = $order['body']['trxToken'];
        $orderid = $order['body']['order']['orderId'];
        //echo "trxToken: ".$trxToken,nl2br("\n");
        //  echo "orderid: ".$orderid.nl2br("\n");
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

        
        //$invoice = json_decode($_COOKIE["PaymentResult"]);
        $status =   $invoice->status;
        echo nl2br("\n");
        $orderid =   $invoice->data->orderId;
        //$orderid = "5836";

        echo "status: " . $status . nl2br("\n");
        echo "orderid " . $orderid . nl2br("\n");

        $order_status = BasSDK::CheckStatus($orderid);
        echo $order_status;
    }
    function userinfo()
    {

      //  echo ConfigProperties::$environment->value;
        //$response = json_decode($_COOKIE["AuthCode"]);
       // echo "intialized client id is: " . ConfigProperties::$clientId;
       //$clientId = "395b0e88-ad46-4692-b797-cedbd2f33d1f";
       $clientId = "453a95c0-1efa-4c9c-8341-392eb44d34f2";
       echo"Client Id is >> ". $clientId .nl2br("\n");
        $response = BasSDK::SimulateMobileFetchAuthAsync(clientId: $clientId);
        $authid =   $response->data->authId;
        echo "Auth Id is: " .$authid .nl2br("\n");
    
        //echo 'fetchAuthResponse '. $response .  nl2br("\n");
        //var_dump($response);
        $status =   $response->status;
        echo nl2br("\n");
        $authid =   $response->data->authId;
        echo "Step1-GetAuthCode: Status= " . $status, nl2br("\n");
        echo "AuthId: " . $authid, nl2br("\n");
        //     $message = $response->messages[0];
        //     echo $message;

        echo nl2br("\n");
        if ($status == 1) {

            $user_response = BasSDK::getUserInfoV2($authid);
            if (is_null($user_response)) {
                echo nl2br("\n"), "Step2-GetUserInfo: Status= 0", nl2br("\n"), nl2br("\n");
                echo "Error Can't get Token";
                return;
            }
            $user_status    = $user_response['status'];
            $user_code      = $user_response['code'];
            if ($user_status == 1) {
                echo "Step2-GetUserInfo: Status= " . $user_status . " Code: " . $user_code, nl2br("\n"), nl2br("\n");
                $open_id =   $user_response['data']['open_id'];
                $user_name = $user_response['data']['user_name'];
                $name =      $user_response['data']['name'];
                $phone =     $user_response['data']['phone'];

                echo "\n My Name: " . $name;
                echo "\n Open Id: " . $open_id;
            } else {
                echo "Step2-GetUserInfo: Status= " . $user_status . " Code: " . $user_code, nl2br("\n"), nl2br("\n");
                $message = $user_response['messages'][0];
                echo $message;
            }
        } else {

            echo "Can not Complite Step1-GetAuthCode";
        }
        //   echo $response;

    }


    ?>


</body>

</html>