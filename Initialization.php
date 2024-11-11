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
    $mKey = $env === ENVIRONMENT::SANDBOX ? '' : MKEY;
    BasSDKService::Initialize(
        $env,
        clientId: CLIENT_ID,
        clientSecret: CLIENT_SECRET,
        appId: APP_ID,
        openId: OPEN_ID,
        mKey: $mKey,
    );

    echo 'Initialized environment: ' . BasSDKService::GetEnvironment();

    return '<script src="bassdk.js" type="text/javascript"></script>';
}
}