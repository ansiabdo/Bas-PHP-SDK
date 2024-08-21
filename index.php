<?php
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

        $amount = rand(100, 10000);
        $order = BasSDK::Init($orderid, $amount, $callBackUrl, 10, ["id" => 100]);
        //echo $order;
        $trxToken = $order['body']['trxToken'];
        $orderid = $order['body']['order']['orderId'];
        //echo "trxToken: ".$trxToken,nl2br("\n");
        //  echo "orderid: ".$orderid.nl2br("\n");
        echo "<div id=orderid>";
        echo htmlspecialchars($orderid); // put as the div content
        echo "</div>";
        echo "<div id=trxToken>";
        echo htmlspecialchars($trxToken); // put as the div content
        echo "</div>";
        echo "<div id=amount>";
        echo htmlspecialchars($amount); // put as the div content
        echo "</div>";
        echo "<div id=appid>";
        echo htmlspecialchars(APPID); // put as the div content
        echo "</div>";

        echo '<script>getPayment();</script>';
    }
    function CheckStatus()
    {
        $invoice = json_decode($_COOKIE["PaymentResult"]);
        $status =   $invoice->status;
        echo nl2br("\n");
        $orderid =   $invoice->data->orderId;

        echo "status: " . $status . nl2br("\n");
        echo "orderid " . $orderid . nl2br("\n");

        $order_status = BasSDK::CheckStatus($orderid);
        echo $order_status;
    }
    function userinfo()
    {
        $response = json_decode($_COOKIE["AuthCode"]);
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

            $user_response = BasSDK::getUserInfo($authid);
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