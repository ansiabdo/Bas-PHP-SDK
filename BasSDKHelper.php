<?php


namespace BasSDK;

class BasSDKHelper
{

    function init_payment()
    {

        $reqBody = '{"head":{"signature":"sigg","requestTimeStamp":"timess"},"body":bodyy}';
        // $requestTimestamp = gmdate("Y-m-d\TH:i:s\Z");
        $requestTimestamp = (string)  time();
        /* body parameters */
        $basgateParams["body"] = array(
            "appId" => $this->getSetting('bas_application_id'),
            "requestTimestamp" => $requestTimestamp,
            "orderType" => "PayBill",
            "callBackUrl" => $callBackURL,
            "customerInfo" => array(
                "id" => $paramData['open_id'],
                "name" => $paramData['cust_name'],
            ),
            "amount" => array(
                "value" => (float)$paramData['amount'],
                "currency" => $paramData['currency'],
            ),
            "orderId" => $paramData['order_id'],
            "orderDetails" => array(
                "Id" => $paramData['order_id'],
                "Currency" => $paramData['currency'],
                "TotalPrice" => (float) $paramData['amount'],
            )
        );
        $bodystr = json_encode($basgateParams["body"]);
        $checksum = BasgateChecksum::generateSignature($bodystr, $this->getSetting('bas_merchant_key'));

        if ($checksum === false) {
            error_log(
                sprintf(
                    /* translators: 1: Event data. */
                    __('Could not retrieve signature, please try again Data: %1$s.'),
                    $bodystr
                )
            );
            throw new Exception(__('Could not retrieve signature, please try again.', BasgateConstants::ID));
        }

        /* prepare JSON string for request */
        $reqBody = str_replace('bodyy', $bodystr, $reqBody);
        $reqBody = str_replace('sigg', $checksum, $reqBody);
        $reqBody = str_replace('timess', $requestTimestamp, $reqBody);

        $url = BasgateHelper::getBasgateURL(BasgateConstants::INITIATE_TRANSACTION_URL, $this->getSetting('bas_environment'));
        $header = array('Accept: text/plain', 'Content-Type: application/json');
        $res = BasgateHelper::httpPost($url, $reqBody, $header);

        if (!empty($res['body']['trxToken'])) {
            $data['trxToken'] = $res['body']['trxToken'];
            $data['trxId'] = $res['body']['trxId'];
            $data['callBackUrl'] = $callBackURL;
        } else {
            error_log(
                sprintf(
                    /* translators: 1: bodystr, 2:. */
                    __('trxToken empty \n bodystr: %1$s , \n $checksum: %2$s.'),
                    $bodystr,
                    $checksum
                )
            );
            $data['trxToken'] = "";
        }
    }
}
