function oauthToken(clientId) {
    window.addEventListener("JSBridgeReady", async (event) => {
        console.log("JSBridgeReady fired");
        //to do anything you want after SDK is ready
        return basFetchAuthCode(clientId);
    }, false);
}

function basFetchAuthCode(clientId) {
    JSBridge.call('basFetchAuthCode', {
        clientId: clientId
    }).then(function (result) {
        /****** Response Example ******/
        alert(JSON.stringify(result));
        /*   {
                "status": 1,
                "data": {
                    "authId": "",
                    "openId": ""
                },
                "messages": [""]
            }*/
        try {
            if (result) {
                if (result.status && result.status == 1) {
                    console.log("AuthCode :", result?.data?.auth_id)
                    createCookie("AuthCode", result?.data?.auth_id, "10");
                }
            }
        } catch (error) {
            console.error("ERROR on basFetchAuthCode:", JSON.stringify(error))
        }

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
    // Delete the old cookie if it exists
    document.cookie = escape(name) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
    document.cookie = escape(name) + "=" + escape(value) + expires + "; path=/";
}

function getPayment() {
    window.addEventListener("JSBridgeReady", (event) => {
        console.log("JSBridgeReady fired");
        //to do anything you want after SDK is ready
        basPayment();
    }, false);
}

function basPayment() {
    console.log("basPayment function called"); // Debugging line
    const orderid = document.getElementById("orderid").textContent;
    const trxToken = document.getElementById("trxToken").textContent;
    const amount = document.getElementById("amount").textContent;
    const appid = document.getElementById("appid").textContent;

    JSBridge.call('basPayment', {

        "amount": {
            "value": amount,
            "currency": "YER",
        },
        "orderId": orderid,
        "transactionToken": trxToken,
        "appId": appid
    }).then(function (result) {
        console.log("Result from JSBridge call:", result);
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