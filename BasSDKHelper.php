<?php

//include('BasChecksum.php');
//namespace BasSdk;
// use BasChecksum;
// use BasSDK;

class BasSDKHelper
{

    // #Region Stage Environment Methods
    static public function GetUserInfo($code)
    {
        $header = array('Content-Type: application/x-www-form-urlencoded');
        $data = array();
        $data['client_id'] = BasSDK::GetClientId();
        $data['client_secret'] = BasSDK::GetClientSecret();
        $data['code'] = $code;
        $data['redirect_uri'] = BasSDK::GetAuthRedirectUrl();
        $body = http_build_query($data);

        if (!is_null($code)) {
            $response =    BasSDK::httpPost(BasSDK::GetuserInfoUrlV2(), $body, $header);
            return json_decode($response, true);
        }
        return null;
    }

    static function InitPayment($orderId, $amount, $callBackUrl, $customerInfoId): mixed
    {
        $reqBody = '{"head":{"signature":"sigg","requestTimeStamp":"timess"},"body":bodyy}';
        // $requestTimestamp = gmdate("Y-m-d\TH:i:s\Z");
        $requestTimestamp = (string)  time();
        /* body parameters */
        $params["body"] = array(
            "appId" => BasSDK::GetAppId(),
            "requestTimestamp" => $requestTimestamp,
            "orderType" => "PayBill",
            "callBackUrl" => $callBackUrl,
            "customerInfo" => array(
                "id" => $customerInfoId,
                "name" => "Test"
            ),
            "amount" => array(
                "value" => (float) $amount,
                "currency" => 'YER',
            ),

            "orderId" => $orderId,
            "orderDetails" => array(
                "Id" => $orderId,
                "Currency" => 'YER',
                "TotalPrice" => (float) $amount,
            )
        );
        $bodystr = json_encode($params["body"]);

        $checksum = BasChecksum::generateSignature($bodystr, BasSDK::GetMKey());

        if ($checksum === false) {
            error_log(
                sprintf(
                    /* translators: 1: Event data. */
                    'Could not retrieve signature, please try again Data: %1$s.',
                    $bodystr
                )
            );
            throw new Exception('Could not retrieve signature, please try again.', BasSDK::GetMKey());
        }

        /* prepare JSON string for request */
        $reqBody = str_replace('bodyy', $bodystr, $reqBody);
        $reqBody = str_replace('sigg', $checksum, $reqBody);
        $reqBody = str_replace('timess', '1729020006', $reqBody);
        print_r($reqBody);
        echo nl2br("\n") ."";
        $url = BasSDK::GetInitiatePaymentUrl();
        $header = array('Accept: text/plain', 'Content-Type: application/json');
        $response = BasSDK::httpPost($url, $reqBody, $header);
        if (BasSDK::isSandboxEnvironment()) {
            return  json_decode($response, true);
        }
        $isVerify = BasChecksum::verifySignature($bodystr, BasSDK::GetMKey(), checksum: $checksum);
        if (!$isVerify) {
            throw new InvalidArgumentException("BASSDK.verifySignature Invalid_response_signature");
        }
        if (!empty($res['body']['trxToken'])) {
            $data['trxToken'] = $response['body']['trxToken'];
            $data['trxId'] = $response['body']['trxId'];
            $data['callBackUrl'] = $callBackUrl;
        } else {
            error_log(
                sprintf(
                    /* translators: 1: bodystr, 2:. */
                    'trxToken empty \n bodystr: %1$s , \n $checksum: %2$s.',
                    $bodystr,
                    $checksum
                )
            );
            $data['trxToken'] = "";
        }
        return  json_decode($response, true);
        //return  json_decode($res, true);
        //return $data;
    }


    static public function CheckPaymentStatus($orderId)
    {
        $requestTimestamp = '1668714632332';
        $header = array('Content-Type: application/json');

        $bodyy['RequestTimestamp'] = $requestTimestamp;
        $bodyy['AppId'] = BasSDK::GetAppId();
        $bodyy['OrderId'] = $orderId;


        $bodyyStr = json_encode($bodyy);

        $basChecksum = BasChecksum::generateSignature($bodyyStr, BasSDK::GetMKey());

        $head["Signature"] = $basChecksum;
        $head["RequestTimestamp"] = $requestTimestamp;

        $req["Head"] = $head;
        $req["Body"] = $bodyy;

        $data = json_encode($req);
        $paymentStatusUrl = BasSDK::GetPaymentStatusUrl();
        $resp = BasSDK::httpPost(url: $paymentStatusUrl, data: $data, header: $header);

        return  json_decode($resp, true);
        // return $resp;
    }
    #endregion

    // #region  SandBox Simulation
    public static function SimulateMobileFetchAuth($clientId): mixed
    {
        if (!BasSDK::isSandboxEnvironment()) {
            throw new InvalidArgumentException('This method is only allowed on Sandbox environment');
        }
        $header = array('Content-Type: application/json');
        $url = BasSDK::GetMobileFetchAuthUrl();
        $fulUrl = $url . "?clientId=" . urlencode($clientId);
        //echo "Full auth Url is: ". $fulUrl .nl2br("\n");
        $jsonResponse = BasSDK::httpPost($fulUrl, data: null, header: $header);
        echo $jsonResponse . nl2br("\n");
        $response = json_decode($jsonResponse);
        // echo "Fatch Auth Response is : ". $response .nl2br("\n");
        //echo $response .nl2br("\n");
        return $response;
    }

    public static function SimulateMobilePayment($orderId, $trxToken, $amount): mixed
    {
        if (!BasSDK::isSandboxEnvironment()) {
            throw new InvalidArgumentException('This method is only available on Sandbox environment');
        }
        $header = array('Content-Type: application/json');
        $data = array();

        $data['amount'] = ['value' => $amount, 'currency' => 'YER'];
        $data['appId'] = BasSDK::GetAppId();
        $data['orderId'] = $orderId;
        $data['trxToken'] = $trxToken;

        // Convert the data array to a JSON string
        $body = json_encode($data);
        //$body = http_build_query($data);
        $url = BasSDK::GetMobilePaymentUrl();
        $response = BasSDK::httpPost($url, $body, header: $header);
        return $response;
    }




}
