<?php

//include('BasChecksum.php');
// namespace BasSDK;
// use BasChecksum;
// use BasSDK;

class BasSDKHelper
{
    static function init_payment($orderId, $amount, $callBackUrl, $customerInfoName): mixed
    {
        $reqBody = '{"head":{"signature":"sigg","requestTimeStamp":"timess"},"body":bodyy}';
        // $requestTimestamp = gmdate("Y-m-d\TH:i:s\Z");
        $requestTimestamp = (string)  time();
        /* body parameters */
        $basgateParams["body"] = array(
            "appId" => BasSDK::GetAppId(),
            "requestTimestamp" => $requestTimestamp,
            "orderType" => "PayBill",
            "callBackUrl" => $callBackUrl,
            "customerInfo" => array(
                "id" => "75b32f99-5fe6-496f-8849-a5dedeb0a65f",
                "name" => "Abdullah AlAnsi"
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
        $bodystr = json_encode($basgateParams["body"]);

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
        $res = BasSDK::httpPost($url, $reqBody, $header);
        // return $res;
        if (!empty($res['body']['trxToken'])) {
            $data['trxToken'] = $res['body']['trxToken'];
            $data['trxId'] = $res['body']['trxId'];
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
        return $res;
        //return  json_decode($res, true);
        //return $data;
    }
}
