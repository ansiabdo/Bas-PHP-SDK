<?php

class BasDebugHelperService {

 // #region  SandBox Simulation
 public static function SimulateMobileFetchAuth(): mixed
 {
     $clientId = BasSDKService::GetClientId();
     if (!BasSDKService::isSandboxEnvironment()) {
         throw new InvalidArgumentException('This method is only allowed on Sandbox environment');
     }
     $header = array('Content-Type: application/json');
     $url = BasSDKService::GetMobileFetchAuthUrl();
     $fulUrl = $url . "?clientId=" . urlencode($clientId);
     //echo "Full auth Url is: ". $fulUrl .nl2br("\n");
     $jsonResponse = BasSDKService::httpPost($fulUrl, data: null, header: $header,grantType: GrantTypes::client_credentials);
     echo $jsonResponse . nl2br("\n");
     $response = json_decode($jsonResponse);
     // echo "Fatch Auth Response is : ". $response .nl2br("\n");
     //echo $response .nl2br("\n");
     return $response;
 }

 public static function SimulateMobilePayment($orderId, $trxToken, $amount): mixed
 {
     if (!BasSDKService::isSandboxEnvironment()) {
         throw new InvalidArgumentException('This method is only available on Sandbox environment');
     }
     $header = array('Content-Type: application/json');
     $data = array();

     $data['amount'] = ['value' => $amount, 'currency' => 'YER'];
     $data['appId'] = BasSDKService::GetAppId();
     $data['orderId'] = $orderId;
     $data['trxToken'] = $trxToken;

     // Convert the data array to a JSON string
     $body = json_encode($data);
     //$body = http_build_query($data);
     $url = BasSDKService::GetMobilePaymentUrl();
     $response = BasSDKService::httpPost($url, $body, header: $header,grantType: GrantTypes::client_credentials);
     return $response;
 }

}