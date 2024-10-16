<?php

// $config = include('config.php');


//namespace BasSdk;
class Initialization
{
    private static $instance;

    private function __construct()
    {
        // Private constructor to prevent instantiation outside the class
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function Initialize(ENVIRONMENT $env): string
    {
        //$config = include('config.php');
        if ($env === ENVIRONMENT::SANDBOX) {
            BasSDK::Initialize(
                $env,
                clientId: CLIENT_ID,
                clientSecret: CLIENT_SECRET,
                appId: APP_ID,
                openId: OPEN_ID,
                mKey: '',
            );
        } else {
            BasSDK::Initialize(
                $env,
                clientId: CLIENT_ID,
                clientSecret: CLIENT_SECRET,
                appId: APP_ID,
                openId: OPEN_ID,
                mKey: MKEY,
            );
        }
    
    
        echo 'Intialized environment : ' . BasSDK::GetEnvironmentValue();
        return '<script src="bassdk.js" type="text/javascript"></script>';
    }
}