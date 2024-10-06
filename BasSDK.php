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


    static public function getUserInfoV2($code)
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

    static public function getUserInfo($code)
    {
        $data = array();
        $data['client_secret'] = self::GetClientSecret();
        $data['client_id'] = self::GetClientId();
        //$data['grant_type'] = 'authorization_code';
        $data['code'] = $code;
        $data['redirect_uri'] = BASEURL . 'auth/callback';

        $token = self::getToken($code);
        //return $token;
        if (!is_null($token)) {
            $header = array('Authorization: Bearer ' . $token);
            $response =    self::httpGet(BASEURL . "auth/userInfo", null, $header);
            return json_decode($response, true);
        }
        return null;
    }

    static public function getToken($code): mixed
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
   
    
/**
     * Implement changes based on updates to Bas Sdk in C#
     *
     *
     */

     public static function Initialize(ENVIRONMENT $environment, string $mKey, string $appId, string $clientId, string $clientSecret): void
     {
         ConfigProperties::$environment = $environment;
         self::SetAppId($appId); 
         self::SetMKey(mKey: $mKey); 
         self::SetClientId(clientId: $clientId); 
         self::SetClientSecret(clientSecret: $clientSecret); 
     }

     /**
         * 
         * Get full baseUrl of API
         */

     static function GetFullBaseUrlBasedOnEnvironment($relativePath): string {
        $baseUrl = "";
        switch (ConfigProperties::$environment) {
            case ENVIRONMENT::STAGING:
                $baseUrl = ConfigProperties::baseUrlStaging.$relativePath;
                return $baseUrl;
            case ENVIRONMENT::PRODUCTION:
                $baseUrl = ConfigProperties::baseUrlProduction.$relativePath;
                return $baseUrl;
            case ENVIRONMENT::SANDBOX:
                $baseUrl = ConfigProperties::BaseUrlSandbox.$relativePath;
                return $baseUrl;
            default:
               throw new InvalidArgumentException("BASSDK.UnKnown Environment" . ConfigProperties::$environment);
         }
    }


      /**
     * Set the appId during initialization.
     *
     * @param  int  $appId
     * 
     */
    private static function SetAppId(string $appId): void
    {
        if (empty($appId)) {
            throw new InvalidArgumentException("BASSDK.SetAppId appId is null");
        }
        ConfigProperties::$appId = $appId;
    }

      /**
     * Set the mKey during initialization.
     *
     * @param  int  $mKey
     * 
     */
    private static function SetMKey(string $mKey): void
    {
        if (empty($mKey)) {
            throw new InvalidArgumentException("BASSDK.SetmKey mKey is null");
        }
        ConfigProperties::$mKey = $mKey;
    }

      /**
     * Set the clientId during initialization.
     *
     * @param  int  $clientId
     * 
     */
    private static function SetClientId(string $clientId): void
    {
        if (empty($clientId)) {
            throw new InvalidArgumentException("BASSDK.SetClientId clientId is null");
        }
        ConfigProperties::$clientId = $clientId;
    }
    /**
     * Set the clientSecret during initialization.
     *
     * @param  int  $clientSecret
     * 
     */
    private static function SetClientSecret(string $clientSecret): void
    {
        if (empty($clientSecret)) {
            throw new InvalidArgumentException("BASSDK.SetClientSecret clientSecret is null");
        }
        ConfigProperties::$clientSecret = $clientSecret;
    }

      /**
     * Get the appId .
     *
     * @return  string  $appId
     * 
     */
    private static function GetAppId(): string
    {
        if (empty( ConfigProperties::$appId)) {
            throw new InvalidArgumentException("BASSDK.SetAppId appId is null");
        }
        return ConfigProperties::$appId;
    }

        /**
     * Get the ClientId .
     *
     * @return  string  $clientId
     * 
     */
    private static function GetClientId(): string
    {
        if (empty( ConfigProperties::$clientId)) {
            throw new InvalidArgumentException("BASSDK.SetClientId ClientId is null");
        }
        return ConfigProperties::$clientId;
    }
        /**
     * Get the ClientSecret .
     *
     * @return  string  $clientSecret
     * 
     */
    private static function GetClientSecret(): string
    {
        if (empty( ConfigProperties::$clientSecret)) {
            throw new InvalidArgumentException("BASSDK.SetClientsecret clientsecret is null");
        }
        return ConfigProperties::$clientSecret;
    }

    private static string GetAuthRedirectUrl(): string
    {
        if (empty( ConfigProperties::$clientSecret)) {
            throw new InvalidArgumentException("BASSDK.SetClientsecret clientsecret is null");
        }
        return ConfigProperties::$clientSecret;
    }


    
}

class ConfigProperties {
    public static $mKey;
    public static $appId;
    public static $clientId;
    public static $clientSecret;
    public static ENVIRONMENT $environment;
    public const  BaseUrlSandbox = "https://basgate-sandbox.com";
    public const  baseUrlStaging = "https://api-tst.basgate.com:4951";
    public const  baseUrlProduction = "https://api.basgate.com:4950";
    public const  redirectIrl = "/api/v1/auth/callback";
}

enum ENVIRONMENT: int {
    case STAGING = 0;
    case PRODUCTION = 1;
    case SANDBOX = 2;
}
