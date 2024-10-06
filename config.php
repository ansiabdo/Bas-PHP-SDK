<?php


const BASEURL = 'https://api-tst.basgate.com:4951/api/v1/';
const paymenturl = '/api/v1/merchant/secure/transaction/initiate';


//Auth client credential
const CLIENT_ID = '<<YOUR-CLIENT-ID>>';
const CLIENT_SECRET = '<<YOUR-CLIENT-SECRET>>';

//Payment client 
const MKEY = '<<YOUR-MERCHANT-KEY>>';
const APPID = '<<YOUR-APP-ID>>';


const baseUrlProduction = 'https://api-tst.basgate.com:4951/api/v1/';
enum ENVIRONMENT: int
        {
            case STAGING = 0;
            case PRODUCTION = 1;
            case SANDBOX = 2;
        }
 //$SANDBOX  = ENVIRONMENT::SANDBOX;