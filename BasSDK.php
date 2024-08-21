<?php
include('BasChecksum.php');
include('config.php');
/**
 * Bas uses checksum signature to ensure that API requests and responses shared between your 
 * application and Bas over network have not been tampered with. We use SHA256 hashing and 
 * AES128 encryption algorithm to ensure the safety of transaction data.
 *
 * @author     Kamal Hassan
 * @version    0.0.1
 * @link       https://.basgate.com/docs/
 */



class BasSDK
{
    private static $ContentTypexwww = 'Content-Type: application/x-www-form-urlencoded';
    private static $ContentTypeJson =  array('Content-Type: application/json', 'Accept: text/plain');


    static public function getUserInfo($code)
    {

        $token = self::getToken($code);
        //return $token;
        if (!is_null($token)) {
            $header = array('Authorization: Bearer ' . $token);
            $response =    self::httpGet(BASEURL . "auth/userInfo", null, $header);
            return json_decode($response, true);
        }
        return null;
    }

    static public function getToken($code)
    {
        $header = array('Content-Type: application/x-www-form-urlencoded');
        $data = array();
        $data['client_secret'] = CLIENT_SECRET;
        $data['client_id'] = CLIENT_ID;
        $data['grant_type'] = 'authorization_code';
        $data['code'] = $code;
        $data['redirect_uri'] = BASEURL . 'auth/callback';

        //return http_build_query($data) . "\n";
        $body = http_build_query($data);
        $response =    self::httpPost(BASEURL . "auth/token", $body, $header);
        $response = json_decode($response, true);
        if (!is_array($response)) {
            //  echo "is Not Array ".$response;
            return null;
        } else {

            if (array_key_exists('access_token', $response)) {
                //  $response=json_decode($response, true);
                // echo $response;
                return $response['access_token'];
            }
        }

        return $response;
    }

    static public function Init($orderId, $amount, $callBackUrl, $customerInfoId, $orderDetails)
    {
        $header = array('Accept: text/plain', 'Content-Type: application/json');
        //  $config=include('config.php');
        $requestTimestamp = '1678695536';


        $bodyy['requestTimestamp'] = $requestTimestamp;
        $bodyy['appId'] = APPID;
        $bodyy['orderId'] = $orderId;
        $bodyy['orderType'] = 'PayBill';
        $bodyy['amount'] = ['value' => $amount, 'currency' => 'YER'];

        $bodyy['callBackUrl'] = $callBackUrl;

        $bodyy['customerInfo'] = ['id' => $customerInfoId, 'name' => 'Test'];
        $bodyy['orderDetails'] = $orderDetails;

        $bodyyStr = json_encode($bodyy);

        $basChecksum = BasChecksum::generateSignature($bodyyStr, MKEY);

        $head["signature"] = $basChecksum;
        $head["requestTimestamp"] = $requestTimestamp;




        $req["head"] = $head;
        $req["body"] = $bodyy;
        $paymentUrl = BASEURL . 'merchant/secure/transaction/initiate';
        $resp = self::httpPost($paymentUrl, json_encode($req), $header);

        return  json_decode($resp, true);
    }


    static public function CheckStatus($orderId)
    {
        $requestTimestamp = '1668714632332';
        $header = array('Content-Type: application/json');

        $bodyy['RequestTimestamp'] = $requestTimestamp;
        $bodyy['AppId'] = APPID;
        $bodyy['OrderId'] = $orderId;


        $bodyyStr = json_encode($bodyy);

        $basChecksum = BasChecksum::generateSignature($bodyyStr, MKEY);

        $head["Signature"] = $basChecksum;
        $head["RequestTimestamp"] = $requestTimestamp;


        $req["Head"] = $head;
        $req["Body"] = $bodyy;



        $paymentUrl = BASEURL . 'merchant/secure/transaction/status';
        $resp = self::httpPost($paymentUrl, json_encode($req), $header);

        return $resp;
    }


    static function httpPost($url, $data, $header)
    {


        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);


        if ($httpCode != 200) {
            $msg = "Return httpCode is {$httpCode} \n"
                . curl_error($curl) . "URL: " . $url;
            //echo $msg,nl2br("\n");
            // echo $msg.$errorresponse['Messages'][0];
            curl_close($curl);
            return $msg;
            //return $response;
        } else {
            curl_close($curl);
            return $response;
        }
    }
    static function httpGet($url, $data, $header)
    {

        //if($url)
        $curl = curl_init($url);

        //curl_setopt($curl, CURLOPT_POST, true);
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);




        if ($httpCode != 200) {
            $msg = "Return httpCode is {$httpCode} \n"
                . curl_error($curl) . "URL: " . $url;

            // echo $msg.$errorresponse['Messages'][0];
            curl_close($curl);
            return $msg;
        } else {
            curl_close($curl);

            return $response;
        }
    }
}
