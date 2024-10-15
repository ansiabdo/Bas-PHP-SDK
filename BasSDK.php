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


    //#region Old

    // static public function getUserInfo($code): mixed
    // {
    //     $token = self::getToken($code);
    //     //return $token;
    //     if (!is_null($token)) {
    //         $header = array('Authorization: Bearer ' . $token);
    //         $response =    self::httpGet(BASEURL . "auth/userInfo", null, $header);
    //         return json_decode($response, true);
    //     }
    //     return null;
    // }
    // static public function getToken($code)
    // {
    //     $header = array('Content-Type: application/x-www-form-urlencoded');
    //     $data = array();
    //     $data['client_secret'] = CLIENT_SECRET;
    //     $data['client_id'] = CLIENT_ID;
    //     $data['grant_type'] = 'authorization_code';
    //     $data['code'] = $code;
    //     $data['redirect_uri'] = BASEURL . 'auth/callback';

    //     //return http_build_query($data) . "\n";
    //     $body = http_build_query($data);
    //     $response =    self::httpPost(BASEURL . "auth/token", $body, $header);
    //     $response = json_decode($response, true);
    //     if (!is_array($response)) {
    //         //  echo "is Not Array ".$response;
    //         return null;
    //     } else {

    //         if (array_key_exists('access_token', $response)) {
    //             //  $response=json_decode($response, true);
    //             // echo $response;
    //             return $response['access_token'];
    //         }
    //     }
    //     return $response;
    // }
    //#endregion


    static public function Init($orderId, $amount, $callBackUrl, $customerInfoId, $orderDetails)
    {
        $reqBody = '{"head":{"signature":"sigg","requestTimeStamp":"timess"},"body":bodyy}';
        //  $config=include('config.php');
        $requestTimestamp =  (string)  time();

        $bodyy['requestTimestamp'] = $requestTimestamp;
        $bodyy['appId'] = self::GetAppId();
        $bodyy['orderId'] = $orderId;
        $bodyy['orderType'] = 'PayBill';
        $bodyy['amount'] = ['value' => $amount, 'currency' => 'YER'];

        $bodyy['callBackUrl'] = $callBackUrl;

        $bodyy['customerInfo'] = ['id' => $customerInfoId, 'name' => 'Test'];
        $bodyy['orderDetails'] = $orderDetails;

        $bodyyStr = json_encode($bodyy);

        $basChecksum = BasChecksum::generateSignature($bodyyStr, self::GetMKey());


        /* prepare JSON string for request */
        $reqBody = str_replace('bodyy', $bodyyStr, $reqBody);
        $reqBody = str_replace('sigg', $basChecksum, $reqBody);
        $reqBody = str_replace('timess', $requestTimestamp, $reqBody);
        $paymentUrl = self::GetInitiatePaymentUrl();
        $resp = self::httpPost($paymentUrl, $reqBody, self::$ContentTypeJson);
        //return $resp;
        if (self::isSandboxEnvironment()) {
            return  json_decode($resp, true);
        }
        //Add Signature
        $isVerify = BasChecksum::verifySignature($bodyyStr, self::GetMKey(), checksum: $basChecksum);
        if (!$isVerify) {
            throw new InvalidArgumentException("BASSDK.verifySignature Invalid_response_signature");
        }

        return  json_decode($resp, true);
    }

    static public function CheckStatus($orderId)
    {
        $requestTimestamp = '1668714632332';
        $header = array('Content-Type: application/json');

        $bodyy['RequestTimestamp'] = $requestTimestamp;
        $bodyy['AppId'] = self::GetAppId();
        $bodyy['OrderId'] = $orderId;


        $bodyyStr = json_encode($bodyy);

        $basChecksum = BasChecksum::generateSignature($bodyyStr, MKEY);

        $head["Signature"] = $basChecksum;
        $head["RequestTimestamp"] = $requestTimestamp;

        $req["Head"] = $head;
        $req["Body"] = $bodyy;

        $data = json_encode($req);
        $paymentStatusUrl = self::GetPaymentStatusUrl();
        $resp = self::httpPost(url: $paymentStatusUrl, data: $data, header: $header);

        return  json_decode($resp, true);
        // return $resp;
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

    public static function Initialize(ENVIRONMENT $environment, string $clientId, string $clientSecret, string $appId, string $openId, string $mKey): void
    {
        ConfigProperties::SetEnvironment(environment: $environment);
        self::SetClientId(clientId: $clientId);
        self::SetClientSecret(clientSecret: $clientSecret);
        self::SetAppId($appId);
        self::SetOpenId($openId);
        self::SetMKey(mKey: $mKey);
    }

    /**
     * Get UserInfo V2 
     *
     * @param  string  $code
     * 
     */

    static public function getUserInfoV2($code)
    {
        $header = array('Content-Type: application/x-www-form-urlencoded');
        $data = array();
        $data['client_secret'] = self::GetClientSecret();
        $data['client_id'] = self::GetClientId();

        $data['code'] = $code;
        $data['redirect_uri'] = self::GetAuthRedirectUrl();
        $body = http_build_query($data);

        if (!is_null($code)) {
            $response =    self::httpPost(self::GetuserInfoV2Url(), $body, $header);
            return json_decode($response, true);
        }
        return null;
    }

    /**
     * 
     * Get full baseUrl of API
     */

    static function GetFullBaseUrlBasedOnEnvironment($relativePath): string
    {
        $baseUrl = "";
        //echo "Current env: ". ConfigProperties::$environment->value;
        switch (ConfigProperties::$environment) {
            case ENVIRONMENT::STAGING:
                $baseUrl = ConfigProperties::$baseUrlStaging . $relativePath;
                return $baseUrl;
            case ENVIRONMENT::PRODUCTION:
                $baseUrl = ConfigProperties::$baseUrlProduction . $relativePath;
                return $baseUrl;
            case ENVIRONMENT::SANDBOX:
                $baseUrl = ConfigProperties::$BaseUrlSandbox . $relativePath;
                return $baseUrl;
            default:
                throw new InvalidArgumentException("BASSDK.UnKnown Environment" . ConfigProperties::$environment->value);
        }
    }

    /**
     * Set the openId during initialization.
     *
     * @param  int  $openId
     * 
     */
    private static function SetOpenId(string $openId): void
    {
        if (empty($openId)) {
            throw new InvalidArgumentException("BASSDK.SetOpenId openId is null");
        }
        ConfigProperties::$openId = $openId;
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

    private static function SetEnvironment(ENVIRONMENT $environment): void
    {
        if (empty($environment->value)) {
            throw new InvalidArgumentException("BASSDK.SetEnvironment environment is null");
        }
        ConfigProperties::$environment = $environment;
    }

    /**
     * Get the appId .
     *
     * @return  string  $appId
     * 
     */
    public static function GetOpenId(): string
    {
        if (empty(ConfigProperties::$openId)) {
            throw new InvalidArgumentException("BASSDK.GetOpenId openId is null");
        }
        return ConfigProperties::$openId;
    }


    /**
     * Get the appId .
     *
     * @return  string  $appId
     * 
     */
    public static function GetAppId(): string
    {
        if (empty(ConfigProperties::$appId)) {
            throw new InvalidArgumentException("BASSDK.SetAppId appId is null");
        }
        return ConfigProperties::$appId;
    }

    /**
     * Get the appId .
     *
     * @return  string  $appId
     * 
     */
    public static function GetMKey(): string
    {
        if (empty(ConfigProperties::$mKey)) {
            throw new InvalidArgumentException("BASSDK.GetMKey mKey is null");
        }
        return ConfigProperties::$mKey;
    }

    /**
     * Get the ClientId .
     *
     * @return  string  $clientId
     * 
     */
    public static function GetClientId(): string
    {
        if (empty(ConfigProperties::$clientId)) {
            throw new InvalidArgumentException("BASSDK.GetClientId ClientId is null");
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
        if (empty(ConfigProperties::$clientSecret)) {
            throw new InvalidArgumentException("BASSDK.SetClientsecret clientsecret is null");
        }
        return ConfigProperties::$clientSecret;
    }

    public static function GetEnvironmentValue(): mixed
    {
        if (ConfigProperties::$environment !== null) {
            return ConfigProperties::$environment->value;
        } else {
            throw new Exception("Environment is not initialized.");
        }
    }

    private static function GetAuthRedirectUrl(): string
    {
        if (empty(ConfigProperties::$redirectUrl)) {
            throw new InvalidArgumentException("BASSDK.GetAuthRedirectUrl RedirectUrl is null");
        }
        return self::GetFullBaseUrlBasedOnEnvironment(ConfigProperties::$redirectUrl);
    }

    private static function GetuserInfoV2Url(): string
    {
        if (empty(ConfigProperties::$userInfoV2Url)) {
            throw new InvalidArgumentException("BASSDK.GetuserInfoV2Url userInfoV2Url is null");
        }
        return self::GetFullBaseUrlBasedOnEnvironment(ConfigProperties::$userInfoV2Url);
    }

    private static function GetInitiatePaymentUrl(): string
    {
        if (empty(ConfigProperties::$initiatePaymentUrl)) {
            throw new InvalidArgumentException("BASSDK.GetInitiatePaymentUrl initiatePaymentUrl is null");
        }
        return self::GetFullBaseUrlBasedOnEnvironment(ConfigProperties::$initiatePaymentUrl);
    }

    private static function GetPaymentStatusUrl(): string
    {
        if (empty(ConfigProperties::$paymentStatusUrl)) {
            throw new InvalidArgumentException("BASSDK.GetPaymentStatusUrl paymentStatusUrl is null");
        }
        return self::GetFullBaseUrlBasedOnEnvironment(ConfigProperties::$paymentStatusUrl);
    }
    private static function GetTokenUrl(): string
    {
        if (empty(ConfigProperties::$tokenUrl)) {
            throw new InvalidArgumentException("BASSDK.GetTokenUrl tokenUrl is null");
        }
        return self::GetFullBaseUrlBasedOnEnvironment(ConfigProperties::$tokenUrl);
    }
    private static function GetMobileFetchAuthUrl(): string
    {
        if (empty(ConfigProperties::$mobileFetchAuthUrl)) {
            throw new InvalidArgumentException("BASSDK.GetMobileFetchAuthUrl mobileFetchAuthUrl is null");
        }
        return self::GetFullBaseUrlBasedOnEnvironment(ConfigProperties::$mobileFetchAuthUrl);
    }

    private static function GetMobilePaymentUrl(): string
    {
        if (empty(ConfigProperties::$mobilePaymentUrl)) {
            throw new InvalidArgumentException("BASSDK.GetMobilePaymentUrl mobilePaymentUrl is null");
        }
        return self::GetFullBaseUrlBasedOnEnvironment(ConfigProperties::$mobilePaymentUrl);
    }

    private static function getTokenV2()
    {
        $header = array('Content-Type: application/x-www-form-urlencoded');
        $data = array();
        $data['client_secret'] = self::GetClientSecret();
        $data['client_id'] = self::GetClientId();
        $data['grant_type'] = 'client_credentials';

        //return http_build_query($data) . "\n";
        $body = http_build_query($data);
        $response =    self::httpPost(self::GetTokenUrl(), $body, $header);
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
    //TODO
    private static function isSandboxEnvironment()
    {
        if (ConfigProperties::$environment == ENVIRONMENT::SANDBOX) {
            return true;
        }
        return false;
    }

    public static function SendNotificationToCustomer($templateName, $orderId, $orderParams, $firebasePayload, $extraPayload): mixed
    {
        $accessToken = self::getTokenV2();
        if (!is_null($accessToken)) {
            throw new InvalidArgumentException("BASSDK.SendNotificationToCustomer accessToken is null");
        }
        $header = array();
        $header['Authorization'] = $accessToken;
        $header['scheme'] = 'Bearer';
        $header['AppId'] = self::GetAppId();

        $data = array();
        $data['orderId'] = $orderId;
        $data['extraPayload'] = $extraPayload;
        $data['firebasePayload'] = $firebasePayload;
        $data['orderParams'] = $orderParams;
        $data['templateName'] = $templateName;

        $body = http_build_query($data);
        $url = self::GetFullBaseUrlBasedOnEnvironment(ConfigProperties::$notificationUrl);
        $response = self::httpPost($url, $body, header: $header);
        return json_decode($response, true);
    }

    public static function SimulateMobileFetchAuthAsync($clientId): mixed
    {
        if (!self::isSandboxEnvironment()) {
            throw new InvalidArgumentException('This method is only allowed on Sandbox environment');
        }
        $header = array('Content-Type: application/json');
        $url = self::GetMobileFetchAuthUrl();
        $fulUrl = $url . "?clientId=" . urlencode($clientId);
        //echo "Full auth Url is: ". $fulUrl .nl2br("\n");
        $jsonResponse = self::httpPost($fulUrl, data: null, header: $header);
        $response = json_decode($jsonResponse);
        // echo "Fatch Auth Response is : ". $response .nl2br("\n");
        return $response;
    }


    public static function SimulateMobilePaymentAsync($orderId, $appId, $trxToken, $amount): mixed
    {
        if (!self::isSandboxEnvironment()) {
            throw new InvalidArgumentException('This method is only available on Sandbox environment');
        }
        $header = array('Content-Type: application/json');
        $data = array();

        $data['amount'] = ['value' => $amount, 'currency' => 'YER'];
        $data['appId'] = $appId;
        $data['orderId'] = $orderId;
        $data['trxToken'] = $trxToken;
        // $merchantId = "ac90ddd1-6627-4ae9-b268-98c17bd8ee6c";
        // $data['merchantId'] = $merchantId;

        // Convert the data array to a JSON string
        $body = json_encode($data);
        //$body = http_build_query($data);
        $url = self::GetMobilePaymentUrl();
        $response = self::httpPost($url, $body, header: $header);
        return $response;
    }
}
// #region Config 
class  ConfigProperties
{

    public static $openId;
    public static $mKey;
    public static $appId;
    public static $clientId;
    public static $clientSecret;
    public static ENVIRONMENT $environment;
    //public static ?ENVIRONMENT $environment = null; 
    public static  $BaseUrlSandbox = "https://basgate-sandbox.com";
    public static  $baseUrlStaging = "https://api-tst.basgate.com:4951";
    public static  $baseUrlProduction = "https://api.basgate.com:4950";
    public static  $redirectUrl = "/api/v1/auth/callback";
    public static  $userInfoV2Url = "/api/v1/auth/secure/userinfo";
    public static  $initiatePaymentUrl = "/api/v1/merchant/secure/transaction/initiate";
    public static  $paymentStatusUrl = "/api/v1/merchant/secure/transaction/status";
    public static  $notificationUrl = "/api/v1/merchant/secure/notifications/send-to-customer";
    public static  $tokenUrl = "/api/v1/auth/token";
    public static  $mobileFetchAuthUrl = "/api/v1/mobile/fetchAuth";
    public static  $mobilePaymentUrl = "/api/v1/mobile/payment";
    public static bool $isInitialized = false;

    public static function SetEnvironment(ENVIRONMENT $environment): void
    {
        if (empty($environment->value)) {
            throw new InvalidArgumentException("BASSDK.SetEnvironment environment is null");
        }
        self::$environment = $environment;
    }

    // Example method that uses the environment
    public static function GetEnvironmentValue(): mixed
    {
        return self::$environment->value;
        // Check if the environment is initialized before accessing
        if (self::$environment !== null) {
            //return "Environment is: " . self::$environment->value;
            self::$environment->value;
        } else {
            throw new Exception("Environment is not initialized.");
        }
    }
}

enum ENVIRONMENT: string
{
    case STAGING = 'staging';
    case PRODUCTION = 'production';
    case SANDBOX = 'sandbox';
}
// #endregion