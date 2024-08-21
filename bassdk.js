function oauthToken() {
    window.addEventListener("JSBridgeReady", (event) => {
        console.log("JSBridgeReady fired");
        //to do anything you want after SDK is ready
        basFetchAuthCode();
    }, false);
}
function basFetchAuthCode() {
    JSBridge.call('basFetchAuthCode', {
        clientId: "395b0e88-ad46-4692-b797-cedbd2f33d1f"
    }).then(function (result) {
        /****** Response Example ******/
        alert(JSON.stringify(result));

        createCookie("AuthCode", JSON.stringify(result), "10");

        $.ajax({
            type: "POST",
            url: 'userinfo.php',
            data: result,
            success: function (data) {

            }, error: function (data) {

            }
        });
        /*   {
                "status": 1,
                "data": {
                    "authId": "",
                    "openId": ""
                },
                "messages": [""]
            }*/
        return JSON.stringify(result);

        /****** End Response Example ******/
        //  console.log(JSON.stringify(result));
    });

    // Function to create the cookie

}
function createCookie(name, value, days) {
    var expires;

    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }
    else {
        expires = "";
    }

    document.cookie = escape(name) + "=" +
        escape(value) + expires + "; path=/";
}

function getPayment() {
    window.addEventListener("JSBridgeReady", (event) => {
        console.log("JSBridgeReady fired");
        //to do anything you want after SDK is ready
        basPayment();
    }, false);
}
function basPayment() {

    const orderid = document.getElementById("orderid").textContent;
    const trxToken = document.getElementById("trxToken").textContent;
    const amount = document.getElementById("amount").textContent;
    const appid = document.getElementById("appid").textContent;



    // alert(orderid);

    JSBridge.call('basPayment', {

        "amount": {
            "value": amount,
            "currency": "YER",
        },
        "orderId": orderid,
        "transactionToken": trxToken,
        "appId": appid
    }).then(function (result) {
        createCookie("PaymentResult", JSON.stringify(result), "10");
        alert(JSON.stringify(result));
        return JSON.stringify(result);
        /****** Response Example ******/
        /*{
          "appid": "",
          "orderId": "",
          "transactionId": "",
          "amount": {
            "value": 0,
            "currency": "YER"
          },
          "paymentType": "",
          "date": "",
          "status":1
        }*/
        /****** End Response Example ******/

        console.log(JSON.stringify(result));
    });
}