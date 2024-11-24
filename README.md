
# BasSdk PHP Package

## Overview
BasSdk is a PHP library that provides a set of services to interact with the BAS super app. This SDK facilitates various operations such as initiating transactions, fetching user information, sending notifications, and simulating mobile authentication. It is designed to be easy to use and integrates seamlessly into PHP applications, providing logging capabilities and allowing initialization with necessary credentials and environment settings.ngs.

## Installation

To install the package, you can use Composer, the PHP dependency manager. Run the following command in your terminal

```bash
composer require .. 
```

## Usage

### Initialization Requirements
Before you can start using the BasSdk services, it is crucial to initialize the SDK. This setup ensures that the SDK is prepared to interact with the BAS super app according to the specified environment.

To initialize the SDK, use the following code:

// Get the initialization instance
$initial = Initialization::getInstance();

// Initialize the SDK with the desired environment
$initial->Initialize(ENVIRONMENT::STAGING);
DiffCopyInsert
Explanation:
Initialization Class: This is a singleton class that handles all configuration settings for the SDK.
Environment Setup: The Initialize method accepts an environment constant such as ENVIRONMENT::STAGING, ENVIRONMENT::PRODUCTION, etc. It configures the SDK based on the specified environment, setting the appropriate API endpoints and authentication parameters needed for operations.


## Methods
### Authorization flow
### <span style="color:blue"> The Authorization flow from two steps </span> 
<span style="color:blue"> 1- fetch auth id (from mobile)
2- Get user info (from Backend)
</span>.

## `1- Fetch auth id (from mobile)`
#### This step should be done in mobile app to get auth id 
#### <span style="color:red"> For debugging purpose from backend without need to retrieve auth id  response from mobile you can use function SimulateMobileFetchAuthAsync in the BasDebugHelperService to Simulate Mobile Initiate Payment response Only  purpose !! </span>

#### <span style="color:red"> You should not call this method in production or staging environment !! </span>.

Simulates a mobile first step you can call this method to simulate mobile fetch auth_id, this method will return a mocked response from mobile app to backend for debugging purpose when environment is Sandbox
```php
//function signature
 public static function SimulateMobileFetchAuth(): mixed
```


## `2- Get User Info`

Fetches user information from the backend.

The function will return a `Task<BasBaseResponse<UserInfoDataResponse>>` object, you can use this object to get the user information
```csharp
//function signature
Task<BasBaseResponse<UserInfoDataResponse>> GetUserInfoAsync(string code, int? requestTimeOut = null, Dictionary<string, string> headerExtraInfo = null)
```
you can call this method to get user information from backend
```csharp
//first inject the IBasSdkService in your service
private readonly IBasSdkService _basMainService;
public YourService(IBasSdkService basMainService)
{
    _basMainService = basMainService;
}
//then you can call the below function to get user information
var authId=<authId from Mobile Response>;
var userInfoResp = await _basMainService.GetUserInfoAsync(authId)
if (userInfoResp.Status != 1)
{
    //handle error case
    await _logger.ErrorAsync($"Error when get user info  response is :{JsonConvert.SerializeObject(userInfoResp)}");
    throw new InvalidOperationException(userInfoResp.Messages.FirstOrDefault());
}
var userName = userInfoResp.Data.user_name;
var email = userInfoResp.Data.email;
var phone = userInfoResp.Data.phone;
var name = userInfoResp.Data.name;
var openId = userInfoResp.Data.open_id;
...your rest of code
```
<hr/>

##
#  `Payment flow` 
### <span style="color:blue"> The Payment flow from three steps </span> 
<span style="color:blue"> 1- initiate payment (from backend)
2- confirm payment (from mobile)
3- check transaction status
</span>.

#### `1- Initiate Transaction`
This is first step to initiate payment (should be called from backend), this function will return a `Task<ResponseSig<InitiateTransactionResponse>>` object, you can use this object to get the transaction token

```csharp
    //function signature
    Task<ResponseSig<InitiateTransactionResponse>> InitiateTransactionAsync(string orderId, decimal amount, string currency, string customerId, int requestTimeout = 60, object orderDetails = null, Dictionary<string, object> extraFields = null, string callbackUrl = null)       
```
<span style="color:blue;font-weight:bold;"> Usage:

```csharp
//first inject the IBasSdkService in your service
private readonly IBasSdkService _basMainService;
public YourService(IBasSdkService basMainService)
{
    _basMainService = basMainService;
}

//then you can call the below function to initiate transaction
ResponseSig<InitiateTransactionResponse> response = await _basMainService.InitiateTransactionAsync("abc123", 2000, "YER", "openId", orderDetails: new
{
    products = new List<object>
    {
        new
        {
            name = "product1",
            quantity = 1,
            price = 2000
        }
    }
});
if (response.Status != 1)
{
    //handle error case (you can get the error message from response.Messages.FirstOrDefault())
    throw new InvalidOperationException(response.Messages.FirstOrDefault());
}
//extract the transaction token from response.Body.TrxToken and return it to mobile app
var responseToMobile = new
{
    amount = new { value = orderTotal.ToString(), currency = "YER" },
    orderId = orderId,
    trxToken = response.Body.TrxToken,
    appId = IBasSdkService.GetAppId()
};
... your rest of code
```
#### `2- Initiate Payment (Mobile)`
#### <span style="color:red"> This step should be done in mobile app to complete the payment process 
#### <span style="color:red"> For debugging purpose from backend without need to retrieve payment response from mobile you can use function SimulateMobilePaymentAsync in the IBasDebugHelperService to Simulate Mobile Initiate Payment response Only  purpose !! 
#### <span style="color:red"> You should not call this method in production or staging environment !! 
This is second step to initiate payment (should be called from mobile app), this function will return a `BasBaseResponse<MobilePaymentResponseModel>` object, you can use this object to get the transaction token

```csharp
//function signature
    public async Task<BasBaseResponse<FetchAuthMobileResponseModel>> SimulateMobilePaymentAsync(MobilePaymentRequest request, int requestTimeout = 60)
```
<span style="color:blue;font-weight:bold;"> Usage:

```csharp
    //to mock (simulate) mobile payment step you can call below function (this should be only for debugging purpose  when environment is sandbox)
    //first inject the IBasDebugHelperService in your service
    private readonly IBasDebugHelperService _basMainService;
    
    public YourService(IBasDebugHelperService basMainService)
    {
        _basMainService = basMainService;
    }

    //then you can call the below function to simulate mobile payment
    var mobileMockedResponse = await _basMainService.SimulateMobilePaymentAsync(new BasSdk.Models.MobileMockModels.MobilePaymentRequest
    {
        amount = new BasSdk.Models.MobileMockModels.MobilePaymentRequest.AmountDto { value = orderTotal.ToString(), currency = targetCurrencyCode },
        orderId = orderId,
        trxToken = response.Body.TrxToken,
        appId=IBasSdkService.GetAppId()
    });
    if (mobileMockedResponse.Status != 1)
    {
        //handle error case for response comming from mobile app
        throw new InvalidOperationException(mobileMockedResponse.Messages.FirstOrDefault());
    }
```


#### `3- CheckTransactionStatusAsync`
This is third step: to check transaction status (should be called from backend), this function will return a `Task<ResponseSig<TrxStatusModel>>` object, you can use this object to get the transaction status
```csharp
//function signature
Task<ResponseSig<TrxStatusModel>> CheckTransactionStatusAsync(string orderId, int requestTimeout = 60)
```
<span style="color:blue;font-weight:bold;"> Usage:

```csharp
  var checkStatusResponse = await _basMainService.CheckTransactionStatusAsync(orderId, _requestTimeout);
    if (checkStatusResponse.Status != 1)
    {
        //handle error case (you can get the error message from checkStatusResponse.Messages.FirstOrDefault())
        throw new InvalidOperationException(checkStatusResponse.Messages.FirstOrDefault());
    }
    if (checkStatusResponse.Body.TrxStatusId== 1202)
    {
        //when checkStatusResponse.Body.TrxStatusId == 1202 then the payment is succeed ( checkStatusResponse.Body.TrxStatus =="processed")
        await LogInformationAsync($"Payment was Succeed");
    }
```

#### ` FullPaymentRefundAsync`
This function is used to refund the full payment amount (should be called from backend), this function will return a `Task<ResponseSig<RefundPaymentResponse>>` object, you can use this object to get the refund status
```csharp
//function signature
Task<ResponseSig<RefundPaymentResponse>> FullPaymentRefundAsync(string orderId, string reason)
```
parameters:
`orderId` is the orderId that you sent in the initiate transaction request (required),

<span style="color:blue;font-weight:bold;"> Usage:

```csharp
//first inject the IBasSdkService in your service
private readonly IBasSdkService _basMainService;
public YourService(IBasSdkService basMainService)
{
    _basMainService = basMainService;
}
//then you can call the below function to refund the full payment
var refundResponse = await _basMainService.FullPaymentRefundAsync(orderId);
if (refundResponse.Status != 1)
{
    //handle error case (you can get the error message from refundResponse.Messages.FirstOrDefault())
    throw new InvalidOperationException(refundResponse.Messages.FirstOrDefault());
}
var refundDetails = refundResponse.Body;
```

#### `SendNotificationToCustomer`

Sends a notification to the customer related to order (you can call this method to send notification to customer about delivery status, etc.).

```csharp
//function signature
Task<BasBaseResponse> SendNotificationToCustomerAsync(string templateName, string orderId, object orderParams = null, object firebasePayload = null, object extraPayload = null, int requestTimeout = 60)
```
The `templateName` is the name of the template that you want to send to the customer (required),
`orderId` is the order id related to this notification (required),
`orderParams` is object that contains template parameters (optional depending on the template),
`firebasePayload` is object that contains firebase payload ex:deepLink, etc. (optional) 
`extraPayload` is object that contains extra payload that you want to send it to your miniApp as query string(optional)

<span style="color:blue;font-weight:bold;"> Usage:

```csharp
//send notification to customer
var notificationResponse = await _basMainService.SendNotificationToCustomerAsync("order_was_shipped", orderId);
if (notificationResponse.Status != 1)
{
    //handle error case
    throw new InvalidOperationException(notificationResponse.Messages.FirstOrDefault());
}
```


## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contributions

Contributions are welcome! Please feel free to submit a pull request or open an issue to discuss any changes.

## Contact

For support, please contact Bas Team.
